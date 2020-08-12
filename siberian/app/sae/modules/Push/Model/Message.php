<?php

/**
 * Class Push_Model_Message
 *
 * @method Push_Model_Db_Table_Message getTable()
 * @method $this setMessageGlobalId(integer $globalId)
 * @method $this setTargetDevices(array $targetDevices)
 * @method $this setAppId(integer $appId)
 * @method $this setSendToAll(boolean $sendToAll)
 * @method $this setSendUntil(string $sendUntil)
 * @method $this setActionValue(string $actionValue)
 * @method $this setCover(string $cover)
 * @method $this setCustomImage(string $customImage)
 * @method $this setIsStandalone(boolean $isStandalone)
 * @method $this setToken(string $token)
 * @method $this setForceAppRoute(boolean $force)
 * @method $this setBase64(boolean $base64)
 */
class Push_Model_Message extends Core_Model_Default
{
    /**
     * @var boolean
     */
    protected $_is_cacheable = true;

    /**
     * @var integer
     */
    const DISPLAYED_PER_PAGE = 10;

    /**
     * @var integer
     */
    const TYPE_PUSH = 1;

    /**
     * @var integer
     */
    const TYPE_INAPP = 2;

    /**
     * @var Siberian_Log
     */
    public $logger;

    /**
     * @var array
     */
    protected $_types = [
        'ios' => 'Push_Model_Ios_Message',
        'android' => 'Push_Model_Android_Message'
    ];

    /**
     * @var
     */
    protected $_instances;

    /**
     * @var
     */
    protected $_messageType;

    /**
     * Push_Model_Message constructor.
     * @param array $datas
     * @throws Zend_Exception
     */
    public function __construct($datas = [])
    {
        parent::__construct($datas);
        $this->_db_table = 'Push_Model_Db_Table_Message';

        $this->logger = Zend_Registry::get("logger");

        $this->_initMessageType();
    }

    /**
     * @return array
     */
    public function getInappStates($value_id)
    {

        $in_app_states = [
            [
                "state" => "push-list",
                "offline" => true,
                "params" => [
                    "value_id" => $value_id,
                ],
            ],
        ];

        return $in_app_states;
    }

    /**
     * @param $option_value
     * @return array|string[]
     * @throws Zend_Exception
     */
    public function getFeaturePaths($option_value)
    {
        if (!$this->isCacheable()) return [];

        $value_id = $option_value->getId();
        $cache_id = "feature_paths_valueid_{$value_id}";
        if (!$paths = $this->cache->load($cache_id)) {

            $paths = [];

            $push_count = new Push_Model_Message();
            $push_count = $push_count->countAll("app_id", $option_value->getAppId());

            $options = [
                "value_id" => $option_value->getId(),
                "device_uid" => "%DEVICE_UID%"
            ];

            $paths[] = $option_value->getPath("push/mobile_list/findall", $options, false);
            for ($i = 0; $i <= ceil($push_count / self::DISPLAYED_PER_PAGE); $i++) {
                $options["offset"] = $i * self::DISPLAYED_PER_PAGE;
                $paths[] = $option_value->getPath("push/mobile_list/findall", $options, false);
            }

            $paths[] = __path("push/mobile/count", [
                "device_uid" => "%DEVICE_UID%"
            ]);

            $paths[] = __path("push/mobile/inapp", [
                "device_uid" => "%DEVICE_UID%"
            ]);

            $this->cache->save($paths, $cache_id, [
                "feature_paths",
                "feature_paths_valueid_{$value_id}"
            ]);
        }

        return $paths;
    }

    /**
     * @return $this|void
     */
    public function delete()
    {
        $message_id = $this->getId();

        parent::delete();

        $this->getTable()->deleteLog($message_id);
    }

    /**
     * @param $option_value
     * @return $this|void
     */
    public function deleteFeature($option_value)
    {
        $app = $this->getApplication();

        $this->setMessageTypeByOptionValue($option_value);
        $this->getTable()->deleteAllLogs($app->getId(), $this->getMessageType());
        $this->getTable()->deleteAllMessages($app->getId(), $this->getMessageType());

    }

    /**
     * @param null $type
     * @return null
     */
    public function getInstance($type = null)
    {
        if (!empty($this->_instances[$type])) return $this->_instances[$type];
        else return null;
    }

    /**
     * @return mixed
     */
    public function getInstances()
    {
        return $this->_instances;
    }

    /**
     * @return mixed
     */
    public function getMessageType()
    {
        return $this->_messageType;
    }

    /**
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function getMessages()
    {
        return $this->getTable()->getMessages($this->_messageType);
    }

    /**
     * @return bool|null|string|string[]
     */
    public function getTitle()
    {
        return !!$this->getData("base64") ? base64_decode($this->getData("title")) :
            mb_convert_encoding($this->getData('title'), 'UTF-8', 'UTF-8');
    }

