<?php

namespace Cabride\Model\Db\Table;

use Core_Model_Db_Table as DbTable;
use Zend_Db_Expr as DbExpr;
use Zend_Db_Table_Row_Abstract;

/**
 * Class Field
 * @package Cabride\Model\Db\Table
 */
class Field extends DbTable
{
    /**
     * @var string
     */
    protected $_name = "cabride_form_field";

    /**
     * @var string
     */
    protected $_primary = "field_id";

    /**
     * @param $valueId
     * @return null|Zend_Db_Table_Row_Abstract
     */
    public function getLastPosition($valueId)
    {
        $select = $this
            ->select()
            ->from($this->_name, ["position" => new DbExpr("MAX(position)")])
            ->where("value_id = ?", $valueId)
            ->limit(1);

        return $this->fetchRow($select);
    }
}