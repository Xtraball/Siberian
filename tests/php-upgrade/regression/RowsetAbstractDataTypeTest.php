<?php
/**
 * Zend_Db_Table_Rowset_Abstract::__construct() assigns $config['data'] to $_data as-is, then does
 * `count($this->_data)` a few lines later. A failed fetch upstream (bad FK, a corrupt row, a query that
 * errored and returned false instead of rows) can hand this `false`, which used to make count() just warn
 * and return 0; PHP 8 makes it an uncaught TypeError instead (production error group "Uncaught TypeError:
 * count(): Argument #1 ($value) must be of type Countable|array, false given" in
 * lib/Zend/Db/Table/Rowset/Abstract.php:129 — seen live on two installs, 46 occurrences since 5.1.13).
 */
$siberian = dirname(__DIR__, 3) . '/siberian';
require_once $siberian . '/lib/Zend/Db/Table/Rowset/Abstract.php';

class RowsetDataTypeRow
{
    public $config;

    public function __construct($config = array())
    {
        $this->config = $config;
    }

    public function toArray()
    {
        return $this->config['data'];
    }
}

class RowsetDataTypeFixture extends Zend_Db_Table_Rowset_Abstract
{
}

return [
    'non-array data (a failed upstream fetch) does not fatal, and the rowset is empty' => function () {
        $rowset = new RowsetDataTypeFixture([
            'rowClass' => RowsetDataTypeRow::class,
            'data'     => false,
        ]);
        t_equal(0, count($rowset), 'count() no longer throws, and reports no rows');
        t_true(!$rowset->valid(), 'nothing to iterate');
        t_true(!isset($rowset[0]), 'array access sees no rows either');
    },

    'real row data is kept and iterated, unchanged behaviour' => function () {
        $rowset = new RowsetDataTypeFixture([
            'rowClass' => RowsetDataTypeRow::class,
            'data'     => [['id' => 1], ['id' => 2]],
        ]);
        t_equal(2, count($rowset), 'both rows counted');
        t_true($rowset[0] instanceof RowsetDataTypeRow, 'row objects are built on access');
        t_equal(['id' => 1], $rowset[0]->config['data'], 'row data passed through');
    },
];
