<?php

namespace Push2\Mobile;

use Push2\Model\Onesignal\Message;
use Push2\Model\Onesignal\Player;
use Siberian\Image;
use Siberian\Json;
use \Application_Controller_Mobile_Default as MobileController;

/**
 * Class ListController
 */
class ListController extends MobileController
{
    public function findAllAction()
    {
        $application = $this->getApplication();
        $payload = ['collection' => []];
        $option = $this->getCurrentOptionValue();
        $request = $this->getRequest();
        $color = $this->getApplication()->getBlock('background')->getColor();
        $session = $this->getSession();
        $customer = $session->getCustomer();
        $player_id = null;

        if ($customer && $customer->getId()) {
            $player_id = (new Player())->find($customer->getId(), 'customer_id')->getPlayerId();
        }

        //$offset = $this->getRequest()->getParam('offset', 0);

        //if ($device_uid = $this->getRequest()->getParam('device_uid')) {
        //$option_value = $this->getCurrentOptionValue();

        $messages = (new Message())->findAllForPlayer($application->getId(), $player_id);

        //$icon_new = $request->getBaseUrl() . ($option->getImage()->getCanBeColorized() ?
        //        $this->_getColorizedImage($option->getIconId(), $color) : $option->getIconUrl());
        $icon_pencil = $request->getBaseUrl() .
            $this->_getColorizedImage($this->_getImage('pictos/pencil.png'), $color);

        $now = time();
        foreach ($messages as $message) {

            // Checking send_after
            // Feb 13 2023 00:00:00 +01:00
            $sendAfter = $message->getSendAfter();
            if (!empty($sendAfter)) {
                $sendAfterDate = date_create_from_format('M d Y H:i:s O', $message->getSendAfter());
                list($hour, $minute) = explode(':', $message->getDeliveryTimeOfDay());
                $sendAfterDate->setTime($hour, $minute);

                // Skip scheduled push messages!
                if ($sendAfterDate->getTimestamp() > $now) {
                    continue;
                }
            }

            $meta = [
                'date' => [
                    'picto' => $icon_pencil,
                    'text' => datetime_to_format($message->getCreatedAt()),
                    'mt_text' => $message->getCreatedAt()
                ]
            ];

            //if (!$message->getIsRead()) {
            //    $meta['likes'] = [
            //        'picto' => $icon_new,
            //        'text' => __('New')
            //    ];
            //}

            //if (preg_match('#^/images/assets#', $message->getCover())) {
            //    $picture = $message->getCover() ?
            //        $this->getRequest()->getBaseUrl() .
            //        $message->getCover() : null;
            //} else {
            //    $picture = $message->getCover() ?
            //        $this->getRequest()->getBaseUrl() .
            //        Application_Model_Application::getImagePath() .
            //        $message->getCover() : null;
            //}

            //$action_value = null;
            //$url = null;
//
//
            //if (is_numeric($message->getActionValue())) {
            //    $option_value = new Application_Model_Option_Value();
            //    $option_value->find($message->getActionValue());
//
            //    $mobileUri = $option_value->getMobileUri();
            //    if (preg_match("/^goto\/feature/", $mobileUri)) {
            //        $action_value = sprintf("/%s/%s/value_id/%s",
            //            $application->getKey(),
            //            $mobileUri,
            //            $option_value->getId());
            //    } else {
            //        $action_value = sprintf("/%s/%sindex/value_id/%s",
            //            $application->getKey(),
            //            $option_value->getMobileUri(),
            //            $option_value->getId());
            //    }
            //} else {
            //    $url = $message->getActionValue();
            //}

            if ($this->getApplication()->getIcon(74)) {
                $icon = $this->getRequest()->getBaseUrl() . $this->getApplication()->getIcon(74);
            } else {
                $icon = null;
            }

            $payload['collection'][] = [
                'id' => (integer)$message->getId(),
                'deliver_id' => (integer)$message->getExternalId(),
                'author' => $message->getTitle(),
                'message' => $message->getBody(),
                'is_individual' => $message->getIsIndividual(),
                //'topic' => $message->getLabel(),
                'topic' => null,
                'details' => $meta,
                'picture' => $message->getBigPicture(),
                'icon' => $icon,
                'action_value' => null,
                'url' => null
            ];
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
        $payload['displayed_per_page'] = 100;
        $payload['settings'] = $settings;

        $this->_sendJson($payload, true);
    }

    public function getSampleAction()
    {
        $application = $this->getApplication();
        $image = "/app/sae/modules/Job/features/job/assets/templates/l1/img/job-header.png";

        $icon = $application::getBaseImagePath() . $application->getData('icon');
        $icon = Image::open($icon);
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
            $date = date($year . '-' . $month . '-d H:i:s');
            $collection[] = [
                'id' => (integer)$i,
                'deliver_id' => 'sample',
                'author' => ($i % 2 === 0) ? p__('push', 'John DOE') : p__('push', 'Jane DOE'),
                'message' => p__('push2', 'This a sample push message, this sample is only available from the application overview. Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.'),
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
            'page_title' => $this->getCurrentOptionValue()->getTabbarName(),
            'collection' => $collection
        ]);
    }
}

// @important!
class_alias(ListController::class, 'Push2_Mobile_ListController');