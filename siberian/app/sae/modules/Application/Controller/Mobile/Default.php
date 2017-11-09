<?php

class Application_Controller_Mobile_Default extends Core_Controller_Default {

    protected static $_device;
    protected $_current_option_value;
    protected $_layout_id;

    public function init() {

        // Prevent CORS conversion method in dev environment
        if($this->getRequest()->getMethod() == "OPTIONS") {
            die;
        }

        parent::init();

        $this->_layout_id = 1;

        // Test si un id de value est passé en paramètre
        $id = $this->getRequest()->getParam('option_value_id');
        if (!$id) {
            $id = $this->getRequest()->getParam('value_id');
        }
        if (!$id) {
            try {
                $data = Zend_Json::decode($this->getRequest()->getRawBody());
                if ($data && !empty($data['value_id'])) $id = $data['value_id'];
            } catch(Zend_Json_Exception $e) {
                $id = null;
            } catch(Exception $e) {
                $id = null;
            }
        }

        // We are in an application
        Siberian::setApplication($this->getApplication());

        // Testing if value_id belongs to the app (or is allowed)
        if(!$this->getApplication()->valueIdBelongsTo($id) && $id) {
            $this->_sendJson(array(
                "error" => true,
                "message" => __("Unauthorized access to feature.")
            ), true);
        } else {
            if($id) {

                // Créé et charge l'objet
                $this->_current_option_value = new Application_Model_Option_Value();

                if($id != "homepage") {
                    $this->_current_option_value->find($id);
                    // Récupère le layout de l'option_value en cours
                    if($this->_current_option_value->getLayoutId()) {
                        $this->_layout_id = $this->_current_option_value->getLayoutId();
                    }
                } else {
                    $this->_current_option_value->setIsHomepage(true);
                }

            }

            if($this->getFullActionName('_') == 'front_mobile_home_view') {
                $this->_layout_id = $this->getApplication()->getLayoutId();
            }

            Core_View_Mobile_Default::setCurrentOption($this->_current_option_value);

            $this->_log();

            return $this;
        }
    }

    public function getDevice() {
        return self::$_device;
    }

    public static function setDevice($device) {
        self::$_device = $device;
    }

    public function isOverview() {
        return $this->getSession()->isOverview;
    }

    /**
     * @depecrated
     */
    public function viewAction() {
        $option = $this->getCurrentOptionValue();
        $this->loadPartials($this->getFullActionName('_').'_l'.$this->_layout_id, false);
        $html = array('html' => mb_convert_encoding($this->getLayout()->render(), 'UTF-8', 'UTF-8'), 'title' => $option->getTabbarName());
        if($url = $option->getBackgroundImageUrl()) $html['background_image_url'] = $url;
        $html['use_homepage_background_image'] = (int) $option->getUseHomepageBackgroundImage() && !$option->getHasBackgroundImage();
        $this->getLayout()->setHtml(Zend_Json::encode($html));
    }

    public function indexAction() {
        $this->forward('index', 'index', 'Front', $this->getRequest()->getParams());
    }

    public function templateAction() {
        $partialId = $this->getFullActionName('_').'_l'.$this->_layout_id;
        $this->loadPartials($partialId, false);
    }

    public function backgroundimageAction() {

        $urls = array("standard" => "", "hd" => "", "tablet" => "");
        $option = $this->getCurrentOptionValue();
        if($option->getUseHomepageBackgroundImage()) {
            $urls = array(
                "standard" => $this->getApplication()->getHomepageBackgroundImageUrl(),
                "hd" => $this->getApplication()->getHomepageBackgroundImageUrl("hd"),
                "tablet" => $this->getApplication()->getHomepageBackgroundImageUrl("tablet"),
            );
        }
        if($option->getHasBackgroundImage()) {
            $url = $option->getBackgroundImageUrl();
            $urls = array(
                "standard" => $url,
                "hd" => $url,
                "tablet" => $url,
            );
        }

        $this->_sendHtml($urls);

    }

    public function getCurrentOptionValue() {
        return $this->_current_option_value;
    }

