<?php

class Push_Backoffice_CertificateController extends Backoffice_Controller_Default
{

    public function loadAction() {

        $html = array(
            "title" => $this->_("Push Notifications"),
            "icon" => "fa-comment-o",
        );

        $this->_sendHtml($html);

    }

    public function findallAction() {

        $data = array(
            "title" => '<i class="fa fa-android"></i> Android',
            "keys" => array(
                array(
                    "title" => "GCM Key",
                    "name" => "android_key",
                    "value" => Push_Model_Certificate::getAndroidKey()
                ), array(
                    "title" => "Sender ID",
                    "name" => "android_sender_id",
                    "value" => Push_Model_Certificate::getAndroidSenderId()
                )
            )
        );


        $this->_sendHtml($data);
    }

    public function saveAction() {

        if($data = Zend_Json::decode($this->getRequest()->getRawBody())) {

            try {

                foreach($data as $key) {
                    $certificate = new Push_Model_Certificate();
                    $certificate->find($key["name"], "type");
                    if(!$certificate->getId()) {
                        $certificate->setType($key["name"]);
                    }

                    $certificate->setPath($key["value"])
                        ->save()
                    ;
                }

                $data = array(
                    "success" => 1,
                    "message" => $this->_("Keys successfully saved")
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

}
