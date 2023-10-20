<?php

use Siberian\Account;
use Siberian\Hook;
use Siberian\Json;
use Siberian\Layout;
use Siberian\Exception;

/**
 * Class Customer_Mobile_Account_RegisterController
 */
class Customer_Mobile_Account_RegisterController extends Application_Controller_Mobile_Default
{
    public function postMcommerceAction ()
    {
        $application = $this->getApplication();
        $appId = $application->getId();
        $request = $this->getRequest();
        $session = $this->getSession();

        try {
            $data = $request->getBodyParams();

            Hook::trigger('mobile.register', [
                'appId' => $application->getId(),
                'request' => $request
            ]);

            $customer = new Customer_Model_Customer();

            $requiredFields = [];

            if (empty($data['firstname'])) {
                $requiredFields[] = p__('customer', 'Firstname');
            }

            if (empty($data['lastname'])) {
                $requiredFields[] = p__('customer', 'Lastname');
            }

            if (empty($data['email'])) {
                $requiredFields[] = p__('customer', 'E-mail');
            } else if (!Zend_Validate::is($data['email'], 'EmailAddress')) {
                $requiredFields[] = p__('customer', 'Invalid e-mail');
            }

            if (empty($data['password'])) {
                $requiredFields[] = p__('customer', 'Password');
            }

            if (empty($data['privacy_policy'])) {
                $requiredFields[] = p__('customer', 'Privacy policy');
            }

            // Throwing all errors at once!
            if (count($requiredFields) > 0) {
                $message = p__('customer', 'The following fields are required') . ':<br />- ' .
                    implode_polyfill('<br />- ', $requiredFields);

                throw new Exception($message);
            }

            $dummy = new Customer_Model_Customer();
            $dummy->find([
                'email' => $data['email'],
                'app_id' => $appId,
                'is_deleted' => 0
            ]);

            if ($dummy->getId()) {
                throw new Exception(p__('customer', 'This e-mail address is already in use, maybe you want to retrieve your password?'));
            }

            // Options
            $data['show_in_social_gaming'] = 0;
            $data['communication_agreement'] = filter_var($data['communication_agreement'], FILTER_VALIDATE_BOOLEAN);

            $customer
                ->setData($data)
                ->setAppId($appId)
                ->setPassword($data['password'])
                ->save();

            $customer->updateSessionUuid(Zend_Session::getId());

            $session->setCustomer($customer);

            $currentCustomer = Customer_Model_Customer::getCurrent();
            // Extended fields!
            $currentCustomer['extendedFields'] = Account::getFields([
                'application' => $application,
                'request' => $request,
                'session' => $session,
            ]);

            $payload = [
                'success' => true,
                'message' => p__('customer', 'Thanks for your registration!'),
                'customer_id' => $customer->getId(),
                'can_access_locked_features' => false,
                'token' => Zend_Session::getId(),
                'customer' => $currentCustomer
            ];

            Hook::trigger('mobile.register.success', [
                'appId' => $appId,
                'customerId' => $customer->getId(),
                'customer' => $currentCustomer,
                'token' => Zend_Session::getId(),
                'request' => $request,
            ]);

        } catch (\Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage()
            ];

            Hook::trigger('mobile.register.error', [
                'appId' => $appId,
                'message' => $e->getMessage(),
                'type' => 'account',
                'request' => $request,
            ]);
        }

