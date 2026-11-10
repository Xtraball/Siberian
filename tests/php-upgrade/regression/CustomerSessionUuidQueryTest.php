<?php
/**
 * Customer_Model_Db_Table_Customer::clearBySessionUuid() ran
 *     UPDATE customer SET session_uuid = NULL WHERE session_uuid = ':sessionUuid'
 * The placeholder was inside quotes, so it was a string and not a token: one bound value for zero tokens, which the
 * database refuses with "SQLSTATE[HY093]: Invalid parameter number: number of bound variables does not match number of
 * tokens". The call sits on every registration and login of a native app (the editor preview skips it).
 */
$root = dirname(__DIR__, 3) . '/siberian/app/sae/modules';
require_once $root . '/Core/Model/Db/Table.php';
require_once $root . '/Customer/Model/Db/Table/Customer.php';

/** The statements that reach the database adapter, instead of a database */
function spy_adapter(): object
{
    return new class {
        public $queries = [];

        public function query($sql, $bind = [])
        {
            $this->queries[] = [$sql, $bind];
        }
    };
}

function session_uuid_table(object $db): Customer_Model_Db_Table_Customer
{
    $table = (new ReflectionClass(Customer_Model_Db_Table_Customer::class))->newInstanceWithoutConstructor();
    $property = new ReflectionProperty(Zend_Db_Table_Abstract::class, '_db');
    $property->setAccessible(true);
    $property->setValue($table, $db);

    return $table;
}

return [
    'clearBySessionUuid binds its value to a real placeholder' => function () {
        $db = spy_adapter();
        session_uuid_table($db)->clearBySessionUuid('abc123');

        t_equal(1, count($db->queries), 'one statement');
        [$sql, $bind] = $db->queries[0];

        // the tokens of the statement, outside of quoted strings
        $outsideQuotes = preg_replace("/'(?:[^'\\\\]|\\\\.)*'/", "''", $sql);
        $tokens = preg_match_all('/\?|:[a-zA-Z_]\w*/', $outsideQuotes);

        t_equal($tokens, count($bind), "the number of tokens in [$sql] matches the bound values");
        t_equal(['abc123'], array_values($bind), 'the session uuid is the bound value');
        t_true(strpos($sql, "'?'") === false && strpos($sql, "':") === false, 'no placeholder inside quotes');
    },
];
