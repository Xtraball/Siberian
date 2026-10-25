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

        // Accepts all templates
        if (preg_match("/(_template)/", $this->getFullActionName("_"))) {
            return $this;
        }

        // Accepts all if installing
        if ($this->getRequest()->isInstalling()) {
            return $this;
        }

        // Accepts all if logged in
        if ($this->getSession(Core_Model_Session::TYPE_BACKOFFICE)->isLoggedIn()) {
            return $this;
        }

        // Accepts all whitelist routes no matter what
        if (in_array($this->getFullActionName("_"), $allowed)) {
            return $this;
        }

        // Otherwise redirect to login
        $this->forward('login', 'account', 'backoffice');
        return $this;
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
     * The JSON body of the request as a non-empty array. When there is none (empty body, `{}`, invalid JSON) the
     * request is answered here with an error and null is returned, so an action never ends without a response
     * (it used to fall through to a missing view script and answer 404).
     *
     * @return array|null
     */
    protected function _jsonBody()
    {
        try {
            $data = Zend_Json::decode($this->getRequest()->getRawBody());
        } catch (\Exception $e) {
            $data = null;
        }

        if (!is_array($data) || empty($data)) {
            $this->_sendJson([
                'error' => 1,
                'message' => __('The request contains no data.'),
            ]);

            return null;
        }

        return $data;
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
