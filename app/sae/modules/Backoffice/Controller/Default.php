<?php

class Backoffice_Controller_Default extends Core_Controller_Default {

    public function init() {
        parent::init();

        $allowed = array(
            "backoffice_index_index",
            "backoffice_account_login_index",
            "backoffice_account_login_post",
            "backoffice_account_login_forgottenpassword",
        );

        if(!$this->getSession(Core_Model_Session::TYPE_BACKOFFICE)->isLoggedIn()
            // Allowed for a few URLs
            AND !in_array($this->getFullActionName("_"), $allowed)
            // Forbidden when Siberian is not installed
            AND !$this->getRequest()->isInstalling()
            // Forbidden fot the non XHR requests
            AND !$this->getRequest()->isXmlHttpRequest()
            // Allowed for the templates
            AND !preg_match("/(_template)/", $this->getFullActionName("_"))
        ) {
            $this->forward('login', 'account', 'backoffice');
            return $this;
        }

    }


    public function indexAction() {
        $this->forward('index', 'index', 'Backoffice', $this->getRequest()->getParams());
    }

    public function templateAction() {
        $this->loadPartials(null, false);
    }

}
