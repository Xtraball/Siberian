<?php

class Template_Model_Db_Table_Design extends Core_Model_Db_Table {

    protected $_name = "template_design";
    protected $_primary = "design_id";

    public function findAllWithCategory() {

        $select = $this->_db->select()
            ->from( ['td' =>'template_design'])
            ->joinLeft( ['tdc' => 'template_design_category'], 'td.design_id = tdc.design_id')
        ;

        return $this->_db->fetchAll($select);
    }
}
