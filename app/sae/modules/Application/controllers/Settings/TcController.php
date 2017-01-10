<?php

class Application_Settings_TcController extends Application_Controller_Default {

    public function indexAction() {
        $this->loadPartials();
    }

    public function saveAction() {

        if($data = $this->getRequest()->getPost()) {

            try {

                $text = !empty($data["text"]) ? $data["text"] : null;
                $type = !empty($data["type"]) ? $data["type"] : null;
                
                $tc = new Application_Model_Tc();
                $tc->findByType($this->getApplication()->getId(), $type);

                if(!$tc->getId()) {
                    $tc->setAppId($this->getApplication()->getId())
                        ->setType($type)
                    ;
                }

                $tc->setText($text)
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

    public function saveprivacypolicyAction() {

        if($data = $this->getRequest()->getPost()) {

            try {

                $this->getApplication()->setPrivacyPolicy($data["privacy_policy"])->save();

                $html = array(
                    'success' => '1',
                    'success_message' => __('Privacy policy successfully saved'),
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
