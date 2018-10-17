<?php

class Application_Settings_TcController extends Application_Controller_Default
{

    /**
     * @var array
     */
    public $cache_triggers = [
        "saveprivacypolicy" => [
            "tags" => [
                "app_#APP_ID#",
                "feature_privacypolicy"
            ],
        ],
    ];

    public function indexAction()
    {
        $this->loadPartials();
    }

    public function saveAction()
    {

        if ($datas = $this->getRequest()->getPost()) {

            try {

                $text = !empty($datas["text"]) ? $datas["text"] : null;
                $type = !empty($datas["type"]) ? $datas["type"] : null;

                $tc = new Application_Model_Tc();
                $tc->findByType($this->getApplication()->getId(), $type);

                if (!$tc->getId()) {
                    $tc->setAppId($this->getApplication()->getId())
                        ->setType($type);
                }

                $tc->setText($text)
                    ->save();

                $data = [
                    "success" => true,
                    "success_message" => __("Info successfully saved"),
                    "message_timeout" => 2,
                    "message_button" => 0,
                    "message_loader" => 0
                ];

            } catch (Exception $e) {
                $data = [
                    "error" => true,
                    "message" => $e->getMessage()
                ];
            }

            $this->_sendJson($data);

        }

    }

    public function saveprivacypolicyAction()
    {

        if ($datas = $this->getRequest()->getPost()) {

            try {

                $this->getApplication()
                    ->setPrivacyPolicy($datas['privacy_policy'])
                    ->setPrivacyPolicyGdpr($datas['privacy_policy_gdpr'])
                    ->save();

                $data = [
                    "success" => true,
                    "success_message" => __("Privacy policy successfully saved"),
                    "message_timeout" => 2,
                    "message_button" => 0,
                    "message_loader" => 0
                ];

            } catch (Exception $e) {
                $data = [
                    "error" => true,
                    "message" => $e->getMessage()
                ];
            }

            $this->_sendJson($data);

        }
    }

}
