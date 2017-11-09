<?php

class Push_IphoneController extends Core_Controller_Default
{

    /**
     * Register Device
     *
     */
    public function registerdeviceAction() {

        $request = $this->getRequest();
        if($request->isPost()) {
            $params = Zend_Json::decode($request->getRawBody());
        } else {
            $params = $request->getParams();
        }

        if($params) {

            $fields = array(
                'app_id',
                'app_name',
                'app_version',
                'device_uid',
                'device_token',
                'device_name',
                'device_model',
                'device_version',
                'push_badge',
                'push_alert',
                'push_sound',
            );

            foreach($params as $key => $value) {
                if(!in_array($key, $fields)) {
                    unset($params[$key]);
                }
            }

            $params['status'] = 'active';

            $device = new Push_Model_Iphone_Device();

            # One couple per app 4.2 (not searching the token, in case we need to update it)
            $device->find(array(
                'app_id' => $params['app_id'],
                'device_uid' => $params['device_uid'],
                //'device_token' => $params['device_token'],
            ));

            $device->addData($params)->save();

            $message = new Push_Model_Message();

            $this->getLayout()->setHtml($message->countByDeviceId($device->getDeviceUid()));
        }

    }

    /**
     * Set this message as displayed
     */
    public function markdisplayedAction() {

        if($params = $this->getRequest()->getParams()) {

            if(empty($params['device_uid']) OR empty($params['message_id'])) return;

            $device = new Push_Model_Iphone_Device();
            $device->find($params['device_uid'], 'device_uid');
            $message = new Push_Model_Message();
            $message->markAsDisplayed($device->getId(), $params['message_id']);

        }

        die;

    }

    public function updatepositionAction() {

        if($params = $this->getRequest()->getPost()) {

            if(empty($params['device_uid'])) return;

            $device = new Push_Model_Iphone_Device();
            $device->find($params['device_uid'], 'device_uid');
            if(!$device->getId()) {
                $device->setDeviceUid($params['device_uid'])
                    ->setAppId($params['app_id'])
                ;
            }

            $messages = $device->findNotReceivedMessages(true);

            if($messages->count() > 0) {
                foreach($messages as $message) {
                    $instance = $message->getInstance('iphone');
                    $instance->setMessage($message);
                    $instance->createConnection();
                    $instance->sendMessage($device);
                }
            }

            die;

        }

    }

}