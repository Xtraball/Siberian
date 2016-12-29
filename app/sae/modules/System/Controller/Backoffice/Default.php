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

            if(!in_array($code, $this->_codes)) {
                continue;
            }
            if($code == "favicon") {
                continue;
            }
            $config = new System_Model_Config();
            $config->find($code, "code");
            $config->setValue($values["value"])->save();
        }

        return $this;
    }

    public function generateanalyticsAction() {

        try {

            Analytics_Model_Aggregate::getInstance()->run(time() - 60 * 60 * 24);
            Analytics_Model_Aggregate::getInstance()->run(time());
            Analytics_Model_Aggregate::getInstance()->run(time() + 60 * 60 * 24);

            $data = array(
                "success" => 1,
                "message" => $this->_("Your analytics has been computed.")
            );
        } catch(Exception $e) {
            $data = array(
                "error" => 1,
                "message" => $e->getMessage()
            );
        }

        $this->_sendHtml($data);

    }

    public function generateanalyticsforperiodAction() {

        try {

            $data = Zend_Json::decode($this->getRequest()->getRawBody());
            if(count($data) !== 2 ) {
                throw new Exception("No period sent.");
            }

            $from = new Zend_Date($data['from'], __("MM/dd/yyyy"));
            $to = new Zend_Date($data['to'], __("MM/dd/yyyy"));

            $fromTimestamp = $from->toValue();
            $toTimestamp = $to->toValue();

            if($fromTimestamp > $toTimestamp) {
                throw new Exception("Invalid period, end date is before start date.");
            }

            if($toTimestamp - $fromTimestamp > 60 * 60 * 24 * 31) {
                throw new Exception("Period to long, please select less than one month.");
            }

            $currentTimestamp = $fromTimestamp;
            while($currentTimestamp <= $toTimestamp) {
                Analytics_Model_Aggregate::getInstance()->run($currentTimestamp);
                $currentTimestamp += 60 * 60 * 24;
            }

            $data = array(
                "success" => 1,
                "message" => $this->_("Your analytics has been computed.")
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
