<?php

class Application_PrivacypolicyController extends Application_Controller_Default {

    /**
     * @var array
     */
    public $cache_triggers = array(
        "index" => array(
            "tags" => array("app_#APP_ID#"),
        ),
    );

    public function indexAction() {
        $this->loadPartials();

        if($this->getRequest()->getParam("id")) {
            $application_model = new Application_Model_Application();
            $application = $application_model->find($this->getRequest()->getParam("id"), "key");
            $this->view->privacy_policy = str_replace("#APP_NAME", $application->getName(), $application->getPrivacyPolicy());

            $layout = $this->getLayout();
            $content_partial = $layout->getPartial("content");
            $this->assignVars($content_partial);

        } else {
            $this->forward("list", "application", "admin");
        }

    }

}
