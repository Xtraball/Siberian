<?php
/**
 * Tier 1: Customer_Model_Customer against a real database: session tokens, passwords and the admin customer lists.
 *
 * Identity code. A session token must belong to one customer at a time, a password check must say yes only to the
 * right password, and nothing typed into a search box may reach the SQL as SQL.
 */
$customerSchema = dirname(__DIR__, 3) . '/siberian/app/sae/modules/Customer/resources/db/schema/customer.php';

/** @return Zend_Db_Adapter_Abstract a database with the customer table */
$fresh = static function () use ($customerSchema): Zend_Db_Adapter_Abstract {
    $db = t_db_fresh();
    Zend_Registry::set('db', $db);
    t_db_sync('customer', $customerSchema);
    unset($_SERVER['HTTP_REFERER']);

    return $db;
};

/** Inserts a customer row and returns its id. */
$add = static function (Zend_Db_Adapter_Abstract $db, array $row = []): int {
    static $n = 0;
    ++$n;
    $db->insert('customer', array_merge([
        'app_id' => 1,
        'firstname' => 'First' . $n,
        'lastname' => 'Last' . $n,
        'nickname' => 'nick' . $n,
        'email' => "c$n@example.test",
        'password' => sha1('secret1'),
    ], $row));

    return (int) $db->lastInsertId();
};

$load = static function (int $id): Customer_Model_Customer {
    $customer = new Customer_Model_Customer();
    $customer->find($id);

    return $customer;
};

