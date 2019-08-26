<?php

use Fanwall\Model\Fanwall;

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
            $fanWall = (new Fanwall())->find($optionValue->getId(), "value_id");
            $settings = $fanWall->buildSettings();
            $payload = [
                "success" => true,
                "settings" => $settings
            ];
        } catch (\Exception $e) {
            $payload = [
                "error" => true,
                "message" => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }
}