    /**
     * @return bool|null|string|string[]
     */
    public function getText()
    {
        return !!$this->getData("base64") ? base64_decode($this->getData("text")) : mb_convert_encoding($this->getData('text'), 'UTF-8', 'UTF-8');
    }

    /**
     * @param $title
     * @return $this
     */
    public function setTitle($title)
    {
        $text = $this->getText();
        return $this->addData([
            "base64" => 1,
            "title" => base64_encode($title),
            "text" => base64_encode($text)
        ]);
    }

    /**
     * @param $text
     * @return $this
     */
    public function setText($text)
    {
        $title = $this->getTitle();
        return $this->addData([
            "base64" => 1,
            "title" => base64_encode($title),
            "text" => base64_encode($text)
        ]);
    }

    /**
     * @param $device_uid
     * @param null $message_id
     * @return $this
     * @throws Zend_Db_Adapter_Exception
     */
    public function markAsRead($device_uid, $message_id = null)
    {
        return $this->getTable()->markAsRead($device_uid, $message_id);
    }

    /**
     * @param $device_id
     * @param $message_id
     * @return $this
     * @throws Zend_Db_Adapter_Exception
     */
    public function markAsDisplayed($device_id, $message_id)
    {
        return $this->getTable()->markAsDisplayed($device_id, $message_id);
    }

    /**
     * @param $appId
     * @param $typeId
     * @param int $limit
     * @return mixed
     */
    public function findAllForFeature($appId, $typeId, $limit = 100)
    {
        return $this->getTable()->findAllForFeature($appId, $typeId, $limit);
    }

    /**
     * @param $device_id
     * @param $app_id
     * @param int $offset
     * @return mixed
     */
    public function findByDeviceId($device_id, $app_id, $offset = 0)
    {
        $allowed_categories = null;
        if ($this->_messageType == self::TYPE_INAPP) {

            $subscription = new Topic_Model_Subscription();
            $allowed_categories = $subscription->findAllowedCategories($device_id);

        }

        return $this->getTable()->findByDeviceId($device_id, $this->_messageType, $app_id, $offset, $allowed_categories);
    }

    /**
     * @param $device_id
     * @return mixed
     */
    public function countByDeviceId($device_id)
    {
        return $this->getTable()->countByDeviceId($device_id, $this->_messageType);
    }

    /**
     * @param $device_id
     * @param $app_id
     * @return $this
     */
    public function findLastPushMessage($device_id, $app_id)
    {
        $row = $this->getTable()->findLastPushMessage($device_id, $app_id);
        $this->_prepareDatas($row);
        return $this;
    }

    /**
     * @param $app_id
     * @param $device_id
     * @return $this
     */
    public function findLastInAppMessage($app_id, $device_id)
    {
        $subscription = new Topic_Model_Subscription();
        $allowed_categories = $subscription->findAllowedCategories($device_id);

        $row = $this->getTable()->findLastInAppMessage($app_id, $device_id, $allowed_categories);
        $this->_prepareDatas($row);
        return $this;
    }

    /**
     * In fact it's "MARK PUSH AS READ"
     *
     * @param $app_id
     * @param $device_id
     * @param $device_type
     * @return mixed
     */
    public function markInAppAsRead($app_id, $device_id, $device_type)
    {
        return $this->getTable()->markInAppAsRead($app_id, $device_id, $device_type);
    }

    /**
     * InApp Message is read!
     *
     * @param $messageId
     * @param $deviceUid
     */
    public function markRealInAppAsRead($messageId, $deviceUid)
    {
        $this->getTable()->markRealInAppAsRead($messageId, $deviceUid);
    }

