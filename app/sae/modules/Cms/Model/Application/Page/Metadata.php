<?php

class Cms_Model_Application_Page_Metadata extends Core_Model_Default
{

    protected $_object;

    public function __construct($params = array())
    {
        parent::__construct($params);
        $this->_db_table = 'Cms_Model_Db_Table_Application_Page_Metadata';
        return $this;
    }

    public function setPayload($payload)
    {
        // Currently support only boolean payload, could be updated in the future, maybe by specifying a payload data type
        $payload ? $this->_data['payload'] = "true" : $this->_data['payload'] = "false";
    }

    public function getPayload()
    {
        if ($this->_data['payload'] == "true") {
            return true;
        } else {
            return false;
        }
    }

}