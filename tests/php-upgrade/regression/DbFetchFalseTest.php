<?php
/**
 * A failed fetch (PDOStatement::fetchAll() answers false when the connection is lost or times out while the result is
 * read, without an exception) went straight into the rowset: count(false) is a TypeError on PHP 8, an Error that the
 * catch (\Exception) blocks of the callers do not catch (production error group
 * "count(): Argument #1 ($value) must be of type Countable|array, false given" in Zend/Db/Table/Rowset/Abstract.php).
 * Zend_Db_Table_Abstract::_fetch() now throws a Zend_Db_Table_Exception, which is an Exception.
 */

/** A statement whose fetch fails the way a lost connection does */
function fetch_false_statement(Zend_Db_Adapter_Abstract $db, $sql): Zend_Db_Statement_Pdo
{
    return new class($db, $sql) extends Zend_Db_Statement_Pdo {
        public function fetchAll($style = null, $col = null)
        {
            return false;
        }
    };
}

/** An in-memory database whose adapter fails the fetch of every select when $failing is true */
function fetch_false_table(bool $failing): Zend_Db_Table_Abstract
{
    $db = new class(['dbname' => ':memory:']) extends Zend_Db_Adapter_Pdo_Sqlite {
        public $failing = false;

        public function query($sql, $bind = [])
        {
            if ($this->failing && $sql instanceof Zend_Db_Select) {
                return fetch_false_statement($this, $sql);
            }

            return parent::query($sql, $bind);
        }
    };
    $db->query('CREATE TABLE fetch_false (id INTEGER PRIMARY KEY AUTOINCREMENT, label TEXT)');
    $db->query("INSERT INTO fetch_false (label) VALUES ('a'), ('b')");

    $table = new class(['db' => $db, 'name' => 'fetch_false', 'primary' => 'id']) extends Zend_Db_Table_Abstract {
    };
    // metadata is read once, before the failures start
    $table->info();
    $db->failing = $failing;

    return $table;
}

return [
    'a healthy fetch still returns the rows' => function () {
        $table = fetch_false_table(false);
        t_equal(2, count($table->fetchAll()), 'fetchAll');
        t_equal('a', $table->fetchRow()->label, 'fetchRow');
    },

    'a failed fetchAll() is an Exception, not a count(false) TypeError' => function () {
        $table = fetch_false_table(true);
        try {
            $table->fetchAll();
        } catch (Zend_Db_Table_Exception $e) {
            t_true(strpos($e->getMessage(), 'Fetching the rows of') === 0, 'the message says what failed');

            return;
        } catch (Throwable $e) {
            throw new RuntimeException('expected a Zend_Db_Table_Exception, got ' . get_class($e) . ': ' . $e->getMessage());
        }
        throw new RuntimeException('no exception');
    },

    'a failed fetchRow() is an Exception too' => function () {
        $table = fetch_false_table(true);
        try {
            $table->fetchRow();
        } catch (Zend_Db_Table_Exception $e) {
            return;
        } catch (Throwable $e) {
            throw new RuntimeException('expected a Zend_Db_Table_Exception, got ' . get_class($e) . ': ' . $e->getMessage());
        }
        throw new RuntimeException('no exception');
    },
];
