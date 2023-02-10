<?php

use Siberian\File;
use Siberian\Json;

/**
 * Class Application_Model_SourceQueue
 */
class Application_Model_SourceQueue extends Core_Model_Default
{
    /**
     * @var string
     */
    const ARCHIVE_FOLDER = "/var/tmp/jobs/";

    /**
     * @var array
     */
    public $_devices = [
        'ios' => 1,
        'iosnoads' => 1,
        'android' => 2,
    ];

    /**
     * Application_Model_SourceQueue constructor.
     * @param array $data
     */
    public function __construct($data = [])
    {
        parent::__construct($data);
        $this->_db_table = "Application_Model_Db_Table_SourceQueue";
    }

    /**
     * @param $status
     * @return mixed
     */
    public function changeStatus($status)
    {
        switch ($status) {
            case 'building':
                $this->setBuildTime(time());
                $this->setBuildStartTime(time());
                break;
            case 'success':
                $this->setBuildTime(time() - $this->getBuildTime());
                break;
            default:
                $this->setBuildTime(0);
        }

        return $this->setStatus($status)->save();
    }

    /**
     * @param $cron
     * @return mixed
     * @throws Exception
     * @throws Zend_Json_Exception
     * @throws Zend_Layout_Exception
     */
    public function generate($cron)
    {
        $application = new Application_Model_Application();
        $application = $application->find($this->getAppId());

        if (!$application->getId()) {
            throw new Exception(__("#500-02: This application does not exist"));
        }

        $design_code = (in_array($this->getDesignCode(), ["angular", "ionic"])) ? $this->getDesignCode() : "ionic";

        $application->setDesignCode($design_code);
        $device = $application->getDevice($this->_devices[$this->getType()]);
        $device->setApplication($application);
        $device->setExcludeAds(($this->getType() == "iosnoads"));
        $device->setDownloadType("zip");
        $device->setHost($this->getHost());

        // Android isApkService
        if ($this->getIsApkService()) {
            define('IS_APK_SERVICE', true);
            $cron->log('Will send to APK service.');
            $this->setApkStatus('building');
        }

        // FAST
        $result = $device->getResources();

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

        $type = ($this->getType() == "android") ? __("Android Source") : __("iOS Source");
        // Send instant e-mail only if it's not an external service!
        if (!$this->getIsApkService()) {
            if (file_exists($result)) {
                $this->changeStatus("success");
                $this->setPath($result);
                $this->save();

                // Success email!
                $protocol = "https://";
                $url = $protocol . $this->getHost() . "/" . str_replace(Core_Model_Directory::getBasePathTo(""), "", $result);

                $baseEmail = $this->baseEmail(
                    'source_queue_success',
                    $application,
                    __('Build succeed'),
                    null);

                $baseEmail->setContentFor('content_email', 'type', $type);
                $baseEmail->setContentFor('content_email', 'link', $url);
                $baseEmail->setContentFor('content_email', 'application_name', $application->getName());

                $content = $baseEmail->render();

                $subject = sprintf('%s - %s',
                    $application->getName(),
                    __('Build succeed!'));

                $mail = new \Siberian_Mail();
                $mail->setBodyHtml($content);
                $mail->addTo($recipients);
                $mail->setSubject($subject);
                $mail->send();

            } else {
                $this->changeStatus("failed");
                $this->save();

                $baseEmail = $this->baseEmail(
                    'source_queue_failed',
                    $application,
                    __('Build failed'),
                    null);

                $baseEmail->setContentFor('content_email', 'type', $type);
                $baseEmail->setContentFor('content_email', 'application_name', $application->getName());

                $content = $baseEmail->render();

                $subject = sprintf('%s - %s',
                    $application->getName(),
                    __('Build failed!'));

                $mail = new \Siberian_Mail();
                $mail->setBodyHtml($content);
                $mail->addTo($recipients);
                $mail->setSubject($subject);
                $mail->send();
            }
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
            ->setContentFor('base', 'email_title', __('Sources Generation') . ' - ' . $title)

            ->setContentFor('content_email', 'app_name', $application->getName())
            ->setContentFor('content_email', 'message', $message)

            ->setContentFor('footer', 'show_legals', false)
        ;

        return $layout;
    }

    /**
     * Fetch if some apps are building.
     *
     * @param $application_id
     * @return array
     */
    public static function getStatus($application_id)
    {
        $table = new self();
        $results = $table->findAll([
            "app_id" => $application_id,
            "status IN (?)" => ["queued", "building"],
        ]);

        $data = [
            "ios" => false,
            "iosnoads" => false,
            "android" => false,
        ];

        foreach ($results as $result) {
            $type = $result->getType();
            if (array_key_exists($type, $data)) {
                # Set is building
                $data[$type] = true;
            }
        }

        return $data;
    }

    /**
     * Fetch if some apps are done.
     *
     * @param $applicationId
     * @return array
     */
    public static function getPackages($applicationId)
    {
        $table = new self();
        $results = $table->findAll(
            [
                'app_id' => $applicationId,
                'status IN (?)' => ['success'],
            ],
            [
                'updated_at DESC'
            ]
        );

        $basePath = Core_Model_Directory::getBasePathTo('');
        $data = [];

        foreach ($results as $result) {
            $type = $result->getType();
            if (!array_key_exists($type, $data)) {
                // Test if the path still exists!
                if (is_file($result->getData('path'))) {
                    // Fetch the path
                    $data[$type] = [
                        'path' => str_replace($basePath, '/', $result->getData('path')),
                        'date' => datetime_to_format($result->getUpdatedAt())
                    ];
                } else {
                    $result
                        ->setData('path', '')
                        ->save();
                }
            }
        }

        return $data;
    }

    /**
     * @param $applicationId
     * @return array
     */
    public static function getApkServiceStatus($applicationId)
    {
        $table = new self();
        $results = $table->findAll(
            [
                'app_id' => $applicationId,
                'is_apk_service' => 1,
            ],
            [
                'created_at DESC'
            ]
        );

        $found = [
            'host' => '-',
            'status' => '-',
            'message' => false,
            'date' => '-',
            'path' => '',
            'aab_path' => '',
        ];
        foreach ($results as $result) {
            $found = [
                'host' => $result->getHost(),
                'status' => $result->getApkStatus(),
                'message' => $result->getApkMessage(),
                'date' => datetime_to_format($result->getUpdatedAt()),
                'path' => str_replace(path(''), '', $result->getApkPath()),
                'aab_path' => str_replace(path(''), '', $result->getAabPath()),
            ];
            break;
        }

        return $found;
    }

    /**
     * @param $applicationId
     * @return Application_Model_SourceQueue|false
     */
    public static function getApkServiceQueue($applicationId)
    {
        $table = new self();
        $results = $table->findAll(
            [
                'app_id' => $applicationId,
                'is_apk_service' => 1,
            ],
            [
                'created_at DESC'
            ]
        );

        $found = false;
        foreach ($results as $result) {
            return $result;
        }

        return $found;
    }

    /**
     * @return Application_Model_SourceQueue[]
     */
    public static function getQueue()
    {
        $table = new self();
        $results = $table->findAll(
            ["status IN (?)" => ["queued"]],
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
                "(build_start_time + 3600) < ?" => $current_time
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
        $db->query("UPDATE source_queue SET path = '' WHERE status != 'building';");
    }

}
