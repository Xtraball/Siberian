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
