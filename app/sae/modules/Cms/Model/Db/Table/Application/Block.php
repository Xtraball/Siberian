<?php

class Cms_Model_Db_Table_Application_Block extends Core_Model_Db_Table {

    protected $_name = "cms_application_block";
    protected $_primary = "block_id";


    public function findByPage($page_id) {

        // Récupère les sous-tables
        $subtables = $this->_db->fetchCol($this->_db->select()->from('cms_application_block', array('type')));
        $blocks = array();
        foreach($subtables as $subtable) {
            $select = $this->select()
                ->from(array('cap' => 'cms_application_page'), array())
                ->join(array('capb' => 'cms_application_page_block'), 'capb.page_id = cap.page_id', array('position'))
                ->join(array('cab' => $this->_name), 'cab.block_id = capb.block_id', array('block_id', 'type', 'template', 'mobile_template'))
                ->join(array($subtable => 'cms_application_page_block_'.$subtable), "$subtable.value_id = capb.value_id")
                ->where('cap.page_id = ?', $page_id)
                ->order('capb.position ASC')
                ->setIntegrityCheck(false)
            ;

            foreach($this->fetchAll($select) as $block) {
                $blocks[$block->getPosition()] = $block;
            }
        }

        ksort($blocks);

        return $blocks;

    }
}