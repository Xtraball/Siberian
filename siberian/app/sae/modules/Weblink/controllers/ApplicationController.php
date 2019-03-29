<?php

use Siberian\Json;

/**
 * Class Weblink_ApplicationController
 */
class Weblink_ApplicationController extends Application_Controller_Default
{

    /**
     * @var array
     */
    public $cache_triggers = [
        "edit-settings" => [
            "tags" => [
                "homepage_app_#APP_ID#",
            ],
        ],
    ];

    /**
     *
     */
    public function editSettingsAction()
    {
        $request = $this->getRequest();
        $params = $request->getPost();

        $form = new Weblink_Form_Settings();
        try {
            if ($form->isValid($params)) {
                // Do whatever you need when form is valid!
                $optionValue = $this->getCurrentOptionValue();

                $filteredValues = $form->getValues();

                $filteredValues["showSearch"] = filter_var($filteredValues["showSearch"], FILTER_VALIDATE_BOOLEAN);
                $filteredValues["cardDesign"] = filter_var($filteredValues["cardDesign"], FILTER_VALIDATE_BOOLEAN);

                $optionValue
                    ->setSettings(Json::encode($filteredValues))
                    ->save();

                /** Update touch date, then never expires (until next touch) */
                $this->getCurrentOptionValue()
                    ->touch()
                    ->expires(-1);

                $payload = [
                    'success' => true,
                    'message' => __('Success.'),
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