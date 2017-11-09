<?php

class Application_Settings_TcController extends Application_Controller_Default {

    /**
     * @var array
     */
    public $cache_triggers = array(
        "saveprivacypolicy" => array(
            "tags" => array(
                "app_#APP_ID#",
                "feature_privacypolicy"
            ),
        ),
    );

    public function indexAction() {
        $this->loadPartials();
    }

    public function saveAction() {

        if($datas = $this->getRequest()->getPost()) {

            try {

                $text = !empty($datas["text"]) ? $datas["text"] : null;
                $type = !empty($datas["type"]) ? $datas["type"] : null;
                
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

                $data = array(
                    "success"           => true,
                    "success_message"   => __("Info successfully saved"),
                    "message_timeout"   => 2,
                    "message_button"    => 0,
                    "message_loader"    => 0
                );

            }
            catch(Exception $e) {
                $data = array(
                    "error"     => true,
                    "message"   => $e->getMessage()
                );
            }

            $this->_sendJson($data);

        }

    }

    public function saveprivacypolicyAction() {

        if($datas = $this->getRequest()->getPost()) {

            try {

                $this->getApplication()->setPrivacyPolicy($datas["privacy_policy"])->save();

                $data = array(
                    "success"           => true,
                    "success_message"   => __("Privacy policy successfully saved"),
                    "message_timeout"   => 2,
                    "message_button"    => 0,
                    "message_loader"    => 0
                );

            }
            catch(Exception $e) {
                $data = array(
                    "error"     => true,
                    "message"   => $e->getMessage()
                );
            }

            $this->_sendJson($data);

        }
    }

}
