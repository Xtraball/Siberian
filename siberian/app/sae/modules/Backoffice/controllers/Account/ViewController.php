<?php

/**
 * Class Backoffice_Account_ViewController
 */
class Backoffice_Account_ViewController extends Backoffice_Controller_Default
{

    public function loadAction()
    {
        $payload = [
            "title" => sprintf('%s > %s > %s',
                __('Manage'),
                __('Backoffice access'),
                __('Account')),
            "icon" => "fa-users",
        ];

        $this->_sendJson($payload);
    }

    public function findAction()
    {
        $request = $this->getRequest();
        $user = (new Backoffice_Model_User())
            ->find($request->getParam('user_id'));

        $data = [];
        if ($user->getId()) {
            $data["user"] = ["id" => $user->getId(), "email" => $user->getEmail()];
            $data["section_title"] = __("Edit the user");
        } else {
            $data["section_title"] = __("Create a user");
        }

        $this->_sendJson($data);
    }

    public function saveAction()
    {

        if ($data = Zend_Json::decode($this->getRequest()->getRawBody())) {

            try {

                if (__getConfig('is_demo')) {
                    // Demo version
                    throw new Exception("This is a demo version, these changes can't be saved");
                }

                if (!Zend_Validate::is($data["email"], "emailAddress")) {
                    throw new Exception(__("Please, enter a correct email address."));
                }

                $user = new Backoffice_Model_User();
                $dummy = new Backoffice_Model_User();
                $dummy->find($data["email"], "email");
                $isNew = true;
                if (!empty($data["id"])) {
                    $user->find($data["id"]);
                    $isNew = !$user->getId();
                }

                $user->addData($data);

                if ($dummy->getEmail() == $user->getEmail() && $dummy->getId() != $user->getId()) {
                    throw new Exception(__("We are sorry but this email address already exists."));
                }

                if ($isNew AND empty($data["password"])) {
                    throw new Exception(__("Please, enter a password."));
                }
                if (!empty($data["password"]) && $data["password"] != $data["confirm_password"]) {
                    throw new Exception(__("Passwords don't match"));
                }

                if (!empty($data["password"])) {
                    $user->setPassword($data["password"]);
                }

                $user->save();

                $data = [
                    "success" => 1,
                    "message" => __("User successfully saved")
                ];

            } catch (Exception $e) {
                $data = [
                    "error" => 1,
                    "message" => $e->getMessage()
                ];
            }

            $this->_sendJson($data);
        }

    }

}
