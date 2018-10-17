<?php

/**
 * Class Backoffice_Tour_SettingsController
 */
class Backoffice_Tour_SettingsController extends Backoffice_Controller_Default
{

    public function loadAction()
    {
        $admin = $this->getSession()->getBackofficeUser();

        $admin_obj = new Admin_Model_Admin();
        $admin_obj->findByEmail($admin->getEmail());

        $tour_status = System_Model_Config::getValueFor("bootstraptour_active") == "1";
        $tour = [
            "is_active" => $tour_status,
            "bt_disabled" => $admin_obj->getEmail() ? false : true,
            "label_disabled" => __("Your account doesn't exists on editor side. Please create it before using tour edition.")
        ];

        $data = [
            "admin" => $admin->getData(),
            "tour" => $tour,
            "header" => [
                "title" => sprintf('%s > %s',
                    __('Appearance'),
                    __('Tour settings')),
                "icon" => "fa fa-globe"
            ]
        ];
        $this->_sendJson($data);
    }

    public function loginasAction()
    {

        if ($admin_email = $this->getRequest()->getParam("email")) {

            $admin = new Admin_Model_Admin();
            $admin->findByEmail($admin_email);

            if ($admin->getId()) {
                $front_session = $this->getSession();
                $front_session->resetInstance()->setAdmin($admin);
                $html = [
                    "success" => 1,
                    "url" => $this->getUrl(),
                    "message" => "ok"
                ];
            } else {
                $html = [
                    "error" => 1,
                    "message" => __("Your account doesn't exists on editor side. Please create it before using tour edition.")
                ];
            }

            $this->_sendHtml($html);

        }

    }

    public function setstatusAction()
    {
        if ($data = Zend_Json::decode($this->getRequest()->getRawBody())) {
            $new_status = $data["status"] ? "1" : "0";
            System_Model_Config::setValueFor("bootstraptour_active", $new_status);

            $html = [
                "success" => 1,
                "message" => __("Tour status successfully saved.")
            ];

            $this->_sendHtml($html);
        } else {
            $html = [
                "error" => 1,
                "message" => __("An error occurred during the process. Please try again later.")
            ];

            $this->_sendHtml($html);
        }
    }
}