    /**
     *
     */
    public function push()
    {
        $success_ios = true;
        $success_android = true;
        $errors = [];

        foreach ($this->_types as $type => $class_name) {

            if ($type == 'ios') {

                if (in_array($this->getTargetDevices(), ["ios", "all", "", null])) {
                    try {
                        $ios_certificate = Core_Model_Directory::getBasePathTo(Push_Model_Certificate::getiOSCertificat($this->getAppId()));
                        if (is_readable($ios_certificate) && is_file($ios_certificate)) {
                            $instance = new Push_Model_Ios_Message(new Siberian_Service_Push_Apns(null, $ios_certificate));
                            $instance->setMessage($this);
                            $instance->push();
                        } else {
                            throw new \Siberian\Exception("You must provide an APNS Certificate for the App ID: {$this->getAppId()}");
                        }
                    } catch (\Exception $e) {
                        $this->logger->info(sprintf("[CRON: %s]: " . $e->getMessage(), date("Y-m-d H:i:s")), "cron_push");
                        $this->_log("Siberian_Service_Push_Apns", $e->getMessage());
                        $errors[] = $e->getMessage();

                        $success_ios = false;
                    }
                } else {
                    $this->logger->info(sprintf("[CRON: %s]: ios is not in the target list, skipping.", date("Y-m-d H:i:s")), "cron_push");
                    $this->_log("Siberian_Service_Push_Apns", "ios is not in the target list, skipping.");
                }
            }

            if ($type == 'android') {

                if (in_array($this->getTargetDevices(), ["android", "all", "", null])) {
                    try {

                        $gcmKey = Push_Model_Certificate::getAndroidKey();
                        $gcmInstance = null;
                        if (!empty($gcmKey)) {
                            $gcmInstance = new \Siberian\CloudMessaging\Sender\Gcm($gcmKey);
                        }

                        $credentials = (new Push_Model_Firebase())
                            ->find('0', 'admin_id');

                        $fcmKey = $credentials->getServerKey();
                        $fcmInstance = null;
                        if (!empty($fcmKey)) {
                            $fcmInstance = new \Siberian\CloudMessaging\Sender\Fcm($fcmKey);
                        }else {
                            // Only FCM is mandatory by now!
                            throw new \Siberian\Exception("You must provide FCM Credentials");
                        }

                        if ($fcmInstance || $gcmInstance) {
                            $instance = new Push_Model_Android_Message($fcmInstance, $gcmInstance);
                            $instance->setMessage($this);
                            $instance->push();
                        }

                    } catch (\Exception $e) {
                        print_r($e->getTraceAsString());
                        $this->logger->info(sprintf("[CRON: %s]: " . $e->getMessage(),
                            date("Y-m-d H:i:s")), "cron_push");
                        $this->_log("Siberian_Service_Push_Fcm", $e->getMessage());
                        $errors[] = $e->getMessage();

                        $success_android = false;
                    }
                } else {
                    $this->logger->info(sprintf("[CRON: %s]: android is not in the target list, skipping.", date("Y-m-d H:i:s")), "cron_push");
                    $this->_log("Siberian_Service_Push_Apns", "android is not in the target list, skipping.");
                }
            }
        }

        # Log errors in message
        if (!empty($errors)) {
            $errors[] = $this->getErrorText();
            $errors = array_filter($errors);
            $this->setErrorText(implode(",\n", $errors));
        }

        # If both iOS & Android failed
        if (!$success_ios && !$success_android) {
            $this->updateStatus('failed');
        } else {
            $this->updateStatus('delivered');
        }

    }

    /**
     * Create the log to fetch push inside app
     *
     * @param $device
     * @param $status
     * @param null $id
     * @return $this
     */
    public function createLog($device, $status, $id = null)
    {

        if (!$id) $id = $device->getDeviceUid();
        $is_displayed = !$this->getLatitude() && !$this->getLongitude();
        $datas = [
            'device_id' => $device->getId(),
            'device_uid' => $id,
            'device_type' => $device->getTypeId(),
            'is_displayed' => $is_displayed,
            'message_id' => $this->getId(),
            'status' => $status,
            'delivered_at' => $this->formatDate(null, 'y-MM-dd HH:mm:ss')
        ];

        $this->getTable()->createLog($datas);

        return $this;
    }

    /**
     * @param $status
     */
    public function updateStatus($status)
    {

        $this->setStatus($status);
        if ($status == 'delivered') {
            $this->setDeliveredAt($this->formatDate(null, 'y-MM-dd HH:mm:ss'));
        }

        $this->save();

    }

    /**
     * @param $message_type
     * @return $this
     */
    public function setMessageType($message_type)
    {
        $this->_messageType = $message_type;
        return $this;
    }

    public function setMessageTypeByOptionValue($optionValue)
    {
        $inapp_option_id = $this->getTable()->getInAppCode();
        switch ($optionValue) {
            case $inapp_option_id:
                $this->_messageType = self::TYPE_INAPP;
                break;
            default:
                $this->_messageType = self::TYPE_PUSH;
        }
    }

    /**
     * @return string
     */
    public function getCoverUrl()
    {
        $cover = $this->getCover();

        // If cover is already an URL, return it unchanged!
        if (preg_match("#^https?://#", $cover)) {
            return $cover;
        }

        $coverPath = Application_Model_Application::getImagePath() . $this->getCover();
        $baseCoverPath = Application_Model_Application::getBaseImagePath() . $this->getCover();
        if (strpos($cover, "/images/assets") === 0 &&
            is_file(path($cover))) {
            return $cover;
        }

        if ($this->getCover() &&
            is_file($baseCoverPath)) {
            return $coverPath;
        }

        return "";
    }

