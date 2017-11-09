<?php

class Padlock_Model_Db_Table_Padlock extends Core_Model_Db_Table {

    protected $_name = "padlock";
    protected $_primary = "padlock_id";

    public function findValueIds($app_id) {

        $select = $this->_db->select()
            ->from('padlock_value', array('value_id'))
            ->where('app_id = ?', $app_id)
        ;

        return $this->_db->fetchCol($select);

    }

    public function saveValueIds($app_id, $value_ids) {

        try {

            $this->beginTransaction();
            $this->_db->delete("padlock_value", array('app_id = ?' => $app_id));

            foreach($value_ids as $value_id) {
                $data = array('app_id' => $app_id, 'value_id' => $value_id);
                $this->_db->insert("padlock_value", $data);
            }
            $this->commit();

        } catch (Exception $e) {
            $this->rollback();
        }

    }

}