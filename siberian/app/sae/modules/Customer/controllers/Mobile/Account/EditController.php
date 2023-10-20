<?php

use Siberian\Account;
use Siberian\Exception;
use Siberian\Hook;
use Siberian\Json;

/**
 * Class Customer_Mobile_Account_EditController
 */
class Customer_Mobile_Account_EditController extends Application_Controller_Mobile_Default
{
    /**
     * @throws Zend_Session_Exception
     * @throws \rock\sanitize\SanitizeException
     */
    public function findAction()
    {
        $customerPayload = Customer_Model_Customer::getCurrent();
        $customerPayload['extendedFields'] = Account::getFields([
            'application' => $this->getApplication(),
            'request' => $this->getRequest(),
            'session' => $this->getSession(),
        ]);

        $this->_sendJson($customerPayload);
    }

    public function postMcommerceAction ()
    {
        try {
            $request = $this->getRequest();
            $data = $request->getBodyParams();
            $session = $this->getSession();
            $customer = $session->getCustomer();

            if (!$customer->getId()) {
                throw new Exception(p__('customer', "The profile you are trying to edit doesn't exists!"));
            }

            if (!Zend_Validate::is($data['email'], 'EmailAddress')) {
                throw new Exception(p__('customer', 'The e-mail you used is not valid!'));
            }

            // Clear email, MUST not be changed
            unset($data['email']);

            if (empty($data['firstname'])) {
                $requiredFields[] = p__('customer', 'Firstname');
            }

            if (empty($data['lastname'])) {
                $requiredFields[] = p__('customer', 'Lastname');
            }

            $customer->setData($data);
            if (!empty($data['metadatas'])) {
                $customer->setMetadatas($data['metadatas']);
            }

            $customer->save();
            $currentCustomer = Customer_Model_Customer::getCurrent();

            $payload = [
                'success' => true,
                'message' => p__('customer', 'Settings saved!'),
                'customer' => $currentCustomer
            ];

        } catch (\Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }

        $this->_sendJson($payload);
    }

