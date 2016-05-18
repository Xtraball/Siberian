<?php

class Backoffice_Account_ListController extends Backoffice_Controller_Default
{

    public function loadAction() {

        $html = array(
            "title" => $this->_("Accounts"),
            "icon" => "fa-users",
        );

        $this->_sendHtml($html);

    }

    public function findallAction() {

        $user = new Backoffice_Model_User();
        $users = $user->findAll();
        $data = array("users" => array());

        foreach($users as $user) {
            $data["users"][] = array(
                "id" => $user->getId(),
                "email" => $user->getEmail(),
                "created_at" => $user->getFormattedCreatedAt($this->_("MM/dd/yyyy"))
            );
        }
        $this->_sendHtml($data);
    }

    public function deleteAction() {

        if($data = Zend_Json::decode($this->getRequest()->getRawBody())) {

            try {

                if (empty($data["user_id"])) {
                    throw new Exception($this->_("An error occurred while saving. Please try again later."));
                }

                $user = new Backoffice_Model_User();
                $user->find($data["user_id"]);

                if (!$user->getId()) {
                    throw new Exception($this->_("An error occurred while saving. Please try again later."));
                }

                if ($user->findAll()->count() <= 1) {
                    throw new Exception($this->_("How do you want to access the backoffice if you remove the only user remaining?"));
                }

                $user->delete();

                $data = array(
                    "success" => 1,
                    "message" => $this->_("User successfully deleted")
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
