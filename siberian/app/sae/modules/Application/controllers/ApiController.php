<?php

class Application_ApiController extends Api_Controller_Default {

    /**
     * @var string
     */
    public $namespace = "application";

    /**
     * @var array
     */
    public $secured_actions = array(
        "create",
        "update",
    );

    public function createAction() {

        if($data = $this->getRequest()->getPost()) {

            try {

                if (isset($data["id"])) {
                    unset($data["id"]);
                }
                if (isset($data["app_id"])) {
                    unset($data["app_id"]);
                }

                if(empty($data["name"])) {
                    throw new Exception(__("The name is required"));
                }

                if(empty($data["user_id"])) {
                    throw new Exception(__("The user_id is required"));
                }

                $admin = new Admin_Model_Admin();
                $admin = $admin->find($data["user_id"]);
                if(!$admin->getId()) {
                    throw new Exception(__("This admin does not exist"));
                }

                $application = new Application_Model_Application();

                $this->__checkKeyAndDomain($data, $application);

                $application
                    ->addData($data)
                    ->addAdmin($admin)
                    ->setData('admin_id', $admin->getId())
                    ->save()
                ;

                $data = array(
                    "success" => 1,
                    "app_id" => $application->getId(),
                    "app_url" => $application->getUrl()
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

    public function updateAction() {

        if($data = $this->getRequest()->getPost()) {

            try {

                if(empty($data["app_id"])) {
                    throw new Exception(__("The app_id is required"));
                }

                $application = new Application_Model_Application();
                $application->find($data["app_id"]);

                if(!$application->getId()) {
                    throw new Exception(__("This application does not exist"));
                }

                $this->__checkKeyAndDomain($data, $application);

                $application->addData($data)
                    ->save()
                ;

                $data = array(
                    "success" => 1,
                    "app_id" => $application->getId(),
                    "app_url" => $application->getUrl()
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

    private function __checkKeyAndDomain($data, $application) {

        if(isset($data["key"]) AND (!$application->getId() OR $application->getKey() != $data["key"])) {

            $module_names = array_map('strtolower', Zend_Controller_Front::getInstance()->getDispatcher()->getSortedModuleDirectories());
            if(in_array($data["key"], $module_names)) {
                throw new Exception(__("Your application key \"%s\" is not valid.", $data["key"]));
            }

            $app_tester = new Application_Model_Application();
            $app_tester->find($data["key"], "key");
            if($app_tester->getId() AND $app_tester->getId() != $application->getId()) {
                throw new Exception(__("The key is already used by another application."));
            }
        }

        if(!empty($data["domain"]) AND $application->getDomain() != $data["domain"]) {

            $data["domain"] = str_replace(array("http://", "https://"), "", $data["domain"]);

            $tmp_url = str_replace(array("http://", "https://"), "", $this->getRequest()->getBaseUrl());
            $tmp_url = current(explode("/", $tmp_url));

            $tmp_domain = explode("/", $data["domain"]);
            $domain = current($tmp_domain);
            if(preg_match('/^(www.)?('.$domain.')/', $tmp_url)) {
                throw new Exception(__("You can't use this domain."));
            } else {
                $domain_folder = next($tmp_domain);
                $module_names = array_map('strtolower', Zend_Controller_Front::getInstance()->getDispatcher()->getSortedModuleDirectories());
                if(in_array($domain_folder, $module_names)) {
                    throw new Exception(__("Your domain key \"%s\" is not valid.", $domain_folder));
                }
            }

            if(!Zend_Uri::check("http://".$data["domain"])) {
                throw new Exception(__("Please enter a valid URL"));
            }

            $app_tester = new Application_Model_Application();
            $app_tester->find($data["domain"], "domain");
            if($app_tester->getId() AND $app_tester->getId() != $application->getId()) {
                throw new Exception("The domain is already used by another application.");
            }

        }



    }

}
