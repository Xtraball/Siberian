<?php

class Push_AndroidController extends Core_Controller_Default
{

    /**
     * Register Device
     *
     */
    public function registerdeviceAction() {

        $request = $this->getRequest();
        if($request->isPost()) {
            try {
                $params = Zend_Json::decode($request->getRawBody());
            } catch(Exception $e) {
                /** Catch for old angular apps in POST */
                $params = $request->getParams();
            }
        } else {
            $params = $request->getParams();
        }

        if($params) {

            if(!empty($params['registration_id'])) {
                $fields = array(
                    'app_id',
                    'app_name',
                    'device_uid',
                    'registration_id',
                );

                foreach($params as $key => $value) {
                    if(!in_array($key, $fields)) unset($params[$key]);
                }

                $params['development'] = 'production';
                $params['status'] = 'active';

                $device = new Push_Model_Android_Device();

                $app = new Application_Model_Application();
                $app->find($params['app_id']);

                # if ionic and base64 we decode
                if($app->useIonicDesign() AND preg_match("~^([A-Za-z0-9+/]{4})*([A-Za-z0-9+/]{4}|[A-Za-z0-9+/]{3}=|[A-Za-z0-9+/]{2}==)$~", trim($params['registration_id']))) {
                    $params['registration_id'] = base64_decode($params['registration_id']);
                }

                # One couple per app 4.2 (not searching the token, in case we need to update it)
                if(!isset($params['device_uid'])) {
                    /** Case for Angular without device_uid */
                    $device->find(array(
                        'app_id' => $params['app_id'],
                        'registration_id' => $params['registration_id'],
                    ));
                } else {
                    /** Case for Ionic apps */
                    $device->find(array(
                        'app_id' => $params['app_id'],
                        'device_uid' => $params['device_uid'],
                    ));
                }

                $device->addData($params)->save();

                die('success');
            }

        }

    }


    /**
     * Set this message as displayed
     */
    public function markdisplayedAction() {

        if($params = $this->getRequest()->getParams()) {

            $device = new Push_Model_Android_Device();

            $app = Application_Model_Application::getInstance();
            if($app->useIonicDesign()) {
                if(empty($params['device_uid']) OR empty($params['message_id'])) return;

                $device->findByDeviceUid($params['device_uid']);
            } else {
                if(empty($params['registration_id']) or empty($params['message_id'])) return;

                $device->findByRegistrationId($params['registration_id']);
            }

            $message = new Push_Model_Message();
            $message->markAsDisplayed($device->getId(), $params['message_id']);

        }

        die;

    }

    /**
     * Update position of device and send pending messages for this zone
     * @return type
     */
    public function updatepositionAction() {

        if($params = $this->getRequest()->getParams()) {

            if(empty($params['latitude']) OR empty($params['longitude']) OR empty($params['registration_id'])) return;

            $device = new Push_Model_Android_Device();
            $device->findByRegistrationId($params['registration_id']);

            $messages = $device->findNotReceivedMessages(true);

            if($messages->count() > 0) {
                foreach($messages as $message) {
                    $instance = $message->getInstance('android');
                    $instance->setMessage($message);
                    $instance->sendMessage(array($device->getRegistrationId()));
                }
            }

            die;

        }

    }
}
