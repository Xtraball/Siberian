<?php

/**
 * Class Places_Model_Db_Table_Category
 */
class Places_Model_Db_Table_Category extends Core_Model_Db_Table
{
    /**
     * @var string
     */
    protected $_name = 'place_category';

    /**
     * @var string
     */
    protected $_primary = 'category_id';

    /**
     * @param $valueId
     * @return null|Zend_Db_Table_Row_Abstract
     */
    public function getLastPosition($valueId)
    {
        $select = $this
            ->select()
            ->from($this->_name, ["position" => new Zend_Db_Expr("MAX(position)")])
            ->where("value_id = ?", $valueId)
            ->limit(1);

        return $this->fetchRow($select);
    }
}