        $this->_sendJson($payload);
    }

    /**
     * @throws Zend_Controller_Response_Exception
     * @throws Zend_Exception
     * @throws Zend_Session_Exception
     */
    public function postAction()
    {
        $application = $this->getApplication();
        $appId = $application->getId();
        $request = $this->getRequest();
        $session = $this->getSession();

        try {
            $data = $request->getBodyParams();

            // This is a M-commerce request (maybe), must be moved later outside here.
            if (stripos($data['email'], '@guest.com') !== false) {
                // Get out of my way!
                return $this->postMcommerceAction();
            }

            Hook::trigger('mobile.register', [
                'appId' => $application->getId(),
                'request' => $request
            ]);

            $customer = new Customer_Model_Customer();

            $requiredFields = [];

            // Check civility & mobile extra fields (and ensure app has a customer account ...)
            $application->checkCustomerAccount();
            $myAccountTab = $application->getOption('tabbar_account');
            $accountSettings = Json::decode($myAccountTab->getSettings());
            $emailValidation = $accountSettings['email_validation'] ?? false;
            $requireMobile = $accountSettings['extra_mobile_required'] ?? false;
            $requireCivility = $accountSettings['extra_civility_required'] ?? false;
            $requireBirthdate = $accountSettings['extra_birthdate_required'] ?? false;
            $requireNickname = $accountSettings['extra_nickname_required'] ?? false;

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

            if (empty($data['email'])) {
                $requiredFields[] = p__('customer', 'E-mail');
            } else if (!Zend_Validate::is($data['email'], 'EmailAddress')) {
                $requiredFields[] = p__('customer', 'Invalid e-mail');
            }

            if (empty($data['password'])) {
                $requiredFields[] = p__('customer', 'Password');
            }

            if (empty($data['privacy_policy'])) {
                $requiredFields[] = p__('customer', 'Privacy policy');
            }

            // Throwing all errors at once!
            if (count($requiredFields) > 0) {
                $message = p__('customer', 'The following fields are required') . ':<br />- ' .
                    implode_polyfill('<br />- ', $requiredFields);

                throw new Exception($message);
            }

            if ($useBirthdate &&
                isset($data['birthdate']) &&
                !empty($data['birthdate'])) {
                try {
                    $birthdate = new Zend_Date();
                    $birthdate->setDate($data['birthdate'], 'DD/MM/YYYY');
                    $data['birthdate'] = $birthdate->getTimestamp();
                } catch (\Exception $e) {
                    throw new Exception(p__('customer', 'Invalid birthdate.'));
                }
            }

            if (!empty($data['nickname'])) {
                $validFormat = preg_match('/^[\w]{6,30}$/', $data['nickname']);
                if (!$validFormat) {
                    throw new Exception(p__('customer', 'The nickname must contains only letters, numbers & underscore and be 6 to 30 characters long.'));
                }

                $dummy = (new Customer_Model_Customer())->find([
                    'nickname' => $data['nickname'],
                    'app_id' => $appId,
                    'is_deleted' => 0
                ]);

                if ($dummy &&
                    $dummy->getId() &&
                    $dummy->getId() !== $customer->getId()) {
                    throw new Exception(p__('customer', 'This nickname is already used, please choose another one!'));
                }
            }

            $dummy = new Customer_Model_Customer();
            $dummy->find([
                'email' => $data['email'],
                'app_id' => $appId,
                'is_deleted' => 0
            ]);

            if ($dummy->getId()) {
                throw new Exception(p__('customer', 'This e-mail address is already in use, maybe you want to retrieve your password?'));
            }

            // Options
            if (empty($data['show_in_social_gaming'])) {
                $data['show_in_social_gaming'] = 0;
            }

            $data['communication_agreement'] = filter_var($data['communication_agreement'], FILTER_VALIDATE_BOOLEAN);

            $customer
                ->setData($data)
                ->setAppId($appId)
                ->setPassword($data['password'])
                ->save();

            // In case there is an image!
            $customer->saveImage($data['image'] ?? null);
            $customer->updateSessionUuid(Zend_Session::getId());

            // PUSH INDIVIDUAL TO USER ONLY
            Customer_Model_Customer_Push::registerForIndividualPush(
                $customer,
                $this->getApplication(),
                $data['device_uid']);

            $session->setCustomer($customer);

            $this->_sendNewAccountEmail($customer, $data['password']);

            $currentCustomer = Customer_Model_Customer::getCurrent();
            // Extended fields!
            $currentCustomer['extendedFields'] = Account::getFields([
                'application' => $application,
                'request' => $request,
                'session' => $session,
            ]);

            $payload = [
                'success' => true,
                'message' => p__('customer', 'Thanks for your registration!'),
                'customer_id' => $customer->getId(),
                'can_access_locked_features' => $customer->canAccessLockedFeatures(),
                'token' => Zend_Session::getId(),
                'customer' => $currentCustomer
            ];

            Hook::trigger('mobile.register.success', [
                'appId' => $appId,
                'customerId' => $customer->getId(),
                'customer' => $currentCustomer,
                'token' => Zend_Session::getId(),
                'request' => $request,
            ]);

        } catch (\Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage()
            ];

            Hook::trigger('mobile.register.error', [
                'appId' => $appId,
                'message' => $e->getMessage(),
                'type' => 'account',
                'request' => $request,
            ]);
        }

        $this->_sendJson($payload);
    }

    /**
     * @param $customer
     * @param $password
     */
    protected function _sendNewAccountEmail($customer, $password)
    {
        try {
            // E-Mail back the user!
            $application = $this->getApplication();
            $applicationName = $application->getName();

            $subject = __('%s - Account creation', $applicationName);

            $adminEmail = null;
            $contact = new Contact_Model_Contact();
            $contactPage = $application->getPage('contact');

            if ($contactPage &&
                $contactPage->getId()) {
                $contact->find($contactPage->getId(), 'value_id');
                $adminEmail = $contact->getEmail();
            }

            $baseEmail = $this->baseEmail('create_account', $subject, '', false);

            $data = [
                'customer' => $customer,
                'password' => $password,
                'admin_email' => $adminEmail,
                'app' => $applicationName,
            ];
            foreach ($data as $key => $value) {
                $baseEmail->setContentFor('content_email', $key, $value);
            }

            $content = $baseEmail->render();

            $mail = new \Siberian_Mail();
            $mail->setBodyHtml($content);
            $mail->addTo($customer->getEmail(), $customer->getName());
            $mail->setSubject($subject);
            $mail->send();
        } catch (\Exception $e) {
            // Something went wrong with the-mail!
        }
    }

    /**
     * @param $nodeName
     * @param $title
     * @param $message
     * @param $showLegals
     * @return Siberian_Layout|Siberian_Layout_Email
     * @throws Zend_Layout_Exception
     */
    public function baseEmail($nodeName,
                              $title,
                              $message = '',
                              $showLegals = false)
    {
        $layout = new Layout();
        $layout = $layout->loadEmail('customer', $nodeName);
        $layout
            ->setContentFor('base', 'email_title', $title)
            ->setContentFor('content_email', 'message', $message)
            ->setContentFor('footer', 'show_legals', $showLegals);

        return $layout;
    }

}
