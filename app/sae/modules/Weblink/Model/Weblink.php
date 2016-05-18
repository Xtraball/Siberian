<?php
class Weblink_Model_Weblink extends Core_Model_Default {

    protected $_type_id;

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Weblink_Model_Db_Table_Weblink';
        return $this;
    }

    public function save() {

        if(!$this->getId()) $this->setTypeId($this->_type_id);
        parent::save();

        return $this;
    }

    public function find($id, $field = null) {
        parent::find($id, $field);
        $this->addLinks();
        return $this;
    }

    public function findAll($values = array(), $order = null, $params = array()) {
        $weblinks = $this->getTable()->findAll($values, $order, $params);
        foreach($weblinks as $weblink) {
            $weblink->addLinks();
        }
        return $weblinks;
    }

}
