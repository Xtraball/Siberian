<?php

use Siberian\Hook;
use Siberian\Layout;
use Siberian\Exception;

/**
 * Class Customer_Mobile_Account_RegisterController
 */
class Customer_Mobile_Account_RegisterController extends Application_Controller_Mobile_Default
{
    /**
     * @throws Zend_Json_Exception
     */
    public function postAction()
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
                $message = p__('customer', 'The following fields are required:<br />- ') .
                    implode('<br />- ', $requiredFields);

                throw new Exception($message);
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

            $dummy = new Customer_Model_Customer();
            $dummy->find([
                'email' => $data['email'],
                'app_id' => $appId,
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
            $customer->saveImage($data['image']);

            $customer->updateSessionUuid(Zend_Session::getId());

            // PUSH INDIVIDUAL TO USER ONLY
            Customer_Model_Customer_Push::registerForIndividualPush(
                $customer,
                $this->getApplication(),
                $data['device_uid']);

            $session->setCustomer($customer);

            $this->_sendNewAccountEmail($customer, $data['password']);

            $currentCustomer = Customer_Model_Customer::getCurrent();

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
        $layout = new Siberian\Layout();
        $layout = $layout->loadEmail('customer', $nodeName);
        $layout
            ->setContentFor('base', 'email_title', $title)
            ->setContentFor('content_email', 'message', $message)
            ->setContentFor('footer', 'show_legals', $showLegals);

        return $layout;
    }

}
