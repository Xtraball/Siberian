<?php

use Fanwall\Model\Fanwall;
use Siberian\Exception;

/**
 * Class Fanwall_Mobile_HomeController
 */
class Fanwall_Mobile_HomeController extends Application_Controller_Mobile_Default
{
    /**
     *
     */
    public function loadSettingsAction ()
    {
        try {
            $optionValue = $this->getCurrentOptionValue();
            $fanWall = (new Fanwall())->find($optionValue->getId(), 'value_id');
            if (!$fanWall || !$fanWall->getId()) {
                throw new Exception(p__('fanwall', 'Something went wrong, the feature has no configuration.'));
            }

            $settings = $fanWall->buildSettings();
            $payload = [
                'success' => true,
                'settings' => $settings
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