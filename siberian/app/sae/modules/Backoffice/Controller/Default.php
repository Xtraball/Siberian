<?php

/**
 * Class Backoffice_Controller_Default
 */
class Backoffice_Controller_Default extends Core_Controller_Default
{
    /**
     * @return $this|void
     */
    public function init()
    {
        parent::init();

        Siberian_Cache_Translation::init();

        $allowed = [
            'backoffice_index_index',
            'backoffice_account_login_index',
            'backoffice_account_login_post',
            'backoffice_account_login_forgottenpassword',
            'application_backoffice_iosautopublish_updatejobstatus', //used by jenkins/fastlane to update job status
            'application_backoffice_iosautopublish_uploadapk', //used by jenkins/fastlane to update job status
            'application_backoffice_iosautopublish_apkservicestatus', //used by jenkins/fastlane to update job status
            'application_backoffice_iosautopublish_uploadcertificate', //used by jenkins/fastlane to update job status
            'installer_module_getfeature',
            'backoffice_advanced_tools_testbasicauth',
            'backoffice_advanced_tools_testbearerauth'
        ];

        if (!$this->getSession(Core_Model_Session::TYPE_BACKOFFICE)->isLoggedIn()
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

    /**
     *
     */
    public function indexAction()
    {
        $this->forward('index', 'index', 'Backoffice', $this->getRequest()->getParams());
    }

    /**
     *
     */
    public function templateAction()
    {
        $this->loadPartials(null, false);
    }

    /**
     * On every request append cool informations
     *
     * @param $payload
     * @return mixed
     * @deprecated use _sendJson($payload) instead
     */
    protected function _sendHtml($payload)
    {
        return $this->_sendJson($payload);
    }

    /**
     * Payload wrapper for backoffice to send more informations on each request!
     *
     * @param $payload
     */
    public function _sendJson($payload, $options = JSON_PRETTY_PRINT)
    {
        $notifs_model = new Backoffice_Model_Notification();
        $unread = $notifs_model->countUnread();

        $is_numeric = true;
        foreach ($payload as $a => $b) {
            if (!is_int($a)) {
                $is_numeric = false;
            }
        }

        if (!$is_numeric) {
            $payload['meta'] = [
                'unread_messages' => $unread,
            ];
        }

        return parent::_sendJson($payload, $options);
    }

}
