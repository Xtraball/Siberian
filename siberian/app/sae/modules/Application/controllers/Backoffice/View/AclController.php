<?php

/**
 * Class Application_Backoffice_View_AclController
 */
class Application_Backoffice_View_AclController extends Backoffice_Controller_Default
{

    public function loadAction()
    {
        $payload = [
            "title" => sprintf('%s > %s > %s',
                __('Manage'),
                __('Application'),
                __('Manage Access')),
            "icon" => "fa-user",
        ];

        $this->_sendJson($payload);
    }

    public function findaccessAction()
    {
        if ($data = Zend_Json::decode($this->getRequest()->getRawBody())) {
            try {

                if (empty($data["admin_id"]) OR empty($data["app_id"])) {
                    throw new Exception(__("An error occurred while saving. Please try again later."));
                }

                $admin = new Admin_Model_Admin();
                $admin->find($data["admin_id"]);

                $app = new Application_Model_Application();
                $app->find($data["app_id"]);

                $app_acl_option = new Application_Model_Acl_Option();
                $forbidden_options = $app_acl_option->findAllByAppId($data["app_id"], $data["admin_id"]);
                $option_tmp = [];
                foreach ($forbidden_options as $option) {
                    $option_tmp[] = $option->getValueId();
                }
                $forbidden_options = $option_tmp;

                $data = [
                    "app_name" => $app->getName(),
                    "user_name" => $admin->getData("firstname") . " " . $admin->getData("lastname"),
                    "can_add_page" => $admin->isAllowedToAddPages($data["app_id"]),
                    "options" => []
                ];

                foreach ($app->getOptions() as $option) {
                    $option_is_allowed = !in_array($option->getValueId(), $forbidden_options);

                    $option_obj = new Application_Model_Option();
                    $option_obj->find($option->getOptionId());

                    if ($option_obj->getId()) {
                        $icon_url = $option_obj->getIconUrl();
                    } else {
                        $icon_url = null;
                    }

                    $data["options"][] = [
                        "value_id" => $option->getValueId(),
                        "icon_url" => $icon_url,
                        "name" => $option->getTabbarName() ? $option->getTabbarName() : $option->getName(),
                        "code" => $option->getCode(),
                        "is_allowed" => $option_is_allowed
                    ];
                }

            } catch (Exception $e) {
                $data = [
                    "error" => 1,
                    "message" => $e->getMessage()
                ];
            }

            $this->_sendHtml($data);
        }
    }

    public function setaddpageAction()
    {
        if ($data = Zend_Json::decode($this->getRequest()->getRawBody())) {
            try {

                if (empty($data["admin_id"]) OR empty($data["app_id"])) {
                    throw new Exception(__("An error occurred while saving. Please try again later."));
                }

                $admin = new Admin_Model_Admin();
                $admin->find($data["admin_id"]);

                $application = new Application_Model_Application();
                $application->find($data["app_id"]);

                if (!$admin->getId() OR !$application->getId()) {
                    throw new Exception(__("An error occurred while saving. Please try again later."));
                }

                $admin->setIsAllowedToAddPages(!empty($data["can_add_page"]));
                $application->addAdmin($admin);
                $admin->save();

                $data = [
                    "success" => 1,
                    "message" => __("Data saved successfully.")
                ];
            } catch (Exception $e) {
                $data = [
                    "error" => 1,
                    "message" => $e->getMessage()
                ];
            }

            $this->_sendHtml($data);
        }
    }

    public function saveaccessAction()
    {
        if ($data = Zend_Json::decode($this->getRequest()->getRawBody())) {
            try {

                if (empty($data["admin_id"]) OR empty($data["app_id"])) {
                    throw new Exception(__("An error occurred while saving. Please try again later."));
                }

                $application_acl_option = new Application_Model_Acl_Option();
                $application_acl_option->deleteAppAclByAdmin($data["app_id"], $data["admin_id"]);

                foreach ($data["options"] as $option) {
                    $application_acl_option = new Application_Model_Acl_Option();
                    $application_acl_option->setAdminId($data["admin_id"])
                        ->setAppId($data["app_id"])
                        ->setValueId($option["value_id"])
                        ->setResourceCode("feature_" . $option["code"])
                        ->save();
                }

                $data = [
                    "success" => 1,
                    "message" => __("Data saved successfully.")
                ];
            } catch (Exception $e) {
                $data = [
                    "error" => 1,
                    "message" => $e->getMessage()
                ];
            }

            $this->_sendHtml($data);
        }
    }

}