    protected function _prepareHtml() {

        $option = $this->getCurrentOptionValue();
        $this->loadPartials($this->getFullActionName('_').'_l'.$this->_layout_id, false);
        $html = array('html' => mb_convert_encoding($this->getLayout()->render(), 'UTF-8', 'UTF-8'), 'title' => $option->getTabbarName());
        if($url = $option->getBackgroundImageUrl()) $html['background_image_url'] = $url;
        $html['use_homepage_background_image'] = (int) $option->getUseHomepageBackgroundImage() && !$option->getHasBackgroundImage();
        return $html;

    }

    /**
     * Send raw text
     *
     * @param $data
     */
    public function _sendRaw($data) {
        $response = $this->getResponse();

        Zend_Controller_Front::getInstance()->returnResponse(true);
        $response->sendResponse();
        echo $data;
        die;
    }

    /**
     * Converts an array to json, set the header code to 400 if error
     *
     * @param $data
     * @param bool $send
     */
    public function _sendJson($data, $send = false) {
        $response = $this->getResponse();

        $response->setHeader("Content-type", "application/json");

        if(isset($data["error"]) && !empty($data["error"])) {

            if(isset($data["gone"]) && $data["gone"]) {
                /** Resource is gone */
                $response->setHttpResponseCode(410);
            } else {
                $response->setHttpResponseCode(400);
            }

        }

        /** Handle development case, unset exception messages in production. */
        if(!Siberian_Debug::isDevelopment() && isset($data["exceptionMessage"])) {
            unset($data["exceptionMessage"]);
        }

        $json = Siberian_Json::encode($data);

        $this->getLayout()->setHtml($json);

        # Abort current request and send immediate response
        if($send === true) {
            Zend_Controller_Front::getInstance()->returnResponse(true);
            $response->sendResponse();
            echo $json;
            die;
        }

    }

    /**
     * @deprecated
     *
     * @param $html
     */
    protected function _sendHtml($html) {
        $this->_sendJson($html);
    }

    protected function _log() {

        if($this->getRequest()->isGet() &&
            $this->getFullActionName("/") == "front/mobile/backgroundimage" &&
            $this->getDevice()->isNative()
        ) {

            $log = new Core_Model_Log();
            $detect = new Mobile_Detect();

            $host = !empty($_SERVER['REMOTE_HOST']) ? $_SERVER['REMOTE_HOST'] : '';
            $user_agent = !empty($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
            $other = array(
                'user_agent' => $user_agent,
                'host' => $host
            );

            $value_id = $this->getCurrentOptionValue()->getId() | 0;

            if($this->getSession()->getCustomerId()) $log->setCustomerId($this->getSession()->getCustomerId());
            $log->setCustomerId($this->getSession()->getCustomerId())
                ->setAppId($this->getApplication()->getId())
                ->setValueId($value_id)
                ->setDeviceName($detect->getDeviceName())
                ->setOther(serialize($other))
                ->save()
                ;

        }

        return $this;
    }

    protected function _durationSince($entry) {

        $date = new Zend_Date($entry);
        $now = Zend_Date::now();
        $difference = $now->sub($date);

        $seconds = $difference->toValue() % 60;
        $allMinutes = ($difference->toValue() - $seconds) / 60;
        $minutes = $allMinutes % 60;
        $allHours = ($allMinutes - $minutes) / 60;
        $hours =  $allHours % 24;
        $allDays = ($allHours - $hours) / 24;
        $allDays.= ' ';
        $hours.= ' ';
        $minutes.= ' ';
        $seconds.= ' ';

        if($allDays > 0) {
            if($allDays == 1) $allDays = $allDays." ".__('day');
            else $allDays = $allDays." ".__('days');
        } else {
            $allDays = '';
        }

        if($hours > 0) {
            if($hours == 1) $hours = $hours ." ".__('hour');
            else $hours = $hours." ".__('hours');
        } else {
            $hours = '';
        }

        if($minutes > 0) {
            if($minutes == 1) $minutes = $minutes ." ".__('minute');
            else $minutes = $minutes." ".__('minutes');
        } else {
            $minutes = '';
        }

        if($seconds > 0) {
            if($seconds == 1) $seconds = $seconds ." ".__('second');
            else $seconds = $seconds." ".__('seconds');
        } else {
            $seconds = '';
        }

        $updated_at = '';
        if($allDays != '') {
            $updated_at = $allDays;
        } elseif($hours != '') {
            $updated_at = $hours;
        } elseif($minutes != '') {
            $updated_at = $minutes;
        } else {
            $updated_at = $seconds;
        }

        return __('%s ago', $updated_at);
    }

}
