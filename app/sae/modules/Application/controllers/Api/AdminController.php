<?php

class Application_Api_AdminController extends Api_Controller_Default {

    private $__application;
    private $__admin;

    public function addAction() {

        if($data = $this->getRequest()->getPost()) {

            try {

                $this->__checkParams($data);

                $this->__application->addAdmin($this->__admin)
                    ->save()
                ;

                $data = array("success" => 1);

            } catch(Exception $e) {
                $data = array(
                    "error" => 1,
                    "message" => $e->getMessage()
                );
            }

            $this->_sendHtml($data);

        }

    }

    public function removeAction() {

        if($data = $this->getRequest()->getPost()) {

            try {

                $this->__checkParams($data);

                $this->__application->removeAdmin($this->__admin)
                    ->save()
                ;

                $data = array("success" => 1);

            } catch(Exception $e) {
                $data = array(
                    "error" => 1,
                    "message" => $e->getMessage()
                );
            }

            $this->_sendHtml($data);

        }

    }

    private function __checkParams($data) {

        if(empty($data["admin_id"])) {
            throw new Exception($this->_("The admin_id parameter is required"));
        }
        if(empty($data["app_id"])) {
            throw new Exception($this->_("The app_id parameter is required"));
        }

        $this->__admin = new Admin_Model_Admin();
        $this->__admin->find($data["admin_id"]);

        if(!$this->__admin->getId()) {
            throw new Exception($this->_("This admin does not exist"));
        }

        $this->__application = new Application_Model_Application();
        $this->__application->find($data["app_id"]);

        if(!$this->__application->getId()) {
            throw new Exception($this->_("This application does not exist"));
        }

    }

}
