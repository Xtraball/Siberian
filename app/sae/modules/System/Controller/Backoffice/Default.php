<?php

class System_Controller_Backoffice_Default extends Backoffice_Controller_Default {

    public function findallAction() {

        $data = $this->_findconfig();
        $this->_sendHtml($data);

    }

    public function saveAction() {

        if($data = Zend_Json::decode($this->getRequest()->getRawBody())) {

            try {

                $this->_save($data);

                $data = array(
                    "success" => 1,
                    "message" => $this->_("Info successfully saved")
                );
            } catch(Exception $e) {
                $data = array(
                    "error" => 1,
                    "message" => $e->getMessage()
                );
            }

            $this->_sendHtml($data);

        }

    }

    protected function _findconfig() {

        $config = new System_Model_Config();
        $values = $config->findAll(array("code IN (?)" => $this->_codes));

        $data = array();
        foreach($this->_codes as $code) {
            $data[$code] = array();
        }

        foreach($values as $value) {
            $data[$value->getCode()] = array(
                "code" => $value->getCode(),
                "label" => $this->_($value->getLabel()),
                "value" => $value->getValue()
            );
        }

        return $data;
    }

    protected function _save($data) {

        foreach($data as $code => $values) {

            if(!in_array($code, $this->_codes)) continue;
            if($code == "favicon") continue;
            $config = new System_Model_Config();
            $config->find($code, "code");
            $config->setValue($values["value"])->save();
        }

        return $this;
    }

}
