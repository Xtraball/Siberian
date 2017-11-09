<?php

class Api_Backoffice_User_EditController extends Backoffice_Controller_Default
{

    public function loadAction() {

        $html = array(
            "title" => __("User"),
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
            $acl = Siberian_Json::decode($user->getAcl());
            foreach(Siberian_Api::$acl_keys as $key => $subkeys) {
                if(!isset($acl[$key])) {
                    $acl[$key] = array();
                }

                if(is_array($acl[$key])) {
                    foreach($subkeys as $subkey => $subvalue) {
                        if(!array_key_exists($subkey, $acl[$key])) {
                            $acl[$key][$subkey] = false;
                        }
                    }
                }
            }
            $data["user"]["acl"] = $acl;

            $data["section_title"] = __("Edit the user %s", $user->getUsername());
        } else {
            $data["section_title"] = __("Create a new user");
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
                    throw new Exception(__("Please, enter a password."));
                }
                if(empty($data["password"]) AND empty($data["confirm_password"])) {
                    unset($data["password"]);
                    unset($data["confirm_password"]);
                }
                if(!empty($data["password"]) AND $data["password"] != $data["confirm_password"]) {
                    throw new Exception(__("Passwords don't match"));
                }

                $user->addData($data);

                if($dummy->getUsername() == $user->getUsername() AND $dummy->getId() != $user->getId()) {
                    throw new Exception(__("We are sorry but this username already exists."));
                }

                if(!empty($data["password"])) {
                    $user->setPassword($data["password"]);
                }

                # Save ACL
                $user->setAcl(Siberian_Json::encode($data["acl"]));

                $user->save();

                $data = array(
                    "success" => 1,
                    "message" => __("User successfully saved")
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
