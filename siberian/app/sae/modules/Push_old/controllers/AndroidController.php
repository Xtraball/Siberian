<?php

use Siberian\Hook;
use Siberian\Exception;

/**
 * Class Push_AndroidController
 */
class Push_AndroidController extends Core_Controller_Default
{
    /**
     * Register Device
     *
     */
    public function registerdeviceAction()
    {
        $request = $this->getRequest();
        $params = $request->getBodyParams();
        $session = $this->getSession();

        try {
            if (empty($params['registration_id'])) {
                throw new Exception(p__('push', 'Registration ID is missing.'));
            }

            $fields = [
                'app_id',
                'app_name',
                'device_uid',
                'registration_id',
                'provider',
            ];

            foreach ($params as $key => $value) {
                if (!in_array($key, $fields)) {
                    unset($params[$key]);
                }
            }

            $params['development'] = 'production';
            $params['status'] = 'active';
            $params['provider'] = 'fcm';
            $params['registration_id'] = base64_decode($params['registration_id']);

            $device = new Push_Model_Android_Device();
            $device->find([
                'app_id' => $params['app_id'],
                'device_uid' => $params['device_uid'],
            ]);

            /**
             * Ensure individual push is always registered
             */
            if ($session->isLoggedIn() &&
                $session->getCustomerId()) {
                $params['customer_id'] = $session->getCustomerId();
            }

            $device
                ->addData($params)
                ->save();

            Hook::trigger('push.android.update_token', [
                'request' => $request,
                'device' => $device
            ]);

            $payload = [
                'success' => true,
                'enabled' => $device->getPushAlert() === 'enabled',
                'message' => p__('push', 'Successfully registered %s to push with token %s',
                    'Android', $params['registration_id'])
            ];
        } catch (\Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }

    /**
     * Set this message as displayed
     */
    public function markdisplayedAction()
    {
        if ($params = $this->getRequest()->getParams()) {

            if (empty($params['device_uid']) || empty($params['message_id'])) {
                return;
            }

            $device = (new Push_Model_Android_Device())->findByDeviceUid($params['device_uid']);
            if (!$device && !$device->getId()) {
                return;
            }

            (new Push_Model_Message())->markAsDisplayed($device->getId(), $params['message_id']);
        }

        die;
    }

    /**
     * Update position of device and send pending messages for this zone
     * @return type
     */
    public function updatepositionAction()
    {

        if ($params = $this->getRequest()->getParams()) {

            if (empty($params['latitude']) OR empty($params['longitude']) OR empty($params['registration_id'])) return;

            $device = new Push_Model_Android_Device();
            $device->findByRegistrationId($params['registration_id']);

            $messages = $device->findNotReceivedMessages(true);

            if ($messages->count() > 0) {
                foreach ($messages as $message) {
                    $instance = $message->getInstance('android');
                    $instance->setMessage($message);
                    $instance->sendMessage([$device->getRegistrationId()]);
                }
            }

            die;

        }

    }
}
