<?php

class Application_Backoffice_ListController extends Backoffice_Controller_Default
{

    public function loadAction() {

        $html = array(
            "title" => __("Applications"),
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
                if(!empty($app_ids)) {
                    $filters["app_id IN (?)"] = $app_ids;
                }

            }
        }

        $published_only = filter_var($this->getRequest()->getParam("published_only", false), FILTER_VALIDATE_BOOLEAN);
        if($published_only) {
            $application_table = new Application_Model_Db_Table_Application();
            $applications = $application_table->findAllForGlobalPush();

            if(!empty($applications)) {
                $filters["app_id IN (?)"] = $applications;
            }


        }

        $total = $application->countAll($filters);

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

        $request = $this->getRequest();
        if($range = $request->getHeader("Range")) {
            $parts = explode("-", $range);
            $offset = $parts[0];
            $limit = ($parts[1] - $parts[0]) + 1;
        }

        $admin_id = $this->getRequest()->getParam("admin_id");

        $filters = array();
        if($_filter = $this->getRequest()->getParam("filter", false)) {
            $filters["(a.name LIKE ? OR a.app_id LIKE ? OR a.bundle_id LIKE ? OR a.package_name LIKE ?)"] = "%{$_filter}%";
        }

        $order = $this->getRequest()->getParam("order", false);
        $by = filter_var($this->getRequest()->getParam("by", false), FILTER_VALIDATE_BOOLEAN);
        if($order) {
            $order_by = ($by) ? "ASC" : "DESC";
            $order = sprintf("%s %s", $order, $order_by);
        }

        $application = new Application_Model_Application();
        if(!Siberian_Version::is("sae")) {
            $show_all = filter_var($this->getRequest()->getParam("show_all_applications", false), FILTER_VALIDATE_BOOLEAN);
            $_admin_id = ($show_all) ? null : $admin_id;

            $applications = $application->findAllByAdmin($_admin_id, $filters, $order, $limit, $offset);
            $total_applications = $application->findAllByAdmin($_admin_id, $filters);

            $total = $total_applications->count();
        } else {
            $applications = array(Application_Model_Application::getInstance());
            $total = 1;
        }

        if($range = $request->getHeader("Range")) {
            $start = $parts[0];
            $end = ($total < $parts[1]) ? $total-1 : $parts[1];

            $this->getResponse()->setHeader("Content-Range", sprintf("%s-%s/%s", $start, $end, $total));
            $this->getResponse()->setHeader("Range-Unit", "items");
        }

        $application_admin = new Application_Model_Admin();

        $apps = array();
        foreach($applications as $application) {
            $result = $application_admin->findAll(array(
                "app_id" => $application->getId(),
                "admin_id" => $admin_id
            ));
            $is_allowed = ($result->count() >= 1) ? true : false;

            $apps[] = array(
                "id" => $application->getId(),
                "app_id" => $application->getId(),
                "icon" => $application->getIcon(128),
                "name" => $application->getName(),
                "bundle_id" => $application->getBundleId(),
                "package_name" => $application->getPackageName(),
                "is_allowed_to_add_pages" => $is_allowed,
            );
        }

        $this->_sendHtml($apps);

    }

}
