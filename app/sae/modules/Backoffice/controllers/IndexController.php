<?php

class Backoffice_IndexController extends Backoffice_Controller_Default
{
    public function indexAction() {
        $this->loadPartials();
    }

    public function loadAction() {

        $services = Siberian_Service::getServices();
        $extensions = Siberian_Service::getExtensions();
        $server_usage = Siberian_Cache::getDiskUsage();
        $libraries = Siberian_Media::getLibraries();

        $html = array(
            "title" => "Dashboard",
            "icon" => "fa-dashboard",
            "services" => $services,
            "libraries" => $libraries,
            "extensions" => $extensions,
            "server_usage" => $server_usage,
        );

        $this->_sendHtml($html);

    }

    public function findAction() {

        $notification = new Backoffice_Model_Notification();
        $unread_number = $notification->findAll(array("is_read = ?" => 0))->count();
        $unread_message = $unread_number > 1 ? $this->_("%d Unread Messages", $unread_number) : $this->_("%d Unread Message", $unread_number);

        $admin = new Admin_Model_Admin();
        $admins = $admin->getStats();

        $array_admin = array();
        foreach($admins as $admin){
            $array_admin[$admin->getDay()] = $admin->getCount();
        }


        $dateKey = new Siberian_Date();
        $dateEnd = new Siberian_Date();

        $dateKey = $dateKey->setDay(1);
        $dateEnd = $dateEnd->setDay(1);
        $dateEnd->addMonth(1);
        $dateEnd = $dateEnd->subDay(1);

        $stats = array();
        $i = 0;

        while (strcmp($dateKey->toString("yyyy-MM-dd"),$dateEnd->toString("yyyy-MM-dd")) <= 0 ){
            $admin = (isset($array_admin[$dateKey->toString("yyyy-MM-dd")]))?$array_admin[$dateKey->toString("yyyy-MM-dd")]:0;

            $stats[] = array($dateKey->toString("EEE. MMM, dSS"),$admin);

            $dateKey->addDay(1);
        }

        $data = array(
            "stats" => $stats,
            "notif" => array(
                "unread_number" => $unread_number,
                "message" => $unread_message
            ),
            "stats_labels" => array(
                $this->_("New users"),
                $this->_("Total sales"),
                $this->_("Payment received")
            )
        );

        $this->_sendHtml($data);

    }

    /**
     *
     */
    public function clearcacheAction() {
        if($type = $this->getRequest()->getParam("type")) {
            try {

                switch($type) {
                    case "log":
                        Siberian_Cache::__clearLog();
                        break;
                    case "cache":
                        Siberian_Cache::__clearCache();
                        break;
                    case "tmp":
                        Siberian_Cache::__clearTmp();
                        break;
                    case "overview":
                        Siberian_Minify::clearCache();
                        break;
                    case "locks":
                        Siberian_Cache::__clearLocks();
                        break;
                }

                $html = array(
                    "success" => 1,
                    "message" => __("Cache cleared"),
                    "server_usage" => Siberian_Cache::getDiskUsage(),
                );

            }
            catch(Exception $e) {
                $html = array(
                    'error' => 1,
                    'message' => $e->getMessage()
                );
            }

            $this->_sendHtml($html);

        }
    }

}
