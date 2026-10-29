<?php
/**
 * #851: the QRCode of an employee encodes a fixed unlock code, so a photo of it is enough to claim loyalty points for
 * ever. A one-time QRCode encodes a random token that is valid for a short time and consumed by its first scan.
 * The fixed code keeps working unless the config option loyalty_card_one_time_qr_only is true.
 */
$siberian = dirname(__DIR__, 3) . '/siberian';
require_once $siberian . '/lib/Siberian/LoyaltyCard/QrToken.php';

use Siberian\LoyaltyCard\QrToken;

/** An in-memory database with the columns of resources/db/schema/loyalty_card_qr_token.php */
function qr_db(): Zend_Db_Adapter_Abstract
{
    $db = Zend_Db::factory('Pdo_Sqlite', ['dbname' => ':memory:']);
    $db->query('CREATE TABLE loyalty_card_qr_token (
        token_id INTEGER PRIMARY KEY AUTOINCREMENT, password_id INTEGER NOT NULL, value_id INTEGER NOT NULL,
        token_hash TEXT NOT NULL UNIQUE, expires_at TEXT NOT NULL, used_at TEXT NULL, created_at TEXT NOT NULL)');

    return $db;
}

return [
    '851 a one-time token is accepted once, for the employee it was issued for' => function () {
        $db = qr_db();
        $now = 1700000000;
        $token = QrToken::issue($db, 7, 42, $now);

        t_true(QrToken::isWellFormed($token), 'a token is 32 hexadecimal characters');
        t_equal(7, QrToken::consume($db, $token, 42, $now + 5), 'the first scan wins and names the employee');
        t_equal(null, QrToken::consume($db, $token, 42, $now + 6), 'a photo of it is worthless: the second scan fails');
    },

    '851 an expired token, a token of another feature and anything else are refused' => function () {
        $db = qr_db();
        $now = 1700000000;

        $expired = QrToken::issue($db, 7, 42, $now);
        t_equal(null, QrToken::consume($db, $expired, 42, $now + QrToken::TTL + 1), 'expired');

        $other = QrToken::issue($db, 7, 42, $now);
        t_equal(null, QrToken::consume($db, $other, 43, $now + 5), 'another loyalty card feature');
        t_equal(7, QrToken::consume($db, $other, 42, $now + 5), 'it was not burnt by the failed attempt');

        foreach ([null, '', 'abc', uniqid(), str_repeat('a', 31), str_repeat('z', 32), ['a'], 12345] as $junk) {
            t_equal(null, QrToken::consume($db, $junk, 42, $now), 'junk refused: ' . var_export($junk, true));
        }
        t_equal(null, QrToken::consume($db, QrToken::generate(), 42, $now), 'a well formed token nobody issued');
    },

    '851 only the hash of the token is stored, and expired rows are purged' => function () {
        $db = qr_db();
        $now = 1700000000;
        $token = QrToken::issue($db, 7, 42, $now);

        $row = $db->fetchRow('SELECT * FROM loyalty_card_qr_token');
        t_equal(QrToken::hash($token), $row['token_hash'], 'the hash is stored');
        t_true(strpos(json_encode($row), $token) === false, 'the clear token is nowhere in the row');

        QrToken::issue($db, 7, 42, $now + QrToken::KEEP + QrToken::TTL + 10);
        t_equal('1', (string) $db->fetchOne('SELECT COUNT(*) FROM loyalty_card_qr_token'), 'the old row is gone, the new one stays');
    },

    '851 two scans of the same token can not both win' => function () {
        $db = qr_db();
        $now = 1700000000;
        $token = QrToken::issue($db, 7, 42, $now);
        $wins = 0;
        for ($i = 0; $i < 5; $i++) {
            if (QrToken::consume($db, $token, 42, $now + 1) === 7) {
                $wins++;
            }
        }
        t_equal(1, $wins, 'exactly one winner');
    },

    '851 the unlock by QRCode consumes the token first and honours the one-time only option' => function () use ($siberian) {
        $view = file_get_contents($siberian . '/app/sae/modules/LoyaltyCard/controllers/Mobile/ViewController.php');
        $consume = strpos($view, '\\Siberian\\LoyaltyCard\\QrToken::consume(');
        $fixed = strpos($view, "findByUnlockCode(\$password_entered, \$option_value->getId());\n                }\n");
        t_true($consume !== false, 'the token is consumed');
        t_true($fixed !== false && $consume < $fixed, 'the fixed unlock code is only a fallback after the token');
        t_true(strpos($view, "__getConfig('loyalty_card_one_time_qr_only') !== true") !== false, 'the option is off by default');
        t_equal(1, substr_count($view, 'QrToken::consume('), 'the manual password flow (validateAction) is untouched');
    },

    '851 the employee download is limited to the application and the schema exists' => function () use ($siberian) {
        $module = $siberian . '/app/sae/modules/LoyaltyCard';
        $controller = file_get_contents($module . '/controllers/ApplicationController.php');
        t_true(strpos($controller, 'function onetimeqrcodeAction') !== false, 'the action exists');
        t_true(strpos($controller, '(int) $password->getAppId() !== (int) $this->getApplication()->getId()') !== false, 'another application\'s employee is refused');
        t_true(strpos($controller, "'sendback:' . \$token") !== false, 'same payload format as the fixed QRCode, the app is unchanged');

        $schemas = [];
        require $module . '/resources/db/schema/loyalty_card_qr_token.php';
        $columns = array_keys($schemas['loyalty_card_qr_token']);
        t_equal(['token_id', 'password_id', 'value_id', 'token_hash', 'expires_at', 'used_at', 'created_at'], $columns, 'columns');
        t_true(!empty($schemas['loyalty_card_qr_token']['token_hash']['index']['is_unique']), 'a hash is unique');
    },
];
