<?php

class Api_Backoffice_User_ListController extends Backoffice_Controller_Default
{

    public function loadAction() {

        $html = array(
            "title" => __("Api Users"),
            "icon" => "fa-users",
        );

        $this->_sendHtml($html);

    }

    public function findallAction() {

        $user = new Api_Model_User();
        $users = $user->findAll();
        $data = array("users" => array());

        foreach($users as $user) {
            $user->setCreatedAt($user->getFormattedCreatedAt());
            $data["users"][] = $user->getData();
        }

        $this->_sendHtml($data);
    }

    public function deleteAction() {

        if ($data = Zend_Json::decode($this->getRequest()->getRawBody())) {

            try {

                if(empty($data["user_id"])) {
                    throw new Exception($this->_("An error occurred while saving. Please try again later."));
                }

                $user = new Api_Model_User();
                $user->find($data["user_id"]);

                if (!$user->getId()) {
                    throw new Exception($this->_("An error occurred while saving. Please try again later."));
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
