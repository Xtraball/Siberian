<?php

use Siberian\Json;

/**
 * Class Push_Mobile_ListController
 */
class Push_Mobile_ListController extends Application_Controller_Mobile_Default
{

    public function deleteAction ()
    {
        try {
            $request = $this->getRequest();
            $params = $request->getBodyParams();
            $deviceUid = $params['device_uid'];
            $deliverId = $params['deliver_id'];

            // Sample use-case for overview test
            if ('sample' === $deliverId) {
                $this->_sendJson([
                    'success' => true,
                    'message' => p__('push', '[Sample] Notification deleted!<br /><br />This is a sample overview, no action were done!')
                ], true);
            }

            if (empty($deliverId) || empty($deviceUid)) {
                throw new Exception(p__('push', 'Missing parameters!'));
            }

            $pushDelivered = (new Push_Model_DeliveredMessage())->find([
                'deliver_id' => $deliverId,
                'device_uid' => $deviceUid,
            ]);
            if (!$pushDelivered || !$pushDelivered->getId()) {
                throw new Exception(p__('push', "This record doesn't exists!"));
            }

            $pushDelivered->delete();

            $payload = [
                'success' => true,
                'message' => p__('push', 'Notification deleted!')
            ];
        } catch (\Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }

        $this->_sendJson($payload);
    }

    /**
     * @throws Zend_Date_Exception
     * @throws Zend_Exception
     * @throws Zend_Locale_Exception
     */
    public function findallAction()
    {
        $application = $this->getApplication();
        $payload = ['collection' => []];
        $option = $this->getCurrentOptionValue();
        $color = $this->getApplication()->getBlock('background')->getColor();
        $offset = $this->getRequest()->getParam('offset', 0);

        if ($device_uid = $this->getRequest()->getParam('device_uid')) {
            $option_value = $this->getCurrentOptionValue();

            $message = new Push_Model_Message();
            $message->setMessageTypeByOptionValue($option_value->getOptionId());
            $messages = $message->findByDeviceId($device_uid, $option_value->getAppId(), $offset);

            $icon_new = $this->getRequest()->getBaseUrl() . ($option->getImage()->getCanBeColorized() ?
                    $this->_getColorizedImage($option->getIconId(), $color) : $option->getIconUrl());
            $icon_pencil = $this->getRequest()->getBaseUrl() .
                $this->_getColorizedImage($this->_getImage('pictos/pencil.png'), $color);

            foreach ($messages as $message) {

                $message_value_id = $message->getValueId();

                if (!empty($message_value_id)) {
                    continue; # We skip it
                }

                $meta = [
                    'date' => [
                        'picto' => $icon_pencil,
                        'text' => datetime_to_format($message->getSendAt() ?? $message->getCreatedAt()),
                        'mt_text' => $message->getSendAt() ?? $message->getCreatedAt()
                    ]
                ];

                if (!$message->getIsRead()) {
                    $meta['likes'] = [
                        'picto' => $icon_new,
                        'text' => __('New')
                    ];
                }

                if (preg_match('#^/images/assets#', $message->getCover())) {
                    $picture = $message->getCover() ?
                        $this->getRequest()->getBaseUrl() .
                        $message->getCover() : null;
                } else {
                    $picture = $message->getCover() ?
                        $this->getRequest()->getBaseUrl() .
                        Application_Model_Application::getImagePath() .
                        $message->getCover() : null;
                }

                $action_value = null;
                $url = null;


                if (is_numeric($message->getActionValue())) {
                    $option_value = new Application_Model_Option_Value();
                    $option_value->find($message->getActionValue());

                    $mobileUri = $option_value->getMobileUri();
                    if (preg_match("/^goto\/feature/", $mobileUri)) {
                        $action_value = sprintf("/%s/%s/value_id/%s",
                            $application->getKey(),
                            $mobileUri,
                            $option_value->getId());
                    } else {
                        $action_value = sprintf("/%s/%sindex/value_id/%s",
                            $application->getKey(),
                            $option_value->getMobileUri(),
                            $option_value->getId());
                    }
                } else {
                    $url = $message->getActionValue();
                }

                if ($this->getApplication()->getIcon(74)) {
                    $icon = $this->getRequest()->getBaseUrl() . $this->getApplication()->getIcon(74);
                } else {
                    $icon = null;
                }

                $payload['collection'][] = [
                    'id' => (integer)$message->getId(),
                    'deliver_id' => (integer)$message->getDeliverId(),
                    'author' => $message->getTitle(),
                    'message' => $message->getText(),
                    'topic' => $message->getLabel(),
                    'details' => $meta,
                    'picture' => $picture,
                    'icon' => $icon,
                    'action_value' => $action_value,
                    'url' => $url
                ];
            }

            $message->markAsRead($device_uid);

        }

        // Settings
        try {
            $dbSettings = Json::decode($option->getSettings());
        } catch (\Exception $e) {
            $dbSettings = [];
        }
        $settings = array_merge([
            'design' => 'list',
        ], $dbSettings);

        $payload['page_title'] = $this->getCurrentOptionValue()->getTabbarName();
        $payload['displayed_per_page'] = Push_Model_Message::DISPLAYED_PER_PAGE;
        $payload['settings'] = $settings;

        $this->_sendJson($payload);

    }

    public function getSampleAction ()
    {
        $application = $this->getApplication();
        $icon = $application->getIcon(128);
        $mainDomain = __get('main_domain');
        $image = "https://{$mainDomain}/app/sae/modules/Job/features/job/assets/templates/l1/img/job-header.png";

        $icon = $application::getBaseImagePath() . $application->getData('icon');
        $icon = Siberian_Image::open($icon);
        $icon->resize(128);
        $icon->fillBackground(0xf3f3f3);
        $icon = $icon->inline('png', 100);

        $collection = [];
        for ($i = 0; $i < 10; $i++) {
            $year = date('Y') - $i;
            $month = date('m') - $i;
            if ($month < 1) {
                $month = 12 - $i;
            }
            $date = date( $year . '-' . $month . '-d H:i:s');
            $collection[] = [
                'id' => (integer) $i,
                'deliver_id' => 'sample',
                'author' => ($i % 2 === 0) ? p__('push', 'John DOE') : p__('push', 'Jane DOE'),
                'message' => p__('push', 'This a sample push message, this sample is only available from the application overview. Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.'),
                'topic' => '',
                'details' => [
                    'date' => [
                        'picto' => '',
                        'text' => datetime_to_format($date),
                        'mt_text' => $date
                    ]
                ],
                'picture' => $image,
                'icon' => $icon,
                'action_value' => '',
                'url' => 'https://w3.org'
            ];
        }

        // Settings
        try {
            $option = $this->getCurrentOptionValue();
            $dbSettings = Json::decode($option->getSettings());
        } catch (\Exception $e) {
            $dbSettings = [];
        }
        $settings = array_merge([
            'design' => 'list',
        ], $dbSettings);

        $this->_sendJson([
            'success' => true,
            'settings' => $settings,
            'collection' => $collection
        ]);
    }

    /**
     * @throws Zend_Exception
     */
    public function countAction()
    {
        $nbr = 0;
        if ($device_uid = $this->getRequest()->getParam('device_uid')) {
            $message = new Push_Model_Message();
            $message->setMessageTypeByOptionValue($this->getCurrentOptionValue()->getOptionId());
            $nbr = $message->countByDeviceId($device_uid);
        }

        $this->_sendJson([
            'count' => $nbr
        ]);
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
