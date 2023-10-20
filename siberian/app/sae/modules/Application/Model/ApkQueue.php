<?php

/**
 * Class Application_Model_ApkQueue
 */
class Application_Model_ApkQueue extends Core_Model_Default
{
    /**
     * Application_Model_ApkQueue constructor.
     * @param array $data
     * @throws Zend_Exception
     */
    public function __construct($data = [])
    {
        parent::__construct($data);
        $this->_db_table = "Application_Model_Db_Table_ApkQueue";
    }

    /**
     * @param $status
     * @return mixed
     */
    public function changeStatus($status)
    {
        switch ($status) {
            case "building":
                $this->setBuildTime(time());
                $this->setBuildStartTime(time());
                break;
            case "success":
                $this->setBuildTime(time() - $this->getBuildTime());
                break;
            default:
                $this->setBuildTime(0);
        }

        return $this->setStatus($status)->save();
    }

    /**
     * Generate APK in cron queue
     *
     * @throws \Siberian\Exception
     */
    public function generate()
    {
        $application = new Application_Model_Application();
        $application = $application->find($this->getAppId());

        if (!$application->getId()) {
            throw new \Siberian\Exception(__("#500-01: This application does not exist"));
        }

        $application->setDesignCode("ionic");
        $device = $application->getDevice(2);
        $device->setApplication($application);
        $device->setDownloadType("apk");
        $device->setHost($this->getHost());

        // FAST
        $result = $device->getResources();

        /** Saving log */
        $this->setLog(implode_polyfill("\n", $result["log"]));

        $recipients = [];
        switch ($this->getUserType()) {
            case "backoffice":
                $backoffice = new Backoffice_Model_User();
                $backoffice_user = $backoffice->find($this->getUserId());
                if ($backoffice_user->getId()) {
                    $recipients[] = $backoffice_user->getEmail();
                }
                break;
            case "admin":
                $admin = new Admin_Model_Admin();
                $admin_user = $admin->find($this->getUserId());
                if ($admin_user->getId()) {
                    $recipients[] = $admin_user->getEmail();
                }
                break;
        }

        if ($result && ($result["success"] == true)) {
            $this->changeStatus("success");
            $this->setPath($result["path"]);
            $this->save();

            /** Success email */
            $protocol = "https://";
            $url = $protocol . $this->getHost() . "/" . str_replace(Core_Model_Directory::getBasePathTo(""), "", $result["path"]);

            $baseEmail = $this->baseEmail(
                'apk_queue_success',
                $application,
                __('Build succeed'),
                null);

            $baseEmail->setContentFor('content_email', 'link', $url);
            $baseEmail->setContentFor('content_email', 'application_name', $application->getName());

            $content = $baseEmail->render();

            $subject = sprintf('%s - %s',
                $application->getName(),
                __('APK build succeed!'));

            $mail = new \Siberian_Mail();
            $mail->setBodyHtml($content);
            $mail->addTo($recipients);
            $mail->setSubject($subject);
            $mail->send();

        } else {
            $this->changeStatus("failed");
            $this->save();

            $baseEmail = $this->baseEmail(
                'apk_queue_failed',
                $application,
                __('Build failed'),
                null);

            $baseEmail->setContentFor('content_email', 'application_name', $application->getName());

            $content = $baseEmail->render();

            $subject = sprintf('%s - %s',
                $application->getName(),
                __('APK build failed!'));

            $mail = new \Siberian_Mail();
            $mail->setBodyHtml($content);
            $mail->addTo($recipients);
            $mail->setSubject($subject);
            $mail->send();

        }

        return $result;
    }

    /**
     * @param $nodeName
     * @param $application
     * @param $title
     * @param $message
     * @return Siberian_Layout|Siberian_Layout_Email
     * @throws Zend_Layout_Exception
     */
    public function baseEmail($nodeName,
                              $application,
                              $title,
                              $message)
    {
        $layout = new Siberian\Layout();
        $layout = $layout->loadEmail('queue', $nodeName);
        $layout
            ->setContentFor('base', 'email_title', __('APK Generation') . ' - ' . $title)
            ->setContentFor('content_email', 'app_name', $application->getName())
            ->setContentFor('content_email', 'message', $message)
            ->setContentFor('footer', 'show_legals', false);

        return $layout;
    }

    /**
     * Fetch if some apps are done.
     *
     * @param $application_id
     * @return array
     */
    public static function getPackages($application_id)
    {
        $results = (new self())->findAll(
            [
                'app_id' => $application_id,
                'status IN (?)' => ['success'],
            ],
            [
                'updated_at DESC'
            ]
        );

        foreach ($results as $result) {
            // Test if the path still exists!
            if (is_file($result->getData('path'))) {
                // Fetch the path
                return [
                    'path' => str_replace(Core_Model_Directory::getBasePathTo(''), '/', $result->getData('path')),
                    'date' => datetime_to_format($result->getUpdatedAt())
                ];
            } else {
                $result
                    ->setData('path', '')
                    ->save();
            }
        }

        return [
            'path' => false,
            'date' => false,
        ];
    }

    /**
     * Fetch if the APK is queued
     *
     * @param $application_id
     * @return bool
     */
    public static function getStatus($application_id)
    {
        $table = new self();
        $results = $table->findAll([
            "app_id" => $application_id,
            "status IN (?)" => ["queued", "building"],
        ]);

        return ($results->count() > 0);
    }

    /**
     * @return Application_Model_ApkQueue[]
     */
    public static function getQueue()
    {
        $table = new self();

        $results = $table->findAll(
            ["status" => "queued"],
            ["created_at ASC"]
        );

        return $results;
    }

    /**
     * Fetch builds started from more than 1 hour
     *
     * @param $current_time
     */
    public static function getStuck($current_time)
    {
        $table = new self();

        $results = $table->findAll(
            [
                "status = ?" => "building",
                "(build_start_time + 3600) < ?" => $current_time,
            ],
            ["created_at ASC"]
        );

        return $results;
    }

    /**
     * Clear old pathts when clearing tmp cache in backoffice
     */
    public static function clearPaths()
    {
        $db = Zend_Db_Table::getDefaultAdapter();
        $db->query("UPDATE apk_queue SET path = '' WHERE status != 'building';");
    }

}
