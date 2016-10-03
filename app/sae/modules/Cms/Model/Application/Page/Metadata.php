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

    /**
     * Converts the data specified by payload to string data
     * Currently support only boolean payload, could be updated in the future, maybe by specifying a payload data type
     *
     * @param  boolean
     * @return $this
     */
    public function setPayload($payload)
    {
        $payload ? $this->_data['payload'] = "true" : $this->_data['payload'] = "false";
        return $this;
    }

    /**
     * Converts the string payload to a boolean value
     * Currently support only boolean payload, could be updated in the future, maybe by specifying a payload data type
     *
     * @return boolean
     */
    public function getPayload()
    {
        if (array_key_exists('payload', $this->_data) && $this->_data['payload'] == "true") {
            return true;
        } else {
            return false;
        }
    }

}