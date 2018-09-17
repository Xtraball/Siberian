<?php

/**
 * Class Template_Model_Design_Content
 */
class Template_Model_Design_Content extends Core_Model_Default
{
    /**
     * @var
     */
    protected $_blocks;

    /**
     * Template_Model_Design_Content constructor.
     * @param array $params
     * @throws Zend_Exception
     */
    public function __construct($params = [])
    {
        parent::__construct($params);
        $this->_db_table = 'Template_Model_Db_Table_Design_Content';
        return $this;
    }

}
