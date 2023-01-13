<?php

namespace Push2;

use Push\Form\Settings;
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

    public function editSettingsAction()
    {
        try {
            $optionValue = $this->getCurrentOptionValue();
            $request = $this->getRequest();
            $values = $request->getPost();

            if (!$optionValue->getId()) {
                throw new Exception(p__('push', "This feature doesn't exists!"));
            }

            if (empty($values)) {
                throw new Exception(p__('push', 'Values are required!'));
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
                $this->cache->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, [
                    'push',
                    'value_id_' . $optionValue->getId(),
                ]);

                $payload = [
                    'success' => true,
                    'message' => p__('push', 'Settings saved'),
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
}

// @important!
class_alias(ApplicationController::class, 'Push2_ApplicationController');
