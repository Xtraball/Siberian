<?php

class Socialgaming_Model_Db_Table_Game extends Core_Model_Db_Table {

    protected $_name = "socialgaming_game";
    protected $_primary = "game_id";

    public function findCurrent($value_id) {
        $select = $this->_prepareSelect($value_id);
        $select->order('main.created_at ASC');

        return $this->fetchRow($select);
    }

    public function findNext($value_id) {
        $select = $this->_prepareSelect($value_id);
        $select->order('main.created_at DESC');

        $last = $this->fetchRow($select);
        $current = $this->findCurrent($value_id);
        if($current AND $last AND $current->getId() == $last->getId()) {
            $last = null;
        }

        return $last;
    }

    protected function _prepareSelect($value_id) {
        return $this->select()
            ->from(array('main' => $this->_name))
            ->where('(main.end_at >= NOW() OR main.end_at IS NULL)')
            ->where('value_id = ?', $value_id)
            ->limit(1)
        ;
    }
}