    public function getInAppCode()
    {
        return $this->getTable()->getInAppCode();
    }

    protected function _initInstances()
    {

        if (is_null($this->_instances)) {

            $this->_instances = [];
            foreach ($this->_types as $device => $type) {
                if ($device == 'iphone') {
                    $this->_instances[$device] = new $type(new Siberian_Service_Push_Apns(ApnsPHP_Push::ENVIRONMENT_SANDBOX));
                } else {
                    $this->_instances[$device] = new $type();
                }

            }
        }

        return $this->_instances;
    }

    /**
     * log for cron
     *
     * @param $service
     * @param $message
     */
    public function _log($service, $message)
    {
        printf("%s %s[%d]: %s\n",
            date('r'), $service, getmypid(), trim($message)
        );
    }

    public function _initMessageType()
    {
        if (is_null($this->_messageType)) {
            $this->_messageType = self::TYPE_PUSH;
        }
    }

    /**
     * Check individual push version
     * @param string $version minimum version required
     * @return bool Individual Push version is superior to $version or NULL if module is not installed
     */
    public static function isIndividualPushVersionCompliant($version)
    {
        if (self::hasIndividualPush()) {
            $module = new Installer_Model_Installer_Module();
            $module->prepare("IndividualPush", false);
            $id = $module->getId();
            if ($module->isInstalled() && !empty($id)) {
                return version_compare($module->getVersion(), $version, ">=");
            }
        }

        return null;
    }

    /**
     * @return bool
     */
    public static function hasIndividualPush()
    {
        $module = new Installer_Model_Installer_Module();
        $module->prepare("IndividualPush", false);

        return (
            $module->isInstalled() ||
            file_exists(Core_Model_Directory::getBasePathTo("app/local/modules/Push/Model/Customer/Message.php"))/** @remove after 4.2.x Backward compatibility if module not updated */
        );
    }

    /**
     * @wtf this name is really to long
     * @deprecated alias for hasIndividualPush()
     *
     * @return bool
     */
    public static function hasTargetedNotificationsModule()
    {
        return self::hasIndividualPush();
    }

    public static function getStatistics()
    {
        $db = Zend_Db_Table::getDefaultAdapter();
        $total = $db
            ->select()
            ->from("push_messages", ["total" => new Zend_Db_Expr("COUNT(*)")]);
        $success = $db
            ->select()
            ->from("push_messages", ["total" => new Zend_Db_Expr("COUNT(*)")])
            ->where("status = ?", "delivered");
        $queued = $db
            ->select()
            ->from("push_messages", ["total" => new Zend_Db_Expr("COUNT(*)")])
            ->where("status IN (?)", ["queued", "sending"]);
        $failed = $db
            ->select()
            ->from("push_messages", ["total" => new Zend_Db_Expr("COUNT(*)")])
            ->where("status = ?", "failed");

        $total = $db->fetchRow($total);
        $success = $db->fetchRow($success);
        $queued = $db->fetchRow($queued);
        $failed = $db->fetchRow($failed);

        $result = [
            "total" => ($total["total"]) ? $total["total"] : 0,
            "success" => ($success["total"]) ? $success["total"] : 0,
            "queued" => ($queued["total"]) ? $queued["total"] : 0,
            "failed" => ($failed["total"]) ? $failed["total"] : 0,
        ];

        return $result;
    }

    /**
     * @param $option Application_Model_Option_Value
     * @return string
     * @throws Exception
     */
    public function exportAction($option, $export_type = null)
    {
        if ($option && $option->getId()) {

            $current_option = $option;

            $dataset = [
                "option" => $current_option->forYaml(),
            ];

            try {
                $result = Siberian_Yaml::encode($dataset);
            } catch (\Exception $e) {
                throw new \Siberian\Exception("#089-03: An error occured while exporting dataset to YAML.");
            }

            return $result;

        } else {
            throw new \Siberian\Exception("#089-01: Unable to export the feature, non-existing id.");
        }
    }

    /**
     * @param $path
     * @throws Exception
     */
    public function importAction($path)
    {
        $content = file_get_contents($path);

        try {
            $dataset = Siberian_Yaml::decode($content);
        } catch (\Exception $e) {
            throw new \Siberian\Exception("#089-04: An error occured while importing YAML dataset '$path'.");
        }

        $application = $this->getApplication();
        $application_option = new Application_Model_Option_Value();

        if (isset($dataset["option"])) {
            $application_option
                ->setData($dataset["option"])
                ->unsData("value_id")
                ->unsData("id")
                ->setData('app_id', $application->getId())
                ->save();

        } else {
            throw new \Siberian\Exception("#089-02: Missing option, unable to import data.");
        }
    }
}
