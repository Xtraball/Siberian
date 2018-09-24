<?php

/**
 * Class Api_Backoffice_User_ListController
 */
class Api_Backoffice_User_ListController extends Backoffice_Controller_Default
{
    /**
     *
     */
    public function loadAction()
    {
        $payload = [
            'title' => sprintf('%s > %s > %s',
                __('Manage'),
                __('Backoffice access'),
                __('Api Users')
            ),
            'icon' => 'fa-users',
        ];

        $this->_sendJson($payload);
    }

    /**
     *
     */
    public function findallAction()
    {

        $user = new Api_Model_User();
        $users = $user->findAll();
        $payload = ["users" => []];

        foreach ($users as $user) {
            $user->setCreatedAt($user->getFormattedCreatedAt());
            $payload["users"][] = $user->getData();
        }

        $this->_sendJson($payload);
    }

    public function deleteAction()
    {
        if ($data = Zend_Json::decode($this->getRequest()->getRawBody())) {

            try {

                if (empty($data["user_id"])) {
                    throw new Exception($this->_("An error occurred while saving. Please try again later."));
                }

                $user = new Api_Model_User();
                $user->find($data["user_id"]);

                if (!$user->getId()) {
                    throw new Exception($this->_("An error occurred while saving. Please try again later."));
                }

                $user->delete();

                $payload = [
                    "success" => 1,
                    "message" => $this->_("User successfully deleted")
                ];

            } catch (Exception $e) {
                $payload = [
                    "error" => 1,
                    "message" => $e->getMessage()
                ];
            }

            $this->_sendJson($payload);
        }
    }

}
