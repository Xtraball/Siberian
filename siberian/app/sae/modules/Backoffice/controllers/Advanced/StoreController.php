<?php

use Siberian\ModuleUpdates\Exception;

/**
 * Class Backoffice_Advanced_StoreController
 *
 * Settings > Advanced > Store account: the admin connects their account on extensions.siberiancms.com and
 * consults their purchases and license keys. Read only, see Siberian\StoreAccount.
 */
class Backoffice_Advanced_StoreController extends Backoffice_Controller_Default
{
    public function loadAction()
    {
        $this->_sendJson([
            'title' => sprintf('%s > %s > %s',
                __('Settings'),
                __('Advanced'),
                __('Store account')),
            'icon' => 'fa-shopping-cart',
        ]);
    }

    /**
     * @return \Siberian\StoreAccount
     */
    private function service()
    {
        return Backoffice_Model_StoreAccount::service((string) $this->getRequest()->getHttpHost());
    }

    /**
     * Connection state, plus the code to show while a connection is waiting for approval.
     */
    public function statusAction()
    {
        try {
            $this->_sendJson([
                'success' => true,
                // The linked site of a module license is generally the Siberian license key of this install.
                'this_install_key' => strtolower(trim((string) __get('siberiancms_key'))),
            ] + $this->service()->status());
        } catch (Exception $e) {
            $this->_sendJson(Backoffice_Model_StoreAccount::errorPayload($e));
        }
    }

    /**
     * Starts the connection, answers with the code to approve on the store.
     */
    public function connectAction()
    {
        if (!$this->getRequest()->isPost()) {
            return $this->_sendJson(['error' => true, 'code' => 'method_not_allowed', 'message' => __('Use POST.')]);
        }

        try {
            $this->_sendJson(['success' => true, 'pending' => $this->service()->startConnect()]);
        } catch (Exception $e) {
            $this->_sendJson(Backoffice_Model_StoreAccount::errorPayload($e));
        }
    }

    /**
     * One poll of the approval, called by the page every few seconds while the code is shown.
     */
    public function pollAction()
    {
        try {
            $this->_sendJson(['success' => true] + $this->service()->poll());
        } catch (Exception $e) {
            $this->_sendJson(Backoffice_Model_StoreAccount::errorPayload($e));
        }
    }

    /**
     * The purchases. `refresh=1` asks the store now, otherwise a copy younger than a day is served.
     */
    public function purchasesAction()
    {
        try {
            $refresh = (bool) $this->getRequest()->getParam('refresh', false);
            $this->_sendJson(['success' => true] + $this->service()->purchases($refresh));
        } catch (Exception $e) {
            $this->_sendJson(Backoffice_Model_StoreAccount::errorPayload($e));
        }
    }

    public function disconnectAction()
    {
        if (!$this->getRequest()->isPost()) {
            return $this->_sendJson(['error' => true, 'code' => 'method_not_allowed', 'message' => __('Use POST.')]);
        }

        try {
            $this->_sendJson(['success' => true] + $this->service()->disconnect());
        } catch (Exception $e) {
            $this->_sendJson(Backoffice_Model_StoreAccount::errorPayload($e));
        }
    }
}
