<?php

class Application_Webservice_PreviewController extends Core_Controller_Default
{
    public function loginAction() {

        try {

            $data = $this->getRequest()->getPost();
            if (!$this->getRequest()->isPost()) {
                $data = Zend_Json::decode($this->getRequest()->getRawBody());

                $this->getResponse()->setHeader("Access-Control-Allow-Credentials", true, true);
                $this->getResponse()->setHeader("Access-Control-Allow-Methods", "PUT", true);
                $this->getResponse()->setHeader("Access-Control-Allow-Origin", "*", true);
                $this->getResponse()->setHeader("Access-Control-Allow-Headers", "Origin, X-Requested-With, Content-Type, Accept, Pragma, X-Client-Cached-Request", true);

            }

            if (!empty($data)) {

                $canBeLoggedIn = false;

                if (empty($data['email']) OR empty($data['password'])) {
                    throw new Exception(__('Authentication failed. Please check your email and/or your password'));
                }
                $admin = new Admin_Model_Admin();
                $admin->findByEmail($data['email']);

                if ($admin->authenticate($data['password'])) {

                    if (empty($data['version'])) {
                        $applications = $admin->getApplicationsByDesignType("angular");
                    } else {
                        $applications = $admin->getApplicationsByDesignType("ionic");
                    }

                    $data = array('applications' => array());

                    foreach ($applications as $tmp_application) {

                        $application = new Application_Model_Application();
                        $application->find($tmp_application["app_id"]);

                        if(!$application->isActive()) continue;

                        $url = parse_url($application->getUrl());
                        $key = "";
                        if (stripos($url["path"], $application->getKey())) {
                            $url["path"] = str_replace($application->getKey(), "", $url["path"]);
                            $key = $application->getKey();
                        }
                        $icon = '';
                        if ($application->getIcon()) {
                            $icon = $this->getRequest()->getBaseUrl() . $application->getIcon();
                        }

                        $data['applications'][] = array(
                            'id' => $application->getId(),
                            'icon' => $icon,
                            'startup_image' => str_replace("//", "/", $application->getStartupImageUrl()),
                            'startup_image_retina' => str_replace("//", "/", $application->getStartupImageUrl("retina")),
                            'name' => $application->getName(),
                            'scheme' => $url['scheme'],
                            'domain' => $url['host'],
                            'path' => ltrim($url['path'], '/'),
                            'key' => $key,
                            'url' => $application->getUrl(),
                        );

                    }

                } else {
                    throw new Exception(__('Authentication failed. Please check your email and/or your password'));
                }

            }

        } catch(Exception $e) {
            $data = array('error' => __('Authentication failed. Please check your email and/or your password'));
        }

        $this->getResponse()->setBody(Zend_Json::encode($data))->sendResponse();
        die;

    }

}
