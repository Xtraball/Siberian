<?php

class Application_Model_Db_Table_Tag extends Core_Model_Db_Table
{
    protected $_name = "application_tag";
    protected $_primary = "tag_id";
    protected $_modelClass = "Application_Model_Tag";
}
