<?php

class Application_Api_Webservice_PreviewController extends Api_Controller_Default {

    public function loginAction() {
        $this->forward("login", "webservice_preview", "application");
        return $this;
    }

}
