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
        $application = new Application_Model_Application();

        $offset = $this->getRequest()->getParam("offset", null);
        $limit = Application_Model_Application::BO_DISPLAYED_PER_PAGE;

        $request = $this->getRequest();
        if($range = $request->getHeader("Range")) {
            $parts = explode("-", $range);
            $offset = $parts[0];
            $limit = ($parts[1] - $parts[0]) + 1;
        }


        $params = array(
            "offset" => $offset,
            "limit" => $limit
        );

        $filters = array();
        if($_filter = $this->getRequest()->getParam("filter", false)) {
            $filters["(name LIKE ? OR app_id LIKE ? OR bundle_id LIKE ? OR package_name LIKE ?)"] = "%{$_filter}%";
        }

        $order = $this->getRequest()->getParam("order", false);
        $by = filter_var($this->getRequest()->getParam("by", false), FILTER_VALIDATE_BOOLEAN);
        if($order) {
            $order_by = ($by) ? "ASC" : "DESC";
            $order = sprintf("%s %s", $order, $order_by);
        }

        $to_publish = filter_var($this->getRequest()->getParam("toPublish", false), FILTER_VALIDATE_BOOLEAN);
        if($to_publish) {
            $app_ids = $application->findAllToPublish();

            if(empty($app_ids)) {
                $filters["app_id = ?"] = -1;
            } else {
                $filters["app_id IN (?)"] = $app_ids;
            }
        }

        $all_applications = $application->findAll($filters, $order);
        $total = $all_applications->count();

        if($range = $request->getHeader("Range")) {
            $start = $parts[0];
            $end = ($total <= $parts[1]) ? $total : $parts[1];

            $this->getResponse()->setHeader("Content-Range", sprintf("%s-%s/%s", $start, $end, $total));
            $this->getResponse()->setHeader("Range-Unit", "items");
        }

        $applications = $application->findAll($filters, $order, $params);

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
                "package_name" => $application->getPackageName(),
                "icon" => $application->getIcon(128)
            );
        }

        $this->_sendHtml($data["collection"]);

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
