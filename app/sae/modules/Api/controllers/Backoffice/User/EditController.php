<?php

class Api_Backoffice_User_EditController extends Backoffice_Controller_Default
{

    public function loadAction() {

        $html = array(
            "title" => $this->_("User"),
            "icon" => "fa-user",
        );

        $this->_sendHtml($html);

    }

    public function findAction() {

        $user = new Api_Model_User();
        $user->find($this->getRequest()->getParam("user_id"));

        $data = array();
        if($user->getId()) {
            $data["user"] = $user->getData();
            $data["section_title"] = $this->_("Edit the user %s", $user->getUsername());
        } else {
            $data["section_title"] = $this->_("Create a new user");
        }

        $this->_sendHtml($data);

    }

    public function saveAction() {

        if($data = Zend_Json::decode($this->getRequest()->getRawBody())) {

            try {

                $user = new Api_Model_User();
                $dummy = new Api_Model_User();
                $dummy->find($data["username"], "username");
                $isNew = true;
                $data["confirm_password"] = !empty($data["confirm_password"]) ? $data["confirm_password"] : "";

                if(!empty($data["id"])) {
                    $user->find($data["id"]);
                    $isNew = !$user->getId();
                }

                if($isNew AND empty($data["password"])) {
                    throw new Exception($this->_("Please, enter a password."));
                }
                if(empty($data["password"]) AND empty($data["confirm_password"])) {
                    unset($data["password"]);
                    unset($data["confirm_password"]);
                }
                if(!empty($data["password"]) AND $data["password"] != $data["confirm_password"]) {
                    throw new Exception($this->_("Passwords don't match"));
                }

                $user->addData($data);

                if($dummy->getUsername() == $user->getUsername() AND $dummy->getId() != $user->getId()) {
                    throw new Exception($this->_("We are sorry but this username already exists."));
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
