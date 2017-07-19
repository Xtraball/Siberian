<?php

class Padlock_Mobile_ViewController extends Application_Controller_Mobile_Default {

    public function init() {

        parent::init();

        $id = $this->getRequest()->getParam('value_id');

        if($id) {
            // Créé et charge l'objet
            $this->_current_option_value = new Application_Model_Option_Value();

            if($id != "homepage") {
                $this->_current_option_value->find($id);
                // Récupère le layout de l'option_value en cours
                if($this->_current_option_value->getLayoutId()) {
                    $this->_layout_id = $this->_current_option_value->getLayoutId();
                }
            } else {
                $this->_current_option_value->setIsHomepage(true);
            }

        } else {
            $this->_current_option_value = $this->getApplication()->getOption("padlock");
            if($this->_current_option_value->getLayoutId()) {
                $this->_layout_id = $this->_current_option_value->getLayoutId();
            }
        }

        Core_View_Mobile_Default::setCurrentOption($this->_current_option_value);

        $this->_log();

        return $this;
    }

    public function findAction() {

        $unlock_by = explode("|", $this->getApplication()->getUnlockBy());

        $unlock_by_account = $unlock_by_qrcode = 0;
        foreach($unlock_by as $value) {
            if($value == "account") {
                $unlock_by_account = 1;
            } else if($value == "qrcode") {
                $unlock_by_qrcode = 1;
            }
        }

        $option = $this->getCurrentOptionValue();

        $payload = array(
            "page_title"    => $option->getTabbarName(),
            "description"   => $option->getObject()->getDescription()
        );

        $this->_sendJson($payload);
    }
    public function findunlocktypesAction() {

        $unlock_by = explode("|", $this->getApplication()->getUnlockBy());

        $unlock_by_account = $unlock_by_qrcode = 0;
        foreach($unlock_by as $value) {
            if($value == "account") {
                $unlock_by_account = 1;
            } else if($value == "qrcode") {
                $unlock_by_qrcode = 1;
            }
        }

        $payload = array(
            "unlock_by_account" => $unlock_by_account,
            "unlock_by_qrcode" => $unlock_by_qrcode
        );

        $this->_sendJson($payload);
    }

    public function unlockbyqrcodeAction() {

        try {

            if ($data = Zend_Json::decode($this->getRequest()->getRawBody())) {

                if($this->getApplication()->getUnlockCode() != $data["qrcode"]) {
                    throw new Exception($this->_("This code is unrecognized"));
                }

                $payload = array(
                    "success" => 1,
                );

            }
        } catch(Exception $e) {
            $payload = array(
                "error" => 1,
                "message" => $e->getMessage()
            );
        }

        $this->_sendJson($payload);
    }
}