<?php

class Application_Settings_FacebookController extends Application_Controller_Default {

    public function indexAction() {
        $this->loadPartials();
    }

    public function saveAction() {

        if($data = $this->getRequest()->getPost()) {

            try {

                $facebook_id = !empty($data['facebook_id']) ? $data['facebook_id'] : null;
                $facebook_key = !empty($data['facebook_key']) ? $data['facebook_key'] : null;
                
                $this->getApplication()
                     ->setFacebookId($facebook_id)
                     ->setFacebookKey($facebook_key)
                     ->save()
                ;

                $html = array(
                    'success' => '1',
                    'success_message' => __('Info successfully saved'),
                    'message_timeout' => 2,
                    'message_button' => 0,
                    'message_loader' => 0
                );

            }
            catch(Exception $e) {
                $html = array('message' => $e->getMessage());
            }

            $this->_sendHtml($html);
        }

    }

}
