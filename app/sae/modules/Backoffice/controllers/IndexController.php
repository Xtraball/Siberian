<?php

class Backoffice_IndexController extends Backoffice_Controller_Default
{
    public function indexAction() {
        $this->loadPartials();
    }

    public function loadAction() {

        $services = Siberian_Service::getServices();
        $external_services = Siberian_Service::fetchRegisteredServices();
        $extensions = Siberian_Service::getExtensions();
        $server_usage = Siberian_Cache::getDiskUsage();
        $libraries = Siberian_Media::getLibraries();
        $messages = Backoffice_Model_Notification::getMessages();

        $html = array(
            "title" => __("Dashboard"),
            "icon" => "fa-dashboard",
            "services" => $services,
            "external_services" => $external_services,
            "libraries" => $libraries,
            "extensions" => $extensions,
            "server_usage" => $server_usage,
            "unread_messages" => $messages,
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
     * Clearing caches
     */
    public function clearcacheAction() {
        $message = __("Cache cleared");

        if($type = $this->getRequest()->getParam("type")) {
            try {

                switch($type) {
                    case "log":
                        $message = __("Logs cleared.");

                        Siberian_Cache::__clearLog();
                        break;
                    case "cache":
                        Siberian_Cache::__clearCache();
                        break;
                    case "tmp":
                        /** When clearing TPM out we need to clear APK/Source queue links, file doesn't exists anymore */
                        Siberian_Cache::__clearTmp();
                        Application_Model_SourceQueue::clearPaths();
                        Application_Model_ApkQueue::clearPaths();

                        break;
                    case "overview":
                        $message = __("Overview cache cleared.");

                        Siberian_Minify::clearCache();

                        $protocol = isset($_SERVER['HTTPS']) ? 'https://' : 'http://';
                        Siberian_Autoupdater::configure($protocol.$this->getRequest()->getHttpHost());
                        break;
                    case "locks":
                        $message = __("Removing CRON Scheduler lock files.");

                        Siberian_Cache::__clearLocks();
                        break;
                    case "source_locks":
                        $message = __("Removing CRON Scheduler source lock files.");

                        Siberian_Cache::__clearLocks("source_locks");
                        break;
                    case "generator":
                        $message = __("Removing CRON Scheduler generator lock files.");

                        Siberian_Cache::__clearLocks("generator");
                        break;
                    case "app_manifest":
                        $message = __("Rebuilding application manifest files.");

                        $default_cache = Zend_Registry::get("cache");
                        $default_cache->clean(Zend_Cache::CLEANING_MODE_ALL);

                        $protocol = isset($_SERVER['HTTPS']) ? 'https://' : 'http://';
                        Siberian_Autoupdater::configure($protocol.$this->getRequest()->getHttpHost());
                        break;
                    case "cron_error":
                        $message = __("Cleared cron errors.");

                        Cron_Model_Cron::clearErrors();
                        break;
                    case "android_sdk":
                        Cron_Model_Cron::clearErrors();

                        # Enable Android SDK Update
                        $task_model = new Cron_Model_Cron();
                        $task = $task_model->find("androidtools", "command");
                        $task->enable();

                        # Clear cron errors/notification
                        Backoffice_Model_Notification::clear("Android_Sdk_Update", 1);

                        $message = __("Android SDK is marked for update.");

                        break;
                }

                $html = array(
                    "success"       => 1,
                    "message"       => $message,
                    "server_usage"  => Siberian_Cache::getDiskUsage(),
                    "services"      => Siberian_Service::getServices(),
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
