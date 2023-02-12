<?php

use Siberian\Hook;
use Siberian\Json;
use Siberian\Exception;

/**
 * Class Customer_Mobile_Account_LoginController
 */
class Customer_Mobile_Account_LoginController extends Application_Controller_Mobile_Default
{
    /**
     *
     */
    public function indexAction()
    {
        if ($this->getSession()->isLoggedIn()) {
            $this->_redirect('customer/mobile_account_edit');
        } else {
            parent::indexAction();
        }
    }

    /**
     *
     */
    public function postv2Action()
    {
        try {
            $request = $this->getRequest();
            if ($params = Json::decode($request->getRawBody())) {
                //
            } else {
                throw new \Siberian\Exception(__('Missing parameters.'));
            }
        } catch (\Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }

        $this->_sendJson($payload);
    }

    /**
     * @return array
     */
    private function _getCustomer()
    {
        return Customer_Model_Customer::getCurrent();
    }

    /**
     *
     */
    public function postAction()
    {
        $application = $this->getApplication();
        $request = $this->getRequest();

        Hook::trigger('mobile.login', [
            'appId' => $application->getId(),
            'request' => $request,
            'type' => 'account'
        ]);

        try {
            if ($datas = Json::decode($request->getRawBody())) {

                if ((empty($datas['email']) || empty($datas['password']))) {
                    throw new Siberian_Exception(
                        __('Authentication failed. Please check your email and/or your password')
                    );
                }

                $customer = new Customer_Model_Customer();
                $customer->find([
                    'email' => $datas['email'],
                    'app_id' => $application->getId(),
                    'is_deleted' => 0
                ]);

                $password = $datas['password'];

                if (!$customer->getId() OR !$customer->authenticate($password)) {
                    throw new Siberian_Exception(
                        __('Authentication failed. Please check your email and/or your password')
                    );
                }

                // PUSH INDIVIDUAL TO USER ONLY
                Customer_Model_Customer_Push::registerForIndividualPush(
                    $customer,
                    $this->getApplication(),
                    $datas['device_uid']);

                if (!$customer->getAppId()) {
                    $customer->setAppId($this->getApplication()->getId())->save();
                }

                $this->getSession()
                    ->resetInstance()
                    ->setCustomer($customer);

                $currentCustomer = $this->_getCustomer();

                $customer->updateSessionUuid(Zend_Session::getId());

                $payload = [
                    'success' => true,
                    'customer_id' => $customer->getId(),
                    'can_access_locked_features' => $customer->canAccessLockedFeatures(),
                    'token' => Zend_Session::getId(),
                    'customer' => $currentCustomer
                ];

                Hook::trigger('mobile.login.success', [
                    'appId' => $application->getId(),
                    'customerId' => $customer->getId(),
                    'customer' => $currentCustomer,
                    'token' => Zend_Session::getId(),
                    'type' => 'account',
                    'request' => $request,
                ]);

            } else {
                throw new Siberian_Exception(__('An error occurred, please try again.'));
            }

        } catch (Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage()
            ];

            Hook::trigger('mobile.login.error', [
                'appId' => $application->getId(),
                'message' => $e->getMessage(),
                'type' => 'account',
                'request' => $request,
            ]);
        }

        $this->_sendJson($payload);
    }

    /**
     *
     */
    public function logoutAction()
    {
        $application = $this->getApplication();
        $appId = $application->getId();
        $request = $this->getRequest();
        $session = $this->getSession();
        $customerId = $session->getCustomerId();

        Hook::trigger('mobile.logout', [
            'appId' => $application->getId(),
            'customerId' => $customerId,
            'request' => $request
        ]);

        /** Unlink from individual push */
        /**if (Push_Model_Message::hasIndividualPush()) {

            $deviceIos = (new Push_Model_Iphone_Device())->findAll([
                'customer_id = ?' => $customerId,
                'app_id = ?' => $appId,
            ]);

            foreach ($deviceIos as $ios) {
                $ios
                    ->setCustomerId(null)
                    ->save();
            }

            $deviceAndroid = (new Push_Model_Android_Device())->findAll([
                'customer_id = ?' => $customerId,
                'app_id = ?' => $appId,
            ]);

            foreach ($deviceAndroid as $android) {
                $android
                    ->setCustomerId(null)
                    ->save();
            }
        }*/

        // Remove session_uuid from customer
        $customer = (new Customer_Model_Customer())->find($customerId);
        $customer->clearSessionUuid();

        $session->resetInstance();

        Zend_Session::destroy();

        $payload = [
            'success' => true,
            'message' => 'User logged out.',
        ];

        Hook::trigger('mobile.logout.success', [
            'appId' => $application->getId(),
            'customerId' => $customerId,
            'request' => $request
        ]);

        $this->_sendJson($payload);
    }

    public function deleteAccountAction ()
    {
        $application = $this->getApplication();
        $request = $this->getRequest();
        $session = $this->getSession();

        Hook::trigger('mobile.delete', [
            'appId' => $application->getId(),
            'request' => $request,
            'type' => 'account'
        ]);

        try {
            if (!$session->isLoggedIn()) {
                throw new Exception(p__('customer', 'You must be logged-in to delete your account.'));
            }

            $customerId = $session->getcustomerId();
            $customer = (new Customer_Model_Customer())->find($customerId);
            if (!$customer || !$customer->getId()) {
                throw new Exception(p__('customer', 'This account does not exists!'));
            }

            $email = $customer->getEmail();

            Hook::trigger('mobile.delete.success', [
                'appId' => $application->getId(),
                'customerId' => $customerId,
                'customer' => $customer->getData(),
                'token' => Zend_Session::getId(),
                'type' => 'account',
                'request' => $request,
            ]);

            // Blanking customer, keep only track of email!
            $customer
                ->setEmail(sprintf("deleted_%s_%s", time(), $email))
                ->setFirstname('-removed-')
                ->setLastname('-removed-')
                ->setNickname('-removed-')
                ->setImage(null)
                ->setSessionUuid(null)
                ->setCustomFields('{}')
                ->setIsActive(0)
                ->setIsDeleted(1)
                ->save();

            $payload = [
                'success' => true,
                'message' => p__('customer', 'Your account has been removed!')
            ];

        } catch (Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage()
            ];

            Hook::trigger('mobile.delete.error', [
                'appId' => $application->getId(),
                'message' => $e->getMessage(),
                'type' => 'account',
                'request' => $request,
            ]);
        }

        $this->_sendJson($payload);
    }
}
