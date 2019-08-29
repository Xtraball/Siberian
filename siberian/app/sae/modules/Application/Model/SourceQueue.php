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

                // If autopublish, send to job, but no e-mail, jenkins will do it!
                if ($this->getIsAutopublish()) {
                    $this->sendJobToAutoPublishServer($application, $result);
                } else {
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
                }
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
     * @param $application
     * @param $sourcePath
     * @throws Zend_Uri_Exception
     * @throws \Siberian\Exception
     * @throws \rock\sanitize\SanitizeException
     */
    protected function sendJobToAutoPublishServer($application, $sourcePath)
    {
        $appId = $application->getId();

        // iOS license key!
        $licenseKey = __get('ios_autobuild_key');

        // Application infos!
        $app = (new Application_Model_Application())
            ->find($appId);

        // Backoffice user!
        $user = (new Backoffice_Model_User())
            ->find($this->getUserId());
        $usermail = $user->getEmail();

        // iOS setting info
        $appIosAutopublish = (new Application_Model_IosAutopublish())
            ->find($appId, 'app_id');

        $languages = Siberian_Json::decode($appIosAutopublish->getLanguages());

        if (!array_key_exists('error', $languages)) {
            if (count($languages) === 0) {
                throw new \Siberian\Exception('There is no language selected');
            }
        } else {
            throw new \Siberian\Exception('Cannot unserialize language data');
        }

        //we keep using ISO-639 for siberian storage but we have to translate ISO code to faslane languages name
        $fastlane_languages = [];
        foreach ($languages as $codeIos => $activated) {
            $fast_label = Application_Model_Languages::getLabelFromCodeIso($codeIos);
            $fastlane_languages[$fast_label] = $activated;
        }

        $data = [
            'name' => $app->getName(),
            'bundle_id' => $app->getBundleId(),
            'want_to_autopublish' => $appIosAutopublish->getWantToAutopublish(),
            'credentials' => $appIosAutopublish->getCypheredCredentials(),
            'team_id' => $appIosAutopublish->getTeamId(),
            'team_name' => $appIosAutopublish->getTeamName(),
            'itc_provider' => $appIosAutopublish->getItcProvider(),
            'account_type' => $appIosAutopublish->getAccountType(),
            'original_login' => $appIosAutopublish->getItunesOriginalLogin(),
            'has_bg_locate' => $appIosAutopublish->getHasBgLocate(),
            'has_audio' => $appIosAutopublish->getHasAudio(),
            'languages' => $fastlane_languages,
            'host' => $this->getHost(),
            'license_key' => $licenseKey,
            'token' => $appIosAutopublish->getToken(),
            'email' => $usermail,
        ];

        $jobCode = time() . '-' . $appIosAutopublish->getToken();
        $jobFolder = Core_Model_Directory::getBasePathTo(self::ARCHIVE_FOLDER . $jobCode);

        if (!mkdir($jobFolder, 0777, true)) {
            throw new \Siberian\Exception('Cannot create folder ' . $jobFolder);
        }

        if (!copy($sourcePath, $jobFolder . '/sources.zip')) {
            throw new \Siberian\Exception('Cannot copy sources to job folder');
        }

        $configJobFilePath = $jobFolder . '/config.json';
        $json = Json::encode($data, JSON_PRETTY_PRINT);

        if (!array_key_exists("error", Json::decode($json))) {
            File::putContents($configJobFilePath, $json);
        } else {
            throw new \Siberian\Exception("Cannot create json config job file");
        }

        $tgzJobFilePath = $jobFolder . '.tgz';

        exec("tar zcf $tgzJobFilePath -C $jobFolder sources.zip config.json", $output, $return_val);

        if ($return_val !== 0) {
            throw new \Siberian\Exception('Cannot create zip job file');
        }

        $jobUrlEncoded = base64_encode('http://' . $this->getHost() . '/var/tmp/jobs/' . $jobCode . '.tgz');

        Siberian_Request::get(
            "https://jenkins-prod02.xtraball.com/job/ios-autopublish/buildWithParameters",
            [
                'token' => 'O0cRwnWPjcfMmXc89SQ3RbVRPGXLQF6a',
                'JOB_NAME' => slugify($app->getName()),
                'SIBERIAN_JOB_URL' => $jobUrlEncoded,
                'VERSION' => "3",
            ]);

        if (!in_array(Siberian_Request::$statusCode, [100, 200, 201])) {
            throw new \Siberian\Exception(__('Cannot send build to service %s.', Siberian_Request::$statusCode));
        }
    }

    /**
     * @param $application
     * @throws Zend_Uri_Exception
     * @throws \Siberian\Exception
     * @throws \rock\sanitize\SanitizeException
     */
    protected function sendPemToAutoPublishServer($application)
    {
        $appId = $application->getId();

        // iOS license key!
        $licenseKey = __get('ios_autobuild_key');

        // Application infos!
        $app = (new Application_Model_Application())
            ->find($appId);

        // Backoffice user!
        $user = (new Backoffice_Model_User())
            ->find($this->getUserId());
        $usermail = $user->getEmail();

        // iOS setting info
        $appIosAutopublish = (new Application_Model_IosAutopublish())
            ->find($appId, 'app_id');

        $languages = Siberian_Json::decode($appIosAutopublish->getLanguages());

        if (!array_key_exists('error', $languages)) {
            if (count($languages) === 0) {
                throw new \Siberian\Exception('There is no language selected');
            }
        } else {
            throw new \Siberian\Exception('Cannot unserialize language data');
        }

        //we keep using ISO-639 for siberian storage but we have to translate ISO code to faslane languages name
        $fastlane_languages = [];
        foreach ($languages as $codeIos => $activated) {
            $fast_label = Application_Model_Languages::getLabelFromCodeIso($codeIos);
            $fastlane_languages[$fast_label] = $activated;
        }

        $data = [
            'name' => $app->getName(),
            'bundle_id' => $app->getBundleId(),
            'want_to_autopublish' => $appIosAutopublish->getWantToAutopublish(),
            'credentials' => $appIosAutopublish->getCypheredCredentials(),
            'team_id' => $appIosAutopublish->getTeamId(),
            'team_name' => $appIosAutopublish->getTeamName(),
            'itc_provider' => $appIosAutopublish->getItcProvider(),
            'has_bg_locate' => $appIosAutopublish->getHasBgLocate(),
            'has_audio' => $appIosAutopublish->getHasAudio(),
            'languages' => $fastlane_languages,
            'host' => $this->getHost(),
            'license_key' => $licenseKey,
            'token' => $appIosAutopublish->getToken(),
            'email' => $usermail,
            'refresh_pem' => true,
        ];

        $jobCode = time() . '-' . $appIosAutopublish->getToken();
        $jobFolder = Core_Model_Directory::getBasePathTo(self::ARCHIVE_FOLDER . $jobCode);

        if (!mkdir($jobFolder, 0777, true)) {
            throw new \Siberian\Exception('Cannot create folder ' . $jobFolder);
        }

        $fakeSources = Core_Model_Directory::getBasePathTo('/var/apps/ionic/refresh_pem.zip');
        if (!copy($fakeSources, $jobFolder . '/sources.zip')) {
            throw new \Siberian\Exception('Cannot copy sources to job folder');
        }

        $configJobFilePath = $jobFolder . '/config.json';
        $json = Siberian_Json::encode($data, JSON_PRETTY_PRINT);

        if (!array_key_exists('error', $json)) {
            File::putContents($configJobFilePath, $json);
        } else {
            throw new \Siberian\Exception('Cannot create json config job file');
        }

        $tgzJobFilePath = $jobFolder . '.tgz';

        exec("tar zcf $tgzJobFilePath -C $jobFolder sources.zip config.json", $output, $return_val);

        if ($return_val !== 0) {
            throw new \Siberian\Exception('Cannot create zip job file');
        }

        $jobUrlEncoded = base64_encode('http://' . $this->getHost() . '/var/tmp/jobs/' . $jobCode . '.tgz');

        Siberian_Request::get(
            "https://jenkins-prod02.xtraball.com/job/generate-pem/buildWithParameters",
            [
                'token' => '6EJQwGkCLzTTvSWUfY19a3QshNvk8RXK',
                'SIBERIAN_JOB_URL' => $jobUrlEncoded,
            ]);

        if (!in_array(Siberian_Request::$statusCode, [100, 200, 201])) {
            throw new \Siberian\Exception(__('Cannot send build to service %s.', Siberian_Request::$statusCode));
        }
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
        ];
        foreach ($results as $result) {
            $found = [
                'host' => $result->getHost(),
                'status' => $result->getApkStatus(),
                'message' => $result->getApkMessage(),
                'date' => datetime_to_format($result->getUpdatedAt()),
                'path' => str_replace(Core_Model_Directory::getBasePathTo(''), '', $result->getApkPath()),
            ];
            break;
        }

        return $found;
    }

    /**
     * @param $applicationId
     * @return bool
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
     * @return Push_Model_Message[]
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
