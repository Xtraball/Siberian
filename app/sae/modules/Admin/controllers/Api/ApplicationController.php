<?php

class Admin_Api_ApplicationController extends Api_Controller_Default {

    public function listAction() {

        if($data = $this->getRequest()->getPost()) {

            try {

                if(empty($data["admin_id"])) {
                    throw new Exception($this->_("The admin_id parameter is required"));
                }

                $admin = new Admin_Model_Admin();
                $admin->find($data["admin_id"]);

                if(!$admin->getId()) {
                    throw new Exception($this->_("This admin does not exist"));
                }

                $applications = array();
                foreach ($admin->getApplications() as $application) {
                    if(!$application->isActive()) continue;

                    $icon = null;
                    if($application->getIcon()) {
                        $icon = $this->getRequest()->getBaseUrl().$application->getIcon();
                    }

                    $application->addData(array(
                        "url" => $application->getUrl(),
                        "icon" => $icon,
                        'startup_image_url' => str_replace("//", "/", $this->getRequest()->getBaseUrl().$application->getStartupImageUrl()),
                        'retina_startup_image_url' => str_replace("//", "/", $this->getRequest()->getBaseUrl().$application->getStartupImageUrl("retina"))
                    ));

                    $applications[] = $application->getData();

                }

                $data = array(
                    "success" => 1,
                    "applications" => $applications
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
