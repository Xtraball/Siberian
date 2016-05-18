<?php

class Application_Backoffice_ListController extends Backoffice_Controller_Default
{

    public function loadAction() {

        $html = array(
            "title" => $this->_("Applications"),
            "icon" => "fa-mobile",
        );

        $this->_sendHtml($html);

    }

    public function findallAction() {

        $offset = $this->getRequest()->getParam("offset")?$this->getRequest()->getParam("offset"):null;
        $limit = Application_Model_Application::BO_DISPLAYED_PER_PAGE;
        $params = array(
            "offset" => $offset,
            "limit" => $limit
        );

        $application = new Application_Model_Application();
        $applications = $application->findAll(null, null, $params);
        $app_ids = $application->findAllToPublish();
        $data = array(
            "display_per_page"=> $limit,
            "collection" => array()
        );

        foreach($applications as $application) {
            $data["collection"][] = array(
                "id" => $application->getId(),
                "can_be_published" => in_array($application->getId(), $app_ids),
                "name" => mb_convert_encoding($application->getName(), 'UTF-8', 'UTF-8'),
                "bundle_id" => $application->getBundleId(),
                "icon" => $application->getIcon(128)
            );
        }

        $this->_sendHtml($data);

    }

    public function findbyadminAction() {

        $data = array("app_ids" => array(), "is_allowed_to_add_pages" => array());

        $application = new Application_Model_Application();
        if(!Siberian_Version::is("sae")) {
            $applications = $application->findAllByAdmin($this->getRequest()->getParam("admin_id"));
        } else {
            $applications = array(Application_Model_Application::getInstance());
        }

        foreach ($applications as $application) {
            $data["app_ids"][] = $application->getId();
            if ($application->getIsAllowedToAddPages()) {
                $data["is_allowed_to_add_pages"][] = $application->getId();
            }
        }

        $this->_sendHtml($data);

    }

}
