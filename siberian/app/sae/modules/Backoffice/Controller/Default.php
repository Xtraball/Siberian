<?php

use Siberian\Security;

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

        // Guest routes (doesn't require active auth)
        $allowed = Security::$routesGuest;

        if (!$this->getSession(Core_Model_Session::TYPE_BACKOFFICE)->isLoggedIn()
            // Allowed for a few URLs
            && !in_array($this->getFullActionName("_"), $allowed)
            // Forbidden when Siberian is not installed
            && !$this->getRequest()->isInstalling()
            // Forbidden fot the non XHR requests
            && !$this->getRequest()->isXmlHttpRequest()
            // Allowed for the templates
            && !preg_match("/(_template)/", $this->getFullActionName("_"))
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
