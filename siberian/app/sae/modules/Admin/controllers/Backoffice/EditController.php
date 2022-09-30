<?php

/**
 * Class Admin_Backoffice_EditController
 */
class Admin_Backoffice_EditController extends Backoffice_Controller_Default
{

    public function loadAction()
    {

        $html = [
            'title' => sprintf('%s > %s > %s',
                __('Manage'),
                __('Editor access'),
                __('User')
            ),
            "icon" => "fa-user",
        ];

        $this->_sendJson($html);

    }

    public function findAction()
    {

        $admin = new Admin_Model_Admin();
        $admin->find($this->getRequest()->getParam("admin_id"));

        $data = [];
        if ($admin->getId()) {
            $data["admin"] = $admin->getData();
            $data["section_title"] = __("Edit the user %s", $admin->getFirstname() . " " . $admin->getLastname());
        } else {
            $data["section_title"] = __("Create a new user");
        }

        $data["applications_section_title"] = __("Manage access");

        $countries = Zend_Registry::get('Zend_Locale')->getTranslationList('Territory', null, 2);
        asort($countries, SORT_LOCALE_STRING);
        $data["country_codes"] = $countries;

        $roles = $admin->getAvailableRole();
        $data["roles"] = $roles;

        $role = new Acl_Model_Role();
        $default_role_id = $role->findDefaultRoleId();
        $data["default_role_id"] = $default_role_id;

        $this->_sendJson($data);

    }

    public function saveAction()
    {

        if ($data = Zend_Json::decode($this->getRequest()->getRawBody())) {

            try {
                // Cleanup empty columns
                $data = array_filter($data);

                if (!Zend_Validate::is($data["email"], "emailAddress")) {
                    throw new \Siberian\Exception(__("Please, enter a correct email address."));
                }

                $admin = new Admin_Model_Admin();
                $dummy = new Admin_Model_Admin();
                $dummy->find($data["email"], "email");
                $isNew = true;
                $data["confirm_password"] = !empty($data["confirm_password"]) ? $data["confirm_password"] : "";

                // Protection for demo mode!
                if (__getConfig('is_demo')) {
                    $protectedEmails = ['client@client.com', 'demo@demo.com'];
                    if (in_array($data['email'], $protectedEmails)) {
                        throw new \Siberian\Exception(__('You are not allowed to edit this account in demo!'));
                    }

                    $editedAdmin = (new Admin_Model_Admin())->find($data['id']);
                    if ($editedAdmin && $editedAdmin->getId() && in_array($editedAdmin->getEmail(), $protectedEmails)) {
                        throw new \Siberian\Exception(__('You are not allowed to edit this account in demo!'));
                    }
                }

                if (!empty($data["id"])) {
                    $admin->find($data["id"]);
                    $isNew = !$admin->getId();
                }

                if ($isNew AND empty($data["password"])) {
                    throw new \Siberian\Exception(__("Please, enter a password."));
                }
                if (empty($data["password"]) AND empty($data["confirm_password"])) {
                    unset($data["password"]);
                    unset($data["confirm_password"]);
                }
                if (!empty($data["password"]) AND $data["password"] != $data["confirm_password"]) {
                    throw new Siberian_Exception(__("Passwords don't match"));
                }

                if ($isNew) {
                    $admin->addData($data);
                }

                if ($dummy->getEmail() === $data["email"] && $dummy->getId() != $admin->getId()) {
                    throw new Exception(__("We are sorry but this email address already exists."));
                }

                $admin->addData($data);

                if (array_key_exists("password", $data)) {
                    $admin->setPassword($data["password"]);
                }

                if (!empty($data["publication_access_type"])) {
                    $admin->setPublicationAccessType($data["publication_access_type"]);
                }

                $admin->save();

                //For SAE we directly link the admin to the app
                $this->getApplication()->addAdmin($admin);

                // Clear admin cache, if acl changed for example.
                /**
                 * @var $cacheOutput Zend_Cache_Frontend_Output
                 */
                $cacheOutput = Zend_Registry::get("cacheOutput");
                $cacheOutput->clean(Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, ["cache_admin"]);

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

    public function setapplicationtoadminAction()
    {

        if ($data = Zend_Json::decode($this->getRequest()->getRawBody())) {

            try {

                if (empty($data["admin_id"]) OR empty($data["app_id"])) {
                    throw new Siberian_Exception(__("#103: An error occurred while saving. Please try again later."));
                }

                $admin = new Admin_Model_Admin();
                $admin->find($data["admin_id"]);
                $application = new Application_Model_Application();
                $application->find($data["app_id"]);

                if (!$admin->getId() OR !$application->getId()) {
                    throw new Siberian_Exception(__("#104: An error occurred while saving. Please try again later."));
                }

                $is_selected = !empty($data["is_allowed_to_add_pages"]);
                $data = ["success" => 1];

                if ($is_selected) {
                    $data["is_allowed_to_add_pages"] = true;
                    $application->addAdmin($admin);
                } else {
                    $data["is_allowed_to_add_pages"] = false;
                    $application->removeAdmin($admin);
                }

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
