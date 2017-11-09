<?php
class Comment_Model_Radius extends Core_Model_Default {

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Comment_Model_Db_Table_Radius';
        return $this;
    }

    public function find($id, $field = null) {
        parent::find($id, $field);

        if (! $this->getId()) {
            $this->setRadius(10.0);
        }

        return $this;
    }

}
