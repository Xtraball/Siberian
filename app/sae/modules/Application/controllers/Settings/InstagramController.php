<?php

class Application_Settings_InstagramController extends Application_Controller_Default {

    public function indexAction() {
        $this->loadPartials();
    }

    public function saveAction() {

        if($data = $this->getRequest()->getPost()) {

            try {

                $instagram_client_id = !empty($data['instagram_client_id']) ? $data['instagram_client_id'] : null;
                $instagram_token = !empty($data['instagram_token']) ? $data['instagram_token'] : null;

                $this->getApplication()
                     ->setInstagramClientId($instagram_client_id)
                     ->setInstagramToken($instagram_token)
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
