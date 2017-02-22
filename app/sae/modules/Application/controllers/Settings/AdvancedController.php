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

            $this->_sendHtml($html);
        }

    }

}
