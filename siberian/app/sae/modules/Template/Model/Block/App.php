<?php

class Template_Model_Block_App extends Core_Model_Default {

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Template_Model_Db_Table_Block_App';
        return $this;
    }

    /**
     * We determine if the Block app, is new scss if it has been edited after a given date
     *
     * "2016-10-17 12:00:00"
     *
     * This is for automatic css cache regeneration (after cache removal)
     */
    public function isNewScss($app_id) {
        $db = Zend_Db_Table::getDefaultAdapter();

        $select = $db->select()
            ->from("template_block_app")
            ->where("updated_at > ?", '2016-10-14 00:00:01')
            ->where("app_id = ?", $app_id)
        ;

        $result = $db->fetchAll($select);

        return (count($result) > 0);
    }


}
