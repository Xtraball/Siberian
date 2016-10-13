<?php

class Application_Settings_AdvancedController extends Application_Controller_Default {

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
