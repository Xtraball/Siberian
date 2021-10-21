<?php

/**
 * Class Application_Settings_AdvancedController
 */
class Application_Settings_AdvancedController extends Application_Controller_Default
{

    /**
     * @var array
     */
    public $cache_triggers = [
        "save" => [
            "tags" => ["app_#APP_ID#"],
        ],
    ];

    /**
     *
     */
    public function indexAction()
    {
        $this->loadPartials();
    }

    /**
     * @throws Zend_Form_Exception
     */
    public function saveformAction()
    {
        $request = $this->getRequest();

        $form = new Application_Form_Smtp();
        if ($form->isValid($request->getParams())) {
            $application = $this->getApplication();

            $application
                ->setData($form->getValues())
                ->setSmtpCredentials(Siberian_Json::encode($request->getParam("smtp_credentials")))
                ->save();

            $data = [
                "success" => 1,
                "message" => __("Success."),
            ];
        } else {
            /** Do whatever you need when form is not valid */
            $data = [
                "error" => 1,
                "message" => $form->getTextErrors(),
                "errors" => $form->getTextErrors(true),
            ];
        }

        $this->_sendJson($data);
    }

    /**
     *
     */
    public function savensdescriptionAction()
    {
        try {
            $request = $this->getRequest();
            $application = $this->getApplication();
            $iosDevice = $application->getIosDevice();
            $params = $request->getParams();

            $formNsDescription = new Application_Form_NsDescription();

            if (!$formNsDescription->isValid($params)) {
                $payload = [
                    'error' => true,
                    'message' => $formNsDescription->getTextErrors(),
                    'errors' => $formNsDescription->getTextErrors(true),
                ];
            } else {
                $values = $formNsDescription->getValues();

                $iosDevice
                    ->setNsCameraUd($values['ns_camera_ud'])
                    ->setNsPhotoLibraryUd($values['ns_photo_library_ud'])
                    ->setNsLocationWhenInUseUd($values['ns_location_when_in_use_ud'])
                    ->setNsBluetoothAlwaysUd($values['ns_bluetooth_always_ud'])
                    ->setNsBluetoothPeripheralUd($values['ns_bluetooth_peripheral_ud'])
                    ->setNsLocationAlwaysUd($values['ns_location_always_ud'])
                    ->setNsLocationAlwaysAndWhenInUseUd($values['ns_location_always_and_when_in_use_ud'])
                    ->setNsMotionUd($values['ns_motion_ud'])
                    ->setNsUserTrackingUd($values['ns_user_tracking_ud'])
                    ->save();

                $application
                    ->setRequestTrackingAuthorization(filter_var($values['request_tracking_authorization'], FILTER_VALIDATE_BOOLEAN))
                    ->save();

                $payload = [
                    'success' => true,
                    'message' => __('iOS Descriptions saved!')
                ];
            }


        } catch (Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }

        $this->_sendJson($payload);
    }

    /**
     *
     */
    public function saveorientationsAction()
    {
        try {
            $request = $this->getRequest();
            $application = $this->getApplication();
            $iosDevice = $application->getDevice(1);
            $androidDevice = $application->getDevice(2);
            $params = $request->getParams();

            $defaults = [
                'iphone-portrait' => false,
                'iphone-upside-down' => false,
                'iphone-landscape-left' => false,
                'iphone-landscape-right' => false,
                'ipad-portrait' => false,
                'ipad-upside-down' => false,
                'ipad-landscape-left' => false,
                'ipad-landscape-right' => false,
                'android-portrait' => false,
                'android-upside-down' => false,
                'android-landscape-left' => false,
                'android-landscape-right' => false,
            ];

            foreach ($params['orientations'] as $key => $value) {
                if (isset($defaults[$key])) {
                    $defaults[$key] = true;
                }
            }

            if (!$defaults['android-portrait'] &&
                !$defaults['android-upside-down'] &&
                !$defaults['android-landscape-left'] &&
                !$defaults['android-landscape-right']) {
                throw new \Siberian\Exception(__('You must select at least one orientation for Android!'));
            }

            // Special android!
            $defaults['android'] = $params['android'];

            $androidValids = [
                "landscape",
                "portrait",
                "reverseLandscape",
                "reversePortrait",
                "sensorPortrait",
                "sensorLandscape",
                "fullSensor",
            ];
            if (!in_array($defaults['android'], $androidValids)) {
                throw new \Siberian\Exception(__('Android orientation is invalid!'));
            }

            if (!$defaults['iphone-portrait'] &&
                !$defaults['iphone-upside-down'] &&
                !$defaults['iphone-landscape-left'] &&
                !$defaults['iphone-landscape-right']) {
                throw new \Siberian\Exception(__('You must select at least one orientation for iPhone!'));
            }

            if (!$defaults['ipad-portrait'] &&
                !$defaults['ipad-upside-down'] &&
                !$defaults['ipad-landscape-left'] &&
                !$defaults['ipad-landscape-right']) {
                throw new \Siberian\Exception(__('You must select at least one orientation for iPad!'));
            }

            $iosDevice
                ->setOrientations(Siberian_Json::encode($defaults))
                ->save();

            $androidDevice
                ->setOrientations(Siberian_Json::encode($defaults))
                ->save();

            $payload = [
                'success' => true,
                'message' => __('Orientations saved!')
            ];
        } catch (Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }

        $this->_sendJson($payload);
    }

    /**
     *
     */
    public function saveAction()
    {

        if ($data = $this->getRequest()->getPost()) {

            try {

                $application = $this->getApplication();

                $disable_updates = ($data['disable_updates'] == 0) ? 0 : 1;
                $application
                    ->setDisableUpdates($disable_updates)
                    ->setFidelityRate($data['fidelity_rate']);

                $application->save();

                $payload = [
                    'success' => true,
                    'success_message' => __('Option successfully saved'),
                    'message_timeout' => 2,
                    'message_button' => 0,
                    'message_loader' => 0,
                ];

            } catch (Exception $e) {
                $payload = [
                    'error' => true,
                    'message' => $e->getMessage()
                ];
            }

            $this->_sendJson($payload);
        }

    }

}