    /**
     *
     */
    public function postAction()
    {
        try {
            $request = $this->getRequest();
            $data = $request->getBodyParams();
            $session = $this->getSession();
            $customer = $session->getCustomer();
            $application = $this->getApplication();
            $appId = $application->getId();

            // This is a M-commerce request (maybe), must be moved later outside here.
            if (!array_key_exists('extendedFieldsPristine', $data) &&
                !array_key_exists('id', $data)
            ) {
                // Get out of my way!
                return $this->postMcommerceAction();
            }

            if (!$customer->getId()) {
                throw new Exception(p__('customer', "The profile you are trying to edit doesn't exists!"));
            }

            if (!Zend_Validate::is($data['email'], 'EmailAddress')) {
                throw new Exception(p__('customer', 'The e-mail you used is not valid!'));
            }

            $dummy = new Customer_Model_Customer();
            $dummy->find([
                'email' => $data['email'],
                'app_id' => $appId
            ]);

            if ($dummy->getId() &&
                $dummy->getId() !== $customer->getId()) {
                throw new Exception(p__('customer', 'This e-mail address is already in use, maybe you want to retrieve your password?'));
            }

            if (!empty($data['nickname'])) {
                $validFormat = preg_match('/^[\w]{6,30}$/', $data['nickname']);
                if (!$validFormat) {
                    throw new Exception(p__('customer', 'The nickname must contains only letters, numbers & underscore and be 6 to 30 characters long.'));
                }

                $dummy = (new Customer_Model_Customer())->find([
                    'nickname' => $data['nickname'],
                    'app_id' => $appId
                ]);

                if ($dummy &&
                    $dummy->getId() &&
                    $dummy->getId() !== $customer->getId()) {
                    throw new Exception(p__('customer', 'This nickname is already used, please choose another one!'));
                }
            }

            if (empty($data['show_in_social_gaming'])) {
                $data['show_in_social_gaming'] = 0;
            }

            // Clear ID's
            if (isset($data['id'])) {
                unset($data['id']);
            }
            if (isset($data['customer_id'])) {
                unset($data['customer_id']);
            }

            // Clear email, must not be changed
            unset($data['email']);

            // Check against required fields
            $requiredFields = [];

            // Check civility & mobile extra fields (and ensure app has a customer account ...)
            $application->checkCustomerAccount();
            $myAccountTab = $application->getOption('tabbar_account');
            $accountSettings = Json::decode($myAccountTab->getSettings());
            $requireMobile = $accountSettings['extra_mobile_required'];
            $requireCivility = $accountSettings['extra_civility_required'];
            $requireBirthdate = $accountSettings['extra_birthdate_required'];
            $requireNickname = $accountSettings['extra_nickname_required'];

            // Adds check for modules extras*
            $useNickname = $accountSettings['extra_nickname'] ?? false;
            $useBirthdate = $accountSettings['extra_birthdate'] ?? false;
            $useCivility = $accountSettings['extra_civility'] ?? false;
            $useMobile = $accountSettings['extra_mobile'] ?? false;

            foreach ($application->getOptions() as $feature) {

                if ($feature->getUseNickname()) {
                    $useNickname = true;
                    $requireNickname = true;
                }
                if ($feature->getUseBirthdate()) {
                    $useBirthdate = true;
                    $requireBirthdate = true;
                }
                if ($feature->getUseCivility()) {
                    $useCivility = true;
                    $requireCivility = true;
                }
                if ($feature->getUseMobile()) {
                    $useMobile = true;
                    $requireMobile = true;
                }

                // All are true, we can abort here!
                if ($requireNickname &&
                    $requireBirthdate &&
                    $requireCivility &&
                    $requireMobile) {
                    break;
                }
            }

            if ($requireMobile && empty($data['mobile'])) {
                $requiredFields[] = p__('customer', 'Mobile');
            }

            if ($requireCivility && empty($data['civility'])) {
                $requiredFields[] = p__('customer', 'Civility');
            }

            if ($requireBirthdate && empty($data['birthdate'])) {
                $requiredFields[] = p__('customer', 'Birthdate');
            }

            if ($requireNickname && empty($data['nickname'])) {
                $requiredFields[] = p__('customer', 'Nickname');
            }

            if (empty($data['firstname'])) {
                $requiredFields[] = p__('customer', 'Firstname');
            }

            if (empty($data['lastname'])) {
                $requiredFields[] = p__('customer', 'Lastname');
            }

            // Throwing all errors at once!
            if (count($requiredFields) > 0) {
                $message = p__('customer', 'The following fields are required') . ':<br />- ' .
                    implode_polyfill('<br />- ', $requiredFields);

                throw new Exception($message);
            }
            // Check against required fields

            if ($useBirthdate &&
                isset($data['birthdate']) &&
                !empty($data['birthdate'])) {
                try {
                    $birthdate = new Zend_Date();
                    $birthdate->setDate($data['birthdate'], 'dd/MM/y');
                    $data['birthdate'] = $birthdate->getTimestamp();
                } catch (\Exception $e) {
                    throw new Exception(p__('customer', 'Invalid birthdate.'));
                }
            }

            $customer->saveImage($data['image']);
            unset($data['image']);

            $password = '';
            $data['change_password'] = filter_var($data['change_password'], FILTER_VALIDATE_BOOLEAN);
            if ($data['change_password'] === true &&
                !empty($data['password'])) {

                if (empty($data['old_password']) ||
                    (!empty($data['old_password']) &&
                        !$customer->isSamePassword($data['old_password']))) {
                    throw new Exception(p__('customer', 'The current password is incorrect.'));
                }

                $password = $data['password'];
            }

            $customer->setData($data);
            if (!empty($password)) {
                $customer->setPassword($password);
                Hook::trigger('mobile.customer.changePassword.success', [
                    'appId' => $this->getApplication()->getId(),
                    'customerId' => $customer->getId(),
                    'customer' => $customer,
                    'newPassword' => $password,
                    'token' => Zend_Session::getId(),
                    'type' => 'account'
                ]);
            }
            if (!empty($data['metadatas'])) {
                $customer->setMetadatas($data['metadatas']);
            }

            // New mobile account hooks/forms
            if (array_key_exists('extendedFields', $data)) {
                Account::saveFields([
                    'application' => $this->getApplication(),
                    'request' => $this->getRequest(),
                    'session' => $this->getSession(),
                ], $data['extendedFields']);
            }

            $customer->save();

            $currentCustomer = Customer_Model_Customer::getCurrent();
            $currentCustomer['extendedFields'] = Account::getFields([
                'application' => $this->getApplication(),
                'request' => $this->getRequest(),
                'session' => $this->getSession(),
            ]);

            $payload = [
                'success' => true,
                'message' => p__('customer', 'Settings saved!'),
                'customer' => $currentCustomer
            ];

        } catch (\Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }

        $this->_sendJson($payload);
    }

    public function sendTestPushAction()
    {
        /**try {
            $request = $this->getRequest();
            $data = $request->getBodyParams();

            if (empty($data)) {
                throw new Exception(p__('customer', 'Missing data!'));
            }

            $tokens = [
                $data['deviceToken']
            ];

            // Adds a little delay!
            sleep(1);

            $push = \Push\Model\StandalonePush::buildFromTokens($tokens);
            $push->sendMessage(
                p__('customer', 'Test push'),
                p__('customer', 'This is a test push!'),
                '',
                null,
                null,
                null,
                false
            );

            $payload = [
                'success' => true,
                'message' => p__('customer', 'Test push sent!'),
            ];
        } catch (\Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }*/

        $payload = [
            'success' => true,
            'message' => p__('customer', 'Disabled!'),
        ];

        $this->_sendJson($payload);
    }

    public function saveSettingsAction()
    {
        // @todo maybe use, or not, or replace with onesignal settings

        $payload = [
            'success' => true,
            'message' => p__('customer', 'Settings saved!'),
        ];

        $this->_sendJson($payload);
    }

}
