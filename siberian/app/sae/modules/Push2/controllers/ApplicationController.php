<?php

namespace Push2;

use onesignal\client\ApiException;
use Push2\Form\Settings;
use Push2\Form\Message;
use Push2\Model\Onesignal\Player;
use Push2\Model\Onesignal\Scheduler;
use Siberian\Feature;
use Siberian\Json;
use \Application_Controller_Default as ControllerDefault;

/**
 * Class Push2\ApplicationController
 */
class ApplicationController extends ControllerDefault
{

    /**
     * @var array
     */
    public $cache_triggers = [
        'edit-settings' => [
            'tags' => [
                'homepage_app_#APP_ID#',
            ],
        ],
    ];

    public function sendMessageAction() {
        try {
            $optionValue = $this->getCurrentOptionValue();
            $application = $this->getApplication();
            $request = $this->getRequest();
            $values = $request->getPost();

            if (!$optionValue->getId()) {
                throw new \Exception(p__('push2', "This feature doesn't exists!"));
            }

            if (empty($values)) {
                throw new \Exception(p__('push2', 'Values are required!'));
            }

            // Required for the Message
            $values['app_id'] = $application->getId();

            $form = new Message(['application' => $application]);
            if ($form->isValid($values)) {

                $scheduler = new Scheduler($application);

                $message = $scheduler->buildMessageFromValues($values);
                Feature::formImageForOption(
                    $this->getCurrentOptionValue(),
                    $message,
                    $values,
                    'big_picture',
                    true
                );

                $result = $scheduler->send();

                if ($result->getErrors()) {
                    $errMessages = [];
                    foreach ($result->getErrors() as $error) {
                        $errMessages[] = $error->getMessage();
                    }

                    $payload = [
                        'warning' => true,
                        'message' => p__('push2', implode_polyfill('<br/>', $errMessages)),
                    ];
                } else {
                    $payload = [
                        'success' => true,
                        'message' => p__('push2', 'Push sent'),
                    ];
                }
            } else {
                $payload = [
                    'error' => true,
                    'message' => $form->getTextErrors(),
                    'errors' => $form->getTextErrors(true)
                ];
            }
        } catch (ApiException $e) {
            $body = Json::decode($e->getResponseBody());
            $payload = [
                'error' => true,
                'message' => "<b>[OneSignal]</b><br/>" . $body["errors"][0],
            ];
        } catch (\Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }

    public function editSettingsAction()
    {
        try {
            $optionValue = $this->getCurrentOptionValue();
            $request = $this->getRequest();
            $values = $request->getPost();

            if (!$optionValue->getId()) {
                throw new \Exception(p__('push2', "This feature doesn't exists!"));
            }

            if (empty($values)) {
                throw new \Exception(p__('push2', 'Values are required!'));
            }

            $form = new Settings();
            if ($form->isValid($values)) {

                $optionValue
                    ->setSettings(Json::encode($values))
                    ->save();

                /** Update touch date, then never expires (until next touch) */
                $optionValue
                    ->touch()
                    ->expires(-1);

                // Clear cache on save!
                $this->cache->clean(\Zend_Cache::CLEANING_MODE_MATCHING_TAG, [
                    'push',
                    'value_id_' . $optionValue->getId(),
                ]);

                $payload = [
                    'success' => true,
                    'message' => p__('push2', 'Settings saved'),
                ];
            } else {
                $payload = [
                    'error' => true,
                    'message' => $form->getTextErrors(),
                    'errors' => $form->getTextErrors(true)
                ];
            }
        } catch (\Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }

    public function importDevicesAction() {
        try {
            $request = $this->getRequest();
            $appId = $request->getParam('app_id', null);
            $application = (new \Application_Model_Application())->find($appId);

            if (!$application || !$application->getId()) {
                throw new \Exception(p__('push2', "This application doesn't exists!"));
            }

            $osAppId = $application->getOnesignalAppId();
            $osAppKeyToken = $application->getOnesignalAppKeyToken();
            if (empty($osAppId) || empty($osAppKeyToken)) {
                throw new \Exception(p__('push2', "This application doesn't have OneSignal configured!"));
            }

            $db = \Zend_Registry::get("db");
            $androidDevices = $db->select()
                ->from('push_gcm_devices', ['app_id', 'app_name', 'customer_id', 'device_uid', 'registration_id'])
                ->where('app_id = ?', $application->getId())
                ->query()
                ->fetchAll();

            $iosDevices = $db->select()
                ->from('push_apns_devices', ['app_id', 'app_name', 'customer_id', 'device_uid', 'device_token', 'device_name', 'device_model', 'device_version'])
                ->where('app_id = ?', $application->getId())
                ->query()
                ->fetchAll();

            $scheduler = new Scheduler($application);
            $counter = $scheduler->importDevices($androidDevices, $iosDevices);

            $payload = [
                'success' => true,
                'message' => p__('push2', sprintf('%d device%s imported and/or updated', $counter, $counter > 1 ? 's' : '')),
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

// @important! controller auto-routing requires old-style class_name
class_alias(ApplicationController::class, 'Push2_ApplicationController');
