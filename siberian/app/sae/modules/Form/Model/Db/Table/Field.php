<?php

class Form_Model_Db_Table_Field extends Core_Model_Db_Table {

    protected $_name = "form_field";
    protected $_primary = "field_id";
    
    /**
     * Recherche par section_id
     * 
     * @param int $section_id
     * @return object
     */
    public function findBySectionId($section_id) {

        $select = $this->select()
            ->from(array('cc' => $this->_name))
            ->where('cc.section_id = ?', $section_id)
            ->order('cc.position ASC');

        return $this->fetchAll($select);
    }
    
    /**
     * Update la position des champs
     * 
     * @param array $ids
     * @return object
     */
    public function updatePosition($ids) {
    	foreach($ids as $pos => $id) {
    		$this->_db->update($this->_name, array('position' => $pos), array('field_id = ?' => $id));
    	}

    	return $this;
    }
}