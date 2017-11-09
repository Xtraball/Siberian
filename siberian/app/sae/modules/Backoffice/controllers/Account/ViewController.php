<?php

class Backoffice_Account_ViewController extends Backoffice_Controller_Default
{

    public function loadAction() {

        $html = array(
            "title" => $this->_("Account"),
            "icon" => "fa-user",
        );

        $this->_sendHtml($html);

    }

    public function findAction() {

        $user = new Backoffice_Model_User();
        $user->find($this->getRequest()->getParam("user_id"));

        $data = array();
        if($user->getId()) {
            $data["user"] = array("id" => $user->getId(), "email" => $user->getEmail());
            $data["section_title"] = $this->_("Edit the user");
        } else {
            $data["section_title"] = $this->_("Create a user");
        }

        $this->_sendHtml($data);

    }

    public function saveAction() {

        if($data = Zend_Json::decode($this->getRequest()->getRawBody())) {

            try {

                if(!Zend_Validate::is($data["email"], "emailAddress")) {
                    throw new Exception($this->_("Please, enter a correct email address."));
                }

                $user = new Backoffice_Model_User();
                $dummy = new Backoffice_Model_User();
                $dummy->find($data["email"], "email");
                $isNew = true;
                if(!empty($data["id"])) {
                    $user->find($data["id"]);
                    $isNew = !$user->getId();
                }

                $user->addData($data);

                if($dummy->getEmail() == $user->getEmail() AND $dummy->getId() != $user->getId()) {
                    throw new Exception($this->_("We are sorry but this email address already exists."));
                }

                if($isNew AND empty($data["password"])) {
                    throw new Exception($this->_("Please, enter a password."));
                }
                if(!empty($data["password"]) AND $data["password"] != $data["confirm_password"]) {
                    throw new Exception($this->_("Passwords don't match"));
                }

                if(!empty($data["password"])) {
                    $user->setPassword($data["password"]);
                }

                $user->save();

                $data = array(
                    "success" => 1,
                    "message" => $this->_("User successfully saved")
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
