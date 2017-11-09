<?php

class Application_Model_Db_Table_TagOption extends Core_Model_Db_Table
{
    protected $_name = "application_tagoption";
    protected $_primary = "tagoption_id";
    protected $_modelClass = "Application_Model_Tag";

    protected $_referenceMap    = array(
        'Tag' => array(
            'columns'           => array('tag_id'),
            'refTableClass'     => 'Application_Model_Db_Table_Tag',
            'refColumns'        => array('tag_id')
        ),
        'Option_Value' => array(
            'columns'           => array('value_id'),
            'refTableClass'     => 'Application_Model_Db_Table_Option_Value',
            'refColumns'        => array('value_id')
        )
    );

}