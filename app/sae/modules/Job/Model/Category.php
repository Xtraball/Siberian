<?php

class Job_Model_Category extends Core_Model_Default {

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Job_Model_Db_Table_Category';
        return $this;
    }

    /**
     * @return mixed
     */
    public function toggle() {
        $this->setIsActive(!$this->getIsActive())->save();

        return $this->getIsActive();
    }

    /**
     * @param bool $relative
     * @return string
     */
    public function _getIcon() {
        return $this->__getBase64Image($this->getIcon());
    }

    /**
     * @param $base64
     * @param $option
     * @return $this
     */
    public function _setIcon($base64, $option) {
        $icon_path = $this->__setImageFromBase64($base64, $option);
        $this->setIcon($icon_path);

        return $this;
    }
}