<?php

class Job_Model_Place extends Core_Model_Default {

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Job_Model_Db_Table_Place';
        return $this;
    }

    /**
     * @param $values
     * @param $order
     * @param $params
     * @return mixed
     */
    public function findActive($values, $order, $params) {
        return $this->getTable()->findActive($values, $order, $params);
    }

    public function getTitle() {
        return $this->title;
    }

    public function setTitle($title) {
        $this->title = $title;
    }

    public function getIcon() {
        return $this->icon;
    }

    public function setIcon($icon) {
        $this->icon = $icon;
    }

    /**
     * @return mixed
     */
    public function toggle() {
        $this->setIsActive(!$this->getIsActive())->save();

        return $this->getIsActive();
    }

    public function enable() {
        $this->is_active = true;
    }

    public function disable() {
        $this->is_active = false;
    }

    public function save() {
        parent::save();
    }
}