<?php
class Message_Model_Application_File extends Core_Model_Default
{

    public function __construct($params = array())
    {
        parent::__construct($params);
        $this->_db_table = 'Message_Model_Db_Table_Application_File';
        return $this;
    }

}