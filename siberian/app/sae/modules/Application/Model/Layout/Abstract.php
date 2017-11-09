<?php

abstract class Application_Model_Layout_Abstract extends Core_Model_Default {

    const DEFAULT_CODE = 1;

    public function getCode() {
        if($this->getId()) return $this->getData('code');
        else return self::DEFAULT_CODE;
    }

}
