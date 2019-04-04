<?php

/**
 * Class Places_Model_Category
 *
 * @method integer getId()
 * @method Places_Model_Db_Table_Category getTable()
 * @method Places_Model_Category[] findAll($values = [], $order = null, $params = [])
 */
class Places_Model_Category extends Core_Model_Default
{
    /**
     * Places_Model_Category constructor.
     * @param array $params
     */
    public function __construct($params = [])
    {
        parent::__construct($params);
        $this->_db_table = 'Places_Model_Db_Table_Category';
        return $this;
    }

    /**
     * @return array|mixed|null|string
     */
    public function getTitle()
    {
        return $this->getData('title');
    }

    /**
     * @return array|mixed|null|string
     */
    public function getSubtitle()
    {
        return $this->getData('subtitle');
    }

    /**
     * @return array|mixed|null|string
     */
    public function getPicture()
    {
        return $this->getData('picture');
    }

    /**
     * @param $valueId
     * @return $this
     */
    public function initPosition($valueId)
    {
        $position = $this->getTable()->getLastPosition($valueId);

        return $this->setData("position", $position["position"] + 1);
    }
}