return [
    'a session token belongs to one customer: giving it to another takes it from the first' => function () use ($fresh, $add, $load) {
        $db = $fresh();
        $a = $add($db);
        $b = $add($db);

        $load($a)->updateSessionUuid('token-1');
        $load($b)->updateSessionUuid('token-1');

        t_equal(null, $db->fetchOne('SELECT session_uuid FROM customer WHERE customer_id = ?', [$a]), 'the first customer lost it');
        t_equal('token-1', $db->fetchOne('SELECT session_uuid FROM customer WHERE customer_id = ?', [$b]), 'the second has it');
    },

    'a new token replaces the old one of the same customer and leaves the others alone' => function () use ($fresh, $add, $load) {
        $db = $fresh();
        $a = $add($db);
        $b = $add($db);
        $load($a)->updateSessionUuid('a-old');
        $load($b)->updateSessionUuid('b-token');

        $load($a)->updateSessionUuid('a-new');

        t_equal('a-new', $db->fetchOne('SELECT session_uuid FROM customer WHERE customer_id = ?', [$a]), 'replaced');
        t_equal('b-token', $db->fetchOne('SELECT session_uuid FROM customer WHERE customer_id = ?', [$b]), 'other customer untouched');
    },

    'a token with quotes, a percent sign or a question mark is stored as text (HY093 class of bug)' => function () use ($fresh, $add, $load) {
        $db = $fresh();
        $a = $add($db);
        $b = $add($db);
        foreach (["it's", 'a"b', '100%', 'what?', "x' OR '1'='1", ':name', '\\'] as $token) {
            $load($a)->updateSessionUuid($token);
            $load($b)->updateSessionUuid($token);
            t_equal($token, $db->fetchOne('SELECT session_uuid FROM customer WHERE customer_id = ?', [$b]), "stored as is: $token");
            t_equal(null, $db->fetchOne('SELECT session_uuid FROM customer WHERE customer_id = ?', [$a]), "moved to the other customer: $token");
        }
        t_equal(2, (int) $db->fetchOne('SELECT COUNT(*) FROM customer'), 'no row was lost or added');
    },

    'the overview and browser (web preview) referers never take the token' => function () use ($fresh, $add, $load) {
        $db = $fresh();
        $a = $add($db);
        $load($a)->updateSessionUuid('phone');
        $b = $add($db);

        foreach (['https://x.test/overview/app', 'https://x.test/BROWSER/view'] as $referer) {
            $_SERVER['HTTP_REFERER'] = $referer;
            $load($b)->updateSessionUuid('phone');
        }
        unset($_SERVER['HTTP_REFERER']);

        t_equal('phone', $db->fetchOne('SELECT session_uuid FROM customer WHERE customer_id = ?', [$a]), 'the phone keeps its session');
        t_equal(null, $db->fetchOne('SELECT session_uuid FROM customer WHERE customer_id = ?', [$b]), 'the preview did not get it');
    },

    'clearSessionUuid logs the customer out and only that customer' => function () use ($fresh, $add, $load) {
        $db = $fresh();
        $a = $add($db);
        $b = $add($db);
        $load($a)->updateSessionUuid('ta');
        $load($b)->updateSessionUuid('tb');

        $load($a)->clearSessionUuid();

        t_equal(null, $db->fetchOne('SELECT session_uuid FROM customer WHERE customer_id = ?', [$a]), 'logged out');
        t_equal('tb', $db->fetchOne('SELECT session_uuid FROM customer WHERE customer_id = ?', [$b]), 'other session kept');
    },

    'passwords: setPassword needs 6 characters, authenticate says yes only to the right one' => function () use ($fresh, $add, $load) {
        $db = $fresh();
        $id = $add($db);
        $customer = $load($id);

        $message = null;
        try {
            $customer->setPassword('12345');
        } catch (Exception $e) {
            $message = $e->getMessage();
        }
        t_true($message !== null, 'five characters are refused');

        $customer->setPassword('correct horse');
        $customer->save();
        $reloaded = $load($id);

        t_true($reloaded->authenticate('correct horse'), 'the right password');
        t_true(!$reloaded->authenticate('correct horsf'), 'a wrong one');
        t_true(!$reloaded->authenticate(''), 'an empty one');
        t_true(!$reloaded->authenticate('CORRECT HORSE'), 'case matters');
        t_true($reloaded->isSamePassword('correct horse'), 'isSamePassword agrees');
    },

    'a customer with an empty stored password cannot be logged in with an empty password' => function () use ($fresh, $add, $load) {
        $db = $fresh();
        $id = $add($db, ['password' => '']);

        t_true(!$load($id)->authenticate(''), 'empty never matches (social accounts have no password)');
        t_true(!$load($id)->authenticate('anything'), 'nor does anything else');
    },

    'findByEmail finds one customer, and a quote in the address is only text' => function () use ($fresh, $add) {
        $db = $fresh();
        $id = $add($db, ['email' => "o'brien@example.test"]);
        $add($db, ['email' => 'other@example.test']);

        $found = (new Customer_Model_Customer())->findByEmail("o'brien@example.test");
        t_equal($id, (int) $found->getId(), 'found by an address with a quote');

        $none = (new Customer_Model_Customer())->findByEmail("x' OR '1'='1");
        t_true(!$none->getId(), 'an injection string finds nobody');
    },

    'the admin list: scoped to the app, filter matches names and email, a quote is only text' => function () use ($fresh, $add) {
        $db = $fresh();
        $add($db, ['app_id' => 1, 'firstname' => 'Alice', 'email' => 'alice@example.test']);
        $add($db, ['app_id' => 1, 'firstname' => "D'Arcy", 'email' => 'darcy@example.test']);
        $add($db, ['app_id' => 2, 'firstname' => 'Alice', 'email' => 'alice2@example.test']);
        $model = new Customer_Model_Customer();

        t_equal(2, count($model->findAllForApp(1)), 'app 1 has two');
        t_equal(['alice@example.test'], array_map(static function ($c) { return $c->getEmail(); }, iterator_to_array($model->findAllForApp(1, ['filter' => 'alice']), false)), 'filter by name, still only app 1');
        t_equal(1, count($model->findAllForApp(1, ['filter' => "D'Arcy"])), 'a quote in the filter');
        t_equal(0, count($model->findAllForApp(1, ['filter' => "x') OR 1=1 -- "])), 'an injection string matches nothing');
        t_equal(['2'], array_map('strval', $model->countAllForApp(1, ['filter' => 'example'])), 'count agrees with the list');
    },

    'the admin list sorts by the requested column, and a hostile column name is refused' => function () use ($fresh, $add) {
        $db = $fresh();
        $add($db, ['firstname' => 'Bob']);
        $add($db, ['firstname' => 'Amy']);
        $model = new Customer_Model_Customer();

        $names = static function ($list): array {
            return array_map(static function ($c) { return $c->getFirstname(); }, iterator_to_array($list, false));
        };
        t_equal(['Amy', 'Bob'], $names($model->findAllForApp(1, ['sorts' => ['firstname' => 1]])), 'ascending');
        t_equal(['Bob', 'Amy'], $names($model->findAllForApp(1, ['sorts' => ['firstname' => -1]])), 'descending');

        $threw = false;
        try {
            $model->findAllForApp(1, ['sorts' => ['firstname; DROP TABLE customer' => 1]]);
        } catch (Throwable $e) {
            $threw = true;
        }
        t_true(in_array('customer', $db->listTables(), true), 'the table is still there');
    },

    'the paginated search: the typed text is data, not SQL' => function () use ($fresh, $add) {
        $db = $fresh();
        $add($db, ['firstname' => 'Alice', 'email' => 'alice@example.test']);
        $add($db, ['firstname' => "D'Arcy", 'email' => 'darcy@example.test']);
        $add($db, ['app_id' => 2, 'firstname' => 'Zed', 'email' => 'zed@example.test']);
        $model = new Customer_Model_Customer();
        $emails = static function ($list): array {
            $out = array_map(static function ($c) { return $c->getEmail(); }, iterator_to_array($list, false));
            sort($out);

            return $out;
        };

        t_equal(['alice@example.test', 'darcy@example.test'], $emails($model->findAllCustomersByApp(['app_id' => 1])), 'no search: the app');
        t_equal(['alice@example.test'], $emails($model->findAllCustomersByApp(['app_id' => 1, 'search_value' => 'alice'])), 'search by text');
        t_equal(['darcy@example.test'], $emails($model->findAllCustomersByApp(['app_id' => 1, 'search_value' => "D'Arcy"])), 'a quote in the text');
        t_equal([], $emails($model->findAllCustomersByApp(['app_id' => 1, 'search_value' => "%') OR app_id = 2 OR (email LIKE '%"])), 'a hostile text does not widen the search to another app');
        t_equal(['alice@example.test'], $emails($model->findAllCustomersByApp(['app_id' => 1, 'search_value' => 'alice'], ['limit' => '1', 'offset' => '0'])), 'limit and offset');
        t_equal(['alice@example.test'], $emails($model->findAllCustomersByApp(['app_id' => 1], ['limit' => '1; DROP TABLE customer', 'offset' => '0'])), 'a hostile limit is read as the number it starts with');
        t_true(in_array('customer', $db->listTables(), true), 'the table is still there');
    },
];
