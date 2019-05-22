<?php


/**
 * Class Push_MobileController
 */
class Push_MobileController extends Application_Controller_Mobile_Default
{
    /**
     *
     */
    public function listAction()
    {

        $this->loadPartials($this->getFullActionName('_') . '_l' . $this->_layout_id, false);
        $html = [];
        $device_uid = $this->_getDeviceUid();
        $messages = [];

        if ($device_uid) {
            $option_value = $this->getCurrentOptionValue();

            $message = new Push_Model_Message();
            $message->setMessageTypeByOptionValue($option_value->getOptionId());
            $messages = $message->findByDeviceId($device_uid, $option_value->getAppId());
            $message->markAsRead($device_uid);
        }

        $this->getLayout()->getPartial('content')->setNotifs($messages);
        $html = ['html' => $this->getLayout()->render()];
        $this->getLayout()->setHtml(Zend_Json::encode($html));
    }

    /**
     * @throws Zend_Exception
     */
    public function countAction()
    {
        $device_uid = $this->_getDeviceUid();
        $nbr = 0;
        if ($device_uid) {
            $message = new Push_Model_Message();
            $nbr = $message->countByDeviceId($device_uid);
        }

        $payload = [
            'success' => true,
            'count' => $nbr,
        ];

        $this->_sendJson($payload);
    }

    /**
     * @throws Zend_Exception
     */
    public function lastmessagesAction()
    {
        $data = [];
        $request = $this->getRequest();
        $baseUrl = $request->getBaseUrl();
        if ($device_uid = $request->getParam("device_uid")) {
            $application = $this->getApplication();

            $message = new Push_Model_Message();
            $message->findLastPushMessage($device_uid, $application->getId());

            if ($message->getId()) {
                // We read this push!
                $message->markAsRead($device_uid, $message->getMessageId());


                if (is_numeric($message->getActionValue())) {
                    $option_value = new Application_Model_Option_Value();
                    $option_value->find($message->getActionValue());

                    $mobileUri = $option_value->getMobileUri();
                    if (preg_match("/^goto\/feature/", $mobileUri)) {
                        $action_url = sprintf("/%s/%s/value_id/%s",
                            $application->getKey(),
                            $mobileUri,
                            $option_value->getId());
                    } else {
                        $action_url = sprintf("/%s/%sindex/value_id/%s",
                            $application->getKey(),
                            $option_value->getMobileUri(),
                            $option_value->getId());
                    }
                } else {
                    $action_url = $message->getActionValue();
                }

                $data["push_message"] = [
                    "cover" => $message->getCoverUrl() ?
                        $baseUrl . $message->getCoverUrl() :
                        null,
                    "action_value" => $action_url,
                    "open_webview" => !is_numeric($message->getActionValue()),
                    "additionalData" => [
                        "message_id" => $message->getId(),
                        "action_value" => $action_url,
                        "open_webview" => !is_numeric($message->getActionValue()),
                        "cover" => $message->getCoverUrl() ?
                            $baseUrl . $message->getCoverUrl() :
                            null,
                    ],
                    "message" => $message->getText(),
                    "title" => $message->getTitle(),
                    "text" => $message->getText()
                ];
            }

            $message = (new Push_Model_Message())->findLastInAppMessage(
                $application->getId(),
                $device_uid
            );

            if ($message->getId()) {
                $data["inapp_message"] = [
                    "title" => $message->getTitle(),
                    "text" => $message->getText(),
                    "message" => $message->getText(),
                    "message_id" => $message->getId(),
                    "additionalData" => [
                        "message_id" => $message->getId(),
                        "open_webview" => false,
                        "cover" => $message->getCoverUrl() ?
                            $baseUrl . $message->getCoverUrl() :
                            null,
                    ],
                    "cover" => $message->getCoverUrl() ?
                        $baseUrl . $message->getCoverUrl() :
                        null
                ];

                // Add to read messages!
                (new Push_Model_Message())
                    ->markRealInAppAsRead(
                        $message->getId(),
                        $device_uid);
            }
        }

        $this->_sendJson($data);
    }

    /**
     * @throws Zend_Exception
     */
    public function readinappAction()
    {
        $data = [];
        if ($device_uid = $this->getRequest()->getParam('device_uid') AND $device_type = $this->getRequest()->getParam('device_type')) {
            $message = new Push_Model_Message();
            $message->markInAppAsRead($this->getApplication()->getId(), $device_uid, $device_type);
            $data = [
                "message" => "Success."
            ];
        }
        $this->_sendJson($data);
    }

    /**
     * @return null
     */
    protected function _getDeviceUid()
    {

        $id = null;
        if ($device_uid = $this->getRequest()->getParam('device_uid')) {
            if (!empty($device_uid)) {
                if (strlen($device_uid) == 36) {
                    $device = new Push_Model_Iphone_Device();
                    $device->find($device_uid, 'device_uid');
                    $id = $device->getDeviceUid();
                } else {
                    $device = new Push_Model_Android_Device();
                    $device->find($device_uid, 'registration_id');
                    $id = $device->getRegistrationId();
                }
            }

        }

        return $id;

    }

}