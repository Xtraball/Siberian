<?php

/**
 * Class Push_Model_Message_Global
 *
 * @method integer getId()
 * @method array getTargetDevices()
 * @method $this setSendToAll(boolean $sendToAll)
 * @method $this setTargetApps(string $targetApps)
 * @method $this setTargetDevices(array $targetDevices)
 * @method $this setCover(string $cover)
 * @method $this setUrl(string $url)
 * @method string getTargetApps()
 * @method boolean getSendToAll()
 */
class Push_Model_Message_Global extends Core_Model_Default
{
    /**
     * Push_Model_Message_Global constructor.
     * @param array $datas
     */
    public function __construct($datas = [])
    {
        parent::__construct($datas);
        $this->_db_table = 'Push_Model_Db_Table_Message_Global';
    }

    /**
     * Create global push.
     *
     * @param $params
     */
    public function createInstance($params, $backoffice = false)
    {
        $this->setTitle($params['title']);
        $this->setMessage($params['message']);
        $this->setSendToAll(filter_var($params['send_to_all'], FILTER_VALIDATE_BOOLEAN));
        $this->setTargetApps(Siberian_Json::encode($params['checked']));
        $this->setTargetDevices($params['devices']);
        $this->setData('base_url', $params['base_url']);
        $this->setCover($params['cover']);
        if (filter_var($params['open_url'], FILTER_VALIDATE_BOOLEAN)) {
            $this->setUrl($params['url']);
        }

        $applications = Siberian_Json::decode($this->getTargetApps());
        $application_table = new Application_Model_Db_Table_Application();
        if (!!$this->getSendToAll()) {
            $all_applications = $application_table->findAllForGlobalPush();

            // Get apps that belong to the current admin!
            $all_for_admin = $application_table->findAllByAdmin(
                $this->getSession()->getAdminId()
            )->toArray();

            $filtered = array_map(function ($app) {
                return $app['app_id'];
            }, $all_for_admin);

            // We keep only apps that belongs to the admin!
            if (!$backoffice) {
                $applications = array_intersect($all_applications, $filtered);
            } else {
                $applications = $all_applications;
            }
        } else {
            // Get apps that belong to the current admin in case any injection to other apps is tested!
            if (!Siberian_Version::is('sae')) {
                $all_for_admin = $application_table->findAllByAdmin(
                    $this->getSession()->getAdminId()
                )->toArray();
                $filtered = array_map(function ($app) {
                    return $app['app_id'];
                }, $all_for_admin);
                $applications = array_intersect($applications, $filtered);
            }
        }

        try {
            if (!empty($applications)) {
                $this->save();

                foreach ($applications as $application_id) {
                    $application_id = intval($application_id);

                    $pushMessage = new Push_Model_Message();
                    $pushMessage->setMessageGlobalId($this->getId());
                    $pushMessage->setTargetDevices($this->getTargetDevices());
                    $pushMessage->setAppId($application_id);
                    $pushMessage->setSendToAll(true);
                    $pushMessage->setTitle($this->getTitle());
                    $pushMessage->setText($this->getMessage());
                    $pushMessage->setSendUntil(null);
                    $pushMessage->setData('base_url', $params['base_url']);

                    // Custom image!
                    $pushMessage->setCover($params['cover']);

                    if (!empty($this->getUrl())) {
                        $url = file_get_contents('https://tinyurl.com/api-create.php?url=' .
                            urlencode($this->getData('url')));
                        $pushMessage->setActionValue($url);
                    }

                    $pushMessage->save();
                }
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {
            echo $e->getMessage();
            die;
            # Add a log.
            return false;
        }
    }

    /**
     * @return array|bool|mixed|null|string
     */
    public function getTitle()
    {
        return !!$this->getData("base64") ? base64_decode($this->getData("title")) : $this->getData("title");
    }

    /**
     * @return array|bool|mixed|null|string
     */
    public function getMessage()
    {
        return !!$this->getData("base64") ? base64_decode($this->getData("message")) : $this->getData('message');
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
            "message" => base64_encode($text)
        ]);
    }

    /**
     * @param $text
     * @return $this
     */
    public function setMessage($text)
    {
        $title = $this->getTitle();
        return $this->addData([
            "base64" => 1,
            "title" => base64_encode($title),
            "message" => base64_encode($text)
        ]);
    }

}
