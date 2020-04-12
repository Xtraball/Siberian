<?php

use Siberian\Exception;

/**
 * Class Push_DeviceController
 */
class Push_DeviceController extends Core_Controller_Default
{
    /**
     * Register Device
     *
     * @route /push/device/is-registered
     */
    public function isRegisteredAction()
    {
        $request = $this->getRequest();
        try {
            $params = $request->getBodyParams();
            $deviceUid = $params['device_uid'];
            $deviceType = $params['type'];

            if ($deviceType === 'android') {
                $device = (new Push_Model_Android_Device())->find($deviceUid, 'device_uid');
                if (!$device && !$device->getId()) {
                    throw new Exception(p__('push', 'Android device is not registered.'));
                }
                $deviceToken = $device->getRegistrationId();
            } else if ($deviceType === 'ios') {
                $device = (new Push_Model_Iphone_Device())->find($deviceUid, 'device_uid');
                if (!$device && !$device->getId()) {
                    throw new Exception(p__('push', 'iOS device is not registered.'));
                }
                $deviceToken = $device->getDeviceToken();
            } else {
                throw new Exception(p__('push', 'Device type %s does not exists.', $deviceType));
            }

            $payload = [
                'success' => true,
                'token' => $deviceToken,
            ];
        } catch (\Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }
}
