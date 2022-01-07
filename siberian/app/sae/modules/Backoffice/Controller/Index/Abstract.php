<?php

use Backoffice\Model\Tools;

/**
 * Class Backoffice_Controller_Index_Abstract
 */
class Backoffice_Controller_Index_Abstract extends Backoffice_Controller_Default
{
    /**
     *
     */
    public function indexAction()
    {
        $this->loadPartials();
    }

    /**
     * @throws Exception
     * @throws Zend_Exception
     */
    public function loadAction()
    {
        $services = Siberian_Service::getServices();
        $extensions = Siberian_Service::getExtensions();
        $server_usage = Siberian_Cache::getDiskUsage();
        $libraries = Siberian_Media::getLibraries();
        $system_diagnostic = Siberian_Service::getSystemDiagnostic();

        $html = [
            'title' => __('Dashboard'),
            'icon' => 'fa-dashboard',
            'services' => $services,
            'system_diagnostic' => $system_diagnostic,
            'libraries' => $libraries,
            'extensions' => $extensions,
            'server_usage' => $server_usage,
        ];

        $this->_sendJson($html);
    }

    /**
     * @throws Exception
     * @throws Zend_Exception
     */
    public function loadServicesAction()
    {
        $external_services = Siberian_Service::fetchRegisteredServices();

        $payload = [
            "external_services" => $external_services,
        ];

        $this->_sendJson($payload);
    }

    /**
     * @throws Exception
     * @throws Zend_Exception
     */
    public function loadMessagesAction()
    {
        $messages = Backoffice_Model_Notification::getMessages();

        $payload = [
            "unread_messages" => $messages,
        ];

        $this->_sendJson($payload);
    }

    /**
     * @throws Zend_Date_Exception
     * @throws Zend_Exception
     */
    public function findAction()
    {
        $notification = new Backoffice_Model_Notification();
        $unread_number = $notification->findAll(["is_read = ?" => 0])->count();
        $unread_message = $unread_number > 1 ?
            __("%d Unread Messages", $unread_number) :
            __("%d Unread Message", $unread_number);

        $admin = new Admin_Model_Admin();
        $admins = $admin->getStats();

        $array_admin = [];
        foreach ($admins as $admin) {
            $array_admin[$admin->getDay()] = $admin->getCount();
        }

        $dateKey = (new Siberian_Date())->setDay(1);
        $dateEnd = (new Siberian_Date())->setDay(1);
        $dateEnd->addMonth(1);
        $dateEnd = $dateEnd->subDay(1);

        $stats = [];
        while (strcmp($dateKey->toString("yyyy-MM-dd"), $dateEnd->toString("yyyy-MM-dd")) <= 0) {
            $admin = (isset($array_admin[$dateKey->toString("yyyy-MM-dd")])) ?
                $array_admin[$dateKey->toString("yyyy-MM-dd")] : 0;

            $stats[] = [$dateKey->toString("EEE. MMM, dSS"), $admin];

            $dateKey->addDay(1);
        }

        $payload = [
            "stats" => $stats,
            "notif" => [
                "unread_number" => $unread_number,
                "message" => $unread_message
            ],
            "stats_labels" => [
                __("New users"),
                __("Total sales"),
                __("Payment received")
            ]
        ];

        $this->_sendJson($payload);
    }

    /**
     * Clearing caches
     */
    public function clearcacheAction()
    {
        $message = __("Cache cleared");

        if ($type = $this->getRequest()->getParam("type")) {
            try {

                switch ($type) {
                    case 'log':
                        $message = __("Logs cleared.");

                        Siberian_Cache::__clearLog();
                        break;
                    case 'cache':
                        Siberian_Cache::__clearCache();
                        Siberian_Cache_Design::clearCache();
                        break;
                    case 'cache_images':
                        Siberian_Cache::__clearCacheImages();
                        break;
                    case 'tmp':
                        /** When clearing TPM out we need to clear APK/Source queue links, file doesn't exists anymore */
                        Siberian_Cache::__clearTmp();
                        Application_Model_SourceQueue::clearPaths();
                        Application_Model_ApkQueue::clearPaths();

                        break;
                    case 'locks':
                        $message = __("Removing CRON Scheduler lock files.");

                        Siberian_Cache::__clearLocks();
                        break;
                    case 'source_locks':
                        $message = __("Removing CRON Scheduler source lock files.");

                        Siberian_Cache::__clearLocks("source_locks");
                        break;
                    case 'generator':
                        $message = __("Removing CRON Scheduler generator lock files.");

                        Siberian_Cache::__clearLocks("generator");
                        break;

                    case 'app_manifest':
                        // Moved to cron

                        $message = p__('backoffice','App manifest rebuild have been scheduled to be processed imminently!');

                        Tools::scheduleTask(Tools::REBUILD_MANIFEST);

                        break;
                    case 'cron_error':
                        $message = __("Cleared cron errors.");

                        (new Cron_Model_Cron())
                            ->clearErrors();
                        break;
                    case 'android_sdk':
                        (new Cron_Model_Cron())
                            ->clearErrors();

                        // Enable Android SDK Update!
                        $task = (new Cron_Model_Cron())
                            ->find("androidtools", "command");
                        $task->enable();

                        # Clear cron errors/notification
                        Backoffice_Model_Notification::clear("Android_Sdk_Update", 1);

                        $message = __("Android SDK is marked for update.");

                        break;
                }

                $payload = [
                    "success" => true,
                    "message" => $message,
                    "server_usage" => Siberian_Cache::getDiskUsage(),
                    "services" => Siberian_Service::getServices(),
                ];
            } catch (Exception $e) {
                $payload = [
                    'error' => true,
                    'message' => $e->getMessage()
                ];
            }

            $this->_sendJson($payload);
        }
    }

}
