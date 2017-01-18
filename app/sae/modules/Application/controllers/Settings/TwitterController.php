<?php

class Application_Settings_TwitterController extends Application_Controller_Default {

    public function indexAction() {
        $this->loadPartials();
    }

    public function saveAction() {

        if($data = $this->getRequest()->getPost()) {

            try {

                $application = $this->getApplication();

                $application
                    ->setTwitterConsumerKey($data["twitter_consumer_key"])
                    ->setTwitterConsumerSecret($data["twitter_consumer_secret"])
                    ->setTwitterApiToken($data["twitter_api_token"])
                    ->setTwitterApiSecret($data["twitter_api_secret"])
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
