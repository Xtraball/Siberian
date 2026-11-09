<?php
/**
 * Companion of DbFetchFalseTest: Zend_Db_Table_Row_Abstract::findManyToManyRowset() fed PDOStatement::fetchAll() straight
 * into the rowset. When the fetch fails (answers false: connection lost or timed out while reading) the rowset died in
 * count(false), a TypeError that the catch (\Exception) blocks of the callers do not catch. It now throws a
 * Zend_Db_Table_Row_Exception, which is an Exception.
 */

// named classes: the reference map of the link table matches tables by class name
class M2mAuthorTable extends Zend_Db_Table_Abstract
{
    protected $_name = 'm2m_author';
    protected $_primary = 'id';
}

class M2mBookTable extends Zend_Db_Table_Abstract
{
    protected $_name = 'm2m_book';
    protected $_primary = 'id';
}

class M2mLinkTable extends Zend_Db_Table_Abstract
{
    protected $_name = 'm2m_link';
    protected $_primary = ['author_id', 'book_id'];
    protected $_referenceMap = [
        'Author' => ['columns' => 'author_id', 'refTableClass' => 'M2mAuthorTable', 'refColumns' => 'id'],
        'Book' => ['columns' => 'book_id', 'refTableClass' => 'M2mBookTable', 'refColumns' => 'id'],
    ];
}

/** An in-memory adapter whose Zend_Db_Select queries return a statement with a failing fetch once $failing is set */
class M2mFailingFetchAdapter extends Zend_Db_Adapter_Pdo_Sqlite
{
    public $failing = false;

    public function query($sql, $bind = [])
    {
        $stmt = parent::query($sql, $bind);
        if ($this->failing && $sql instanceof Zend_Db_Select) {
            return new class($this, $sql) extends Zend_Db_Statement_Pdo {
                public function fetchAll($style = null, $col = null)
                {
                    return false;
                }
            };
        }

        return $stmt;
    }
}

/** Returns [author row, book table, link table] on a fresh schema: author 1 has books 1 and 2 */
function m2m_fixture(): array
{
    $db = new M2mFailingFetchAdapter(['dbname' => ':memory:']);
    $db->query('CREATE TABLE m2m_author (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT)');
    $db->query('CREATE TABLE m2m_book (id INTEGER PRIMARY KEY AUTOINCREMENT, title TEXT)');
    $db->query('CREATE TABLE m2m_link (author_id INTEGER, book_id INTEGER)');
    $db->query("INSERT INTO m2m_author (name) VALUES ('ann')");
    $db->query("INSERT INTO m2m_book (title) VALUES ('one'), ('two')");
    $db->query('INSERT INTO m2m_link (author_id, book_id) VALUES (1, 1), (1, 2)');

    $authors = new M2mAuthorTable(['db' => $db]);
    $books = new M2mBookTable(['db' => $db]);
    $links = new M2mLinkTable(['db' => $db]);
    // metadata is read once, before the failures start
    $authors->info();
    $books->info();
    $links->info();

    return [$authors->find(1)->current(), $books, $links];
}

return [
    'a healthy many-to-many fetch still returns the rows' => function () {
        [$author, $books, $links] = m2m_fixture();
        t_equal(2, count($author->findManyToManyRowset($books, $links)), 'two books');
    },

    'a failed many-to-many fetch is an Exception, not a count(false) TypeError' => function () {
        [$author, $books, $links] = m2m_fixture();
        $books->getAdapter()->failing = true;
        try {
            $author->findManyToManyRowset($books, $links);
        } catch (Zend_Db_Table_Row_Exception $e) {
            t_true(strpos($e->getMessage(), 'Fetching the many-to-many rows of') === 0, 'the message says what failed');

            return;
        } catch (Throwable $e) {
            throw new RuntimeException('expected a Zend_Db_Table_Row_Exception, got ' . get_class($e) . ': ' . $e->getMessage());
        }
        throw new RuntimeException('no exception');
    },
];
