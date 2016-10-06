<?php

class Job_Model_Company extends Core_Model_Default {

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Job_Model_Db_Table_Company';
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
     * @param bool $base64
     * @return string
     */
    public function _getLogo() {
        return $this->__getBase64Image($this->getLogo());
    }

    /**
     * @param $base64
     * @param $option
     * @return $this
     */
    public function _setLogo($base64, $option) {
        $logo_path = $this->__setImageFromBase64($base64, $option);
        $this->setLogo($logo_path);

        return $this;
    }

    /**
     * @param bool $relative
     * @param bool $base64
     * @return string
     */
    public function _getHeader() {
        return $this->__getBase64Image($this->getHeader());
    }

    /**
     * @param $base64
     * @param $option
     * @return $this
     */
    public function _setHeader($base64, $option) {
        $header_path = $this->__setImageFromBase64($base64, $option);
        $this->setHeader($header_path);

        return $this;
    }
}