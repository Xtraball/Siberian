<?php

class Application_Settings_AdvancedController extends Application_Controller_Default {

    /**
     * @var array
     */
    public $cache_triggers = array(
        "save" => array(
            "tags" => array("app_#APP_ID#"),
        ),
    );

    public function indexAction() {
        $this->loadPartials();
    }

    public function saveformAction() {
        $request = $this->getRequest();

        $form = new Application_Form_Smtp();
        if($form->isValid($request->getParams())) {
            $application = $this->getApplication();

            $application
                ->setData($form->getValues())
                ->setSmtpCredentials(Siberian_Json::encode($request->getParam("smtp_credentials")))
                ->save()
            ;

            $data = array(
                "success" => 1,
                "message" => __("Success."),
            );
        } else {
            /** Do whatever you need when form is not valid */
            $data = array(
                "error" => 1,
                "message" => $form->getTextErrors(),
                "errors" => $form->getTextErrors(true),
            );
        }

        $this->_sendJson($data);
    }

    public function savensdescriptionAction() {
        try {
            $request = $this->getRequest();
            $application = $this->getApplication();
            $iosDevice = $application->getDevice(1);
            $params = $request->getParams();

            $formNsDescription = new Application_Form_NsDescription();

            if (!$formNsDescription->isValid($params)) {
                $payload = [
                    'error' => true,
                    'message' => $formNsDescription->getTextErrors(),
                    'errors' => $formNsDescription->getTextErrors(true),
                ];
            } else {
                $iosDevice
                    ->setData($formNsDescription->getValues())
                    ->save();

                $payload = [
                    'success' => true,
                    'message' => __('iOS Descriptions saved!')
                ];
            }


        } catch(Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }

        $this->_sendJson($payload);
    }

    public function saveorientationsAction() {
        try {
            $request = $this->getRequest();
            $application = $this->getApplication();
            $iosDevice = $application->getDevice(1);
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
                'android-portrait' => true,
                'android-upside-down' => true,
                'android-landscape-left' => true,
                'android-landscape-right' => true,
            ];

            foreach ($params['orientations'] as $key => $value) {
                if (isset($defaults[$key])) {
                    $defaults[$key] = true;
                }
            }

            if (!$defaults['iphone-portrait'] &&
                !$defaults['iphone-upside-down'] &&
                !$defaults['iphone-landscape-left'] &&
                !$defaults['iphone-landscape-right']) {
                throw new Siberian_Exception(__('You must select at least one orientation for iPhone!'));
            }

            if (!$defaults['ipad-portrait'] &&
                !$defaults['ipad-upside-down'] &&
                !$defaults['ipad-landscape-left'] &&
                !$defaults['ipad-landscape-right']) {
                throw new Siberian_Exception(__('You must select at least one orientation for iPad!'));
            }

            $iosDevice
                ->setOrientations(Siberian_Json::encode($defaults))
                ->save();

            $payload = [
                'success' => true,
                'message' => __('Orientations saved!')
            ];
        } catch(Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }

        $this->_sendJson($payload);
    }

    public function saveAction() {

        if($data = $this->getRequest()->getPost()) {

            try {

                $application = $this->getApplication();

                $value = ($data["offline_content"] == 0) ? 0 : 1;
                $application
                    ->setOfflineContent($value)
                    ->setGooglemapsKey($data["googlemaps_key"])
                    ->setFlickrKey($data["flickr_key"])
                    ->setFlickrSecret($data["flickr_secret"])
                    ->setFidelityRate($data["fidelity_rate"])
                ;

                $application->save();

                $html = array(
                    'success' => '1',
                    'success_message' => __("Option successfully saved"),
                    'message_timeout' => 2,
                    'message_button' => 0,
                    'message_loader' => 0,
                );

            }
            catch(Exception $e) {
                $html = array('message' => $e->getMessage());
            }

            $this->_sendJson($html);
        }

    }

}
