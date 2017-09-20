<?php

class Push_MobileController extends Application_Controller_Mobile_Default {

    public function listAction() {

        $this->loadPartials($this->getFullActionName('_').'_l'.$this->_layout_id, false);
        $html = array();
        $device_uid = $this->_getDeviceUid();
        $messages = array();

        if($device_uid) {
            $option_value = $this->getCurrentOptionValue();

            $message = new Push_Model_Message();
            $message->setMessageTypeByOptionValue($option_value->getOptionId());
            $messages = $message->findByDeviceId($device_uid, $option_value->getAppId());
            $message->markAsRead($device_uid);
        }

        $this->getLayout()->getPartial('content')->setNotifs($messages);
        $html = array('html' => $this->getLayout()->render());
        $this->getLayout()->setHtml(Zend_Json::encode($html));
    }

    public function countAction() {

        $html = array();
        $device_uid = $this->_getDeviceUid();
        $nbr = 0;
        if($device_uid) {
            $message = new Push_Model_Message();
            $nbr = $message->countByDeviceId($device_uid);
        }

        $html = array('count' => $nbr);
        $this->getLayout()->setHtml(Zend_Json::encode($html));

    }

    public function lastmessagesAction() {
        $data = array();
        $request = $this->getRequest();
        $baseUrl = $request->getBaseUrl();
        if($device_uid = $request->getParam('device_uid')) {
            $application = $this->getApplication();

            $message = new Push_Model_Message();
            $message->findLastPushMessage($device_uid, $application->getId());

            if($message->getId()) {
                // We read this push!
                $message->markAsRead($device_uid,$message->getMessageId());

                if(is_numeric($message->getActionValue())) {
                    $option_value = new Application_Model_Option_Value();
                    $option_value->find($message->getActionValue());
                    $action_url = $option_value->getPath(
                        null,
                        array('value_id' => $option_value->getId()),
                        false
                    );
                } else {
                    $action_url = $message->getActionValue();
                }

                $data['push_message'] = [
                    'cover' => $message->getCoverUrl() ?
                        $baseUrl . $message->getCoverUrl() :
                        null,
                    'action_value' => $action_url,
                    'open_webview' => !is_numeric($message->getActionValue()),
                    'additionalData' => [
                        'message_id' => $message->getId(),
                        'action_value' => $action_url,
                        'open_webview' => !is_numeric($message->getActionValue()),
                        'cover' => $message->getCoverUrl() ?
                            $baseUrl . $message->getCoverUrl() :
                            null,
                    ],
                    'message' => $message->getText(),
                    'title' => $message->getTitle(),
                    'text' => $message->getText()
                ];
            }

            $message = new Push_Model_Message();
            $message->findLastInAppMessage(
                $application->getId(),
                $device_uid
            );

            if($message->getId()) {
                $data['inapp_message'] = array(
                    'title' => $message->getTitle(),
                    'text' => $message->getText(),
                    'message' => $message->getText(),
                    'message_id' => $message->getId(),
                    'additionalData' => [
                        'message_id' => $message->getId(),
                        'open_webview' => false,
                        'cover' => $message->getCoverUrl() ?
                            $baseUrl . $message->getCoverUrl() :
                            null,
                    ],
                    'cover' => $message->getCoverUrl() ?
                        $baseUrl . $message->getCoverUrl() :
                        null
                );
            }
        }

        $this->_sendJson($data);
    }

    public function readinappAction(){
        $data = array();
        if($device_uid = $this->getRequest()->getParam('device_uid') AND $device_type = $this->getRequest()->getParam('device_type')) {
            $message = new Push_Model_Message();
            $message->markInAppAsRead($this->getApplication()->getId(), $device_uid, $device_type);
            $data = array(
                "message" => "Success."
            );
        }
        $this->_sendJson($data);
    }

    protected function _getDeviceUid() {

        $id = null;
        if($device_uid = $this->getRequest()->getParam('device_uid')) {
            if(!empty($device_uid)) {
                if(strlen($device_uid) == 36) {
                    $device = new Push_Model_Iphone_Device();
                    $device->find($device_uid, 'device_uid');
                    $id = $device->getDeviceUid();
                }
                else {
                    $device = new Push_Model_Android_Device();
                    $device->find($device_uid, 'registration_id');
                    $id = $device->getRegistrationId();
                }
            }

        }

        return $id;

    }

}