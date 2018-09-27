<?php

class Application_PrivacypolicyController extends Application_Controller_Default
{

    /**
     * @var array
     */
    public $cache_triggers = [
        "index" => [
            "tags" => ["app_#APP_ID#"],
        ],
    ];

    public function indexAction()
    {
        $this->loadPartials();
        $request = $this->getRequest();
        $appKey = $request->getParam('id');

        if ($appKey === 'overview') {
            $application = Application_Model_Application::getInstance();
        } else {
            $application = (new Application_Model_Application())
                ->find($appKey, "key");
        }

        if ($application->getId()) {
            $this->view->privacy_policy = str_replace("#APP_NAME", $application->getName(), $application->getPrivacyPolicy());
            $this->view->privacy_policy_gdpr = str_replace("#APP_NAME", $application->getName(), $application->getPrivacyPolicyGdpr());

            $layout = $this->getLayout();
            $content_partial = $layout->getPartial("content");
            $this->assignVars($content_partial);
        } else {
            $this->forward("list", "application", "admin");
        }
    }

}
