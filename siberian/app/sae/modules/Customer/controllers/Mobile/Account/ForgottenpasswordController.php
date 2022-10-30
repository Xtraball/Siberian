<?php

use Siberian\Hook;
use Siberian\Exception;
use Siberian\Mail;

/**
 * Class Customer_Mobile_Account_ForgottenpasswordController
 */
class Customer_Mobile_Account_ForgottenpasswordController extends Application_Controller_Mobile_Default
{
    /**
     * @return $this
     * @throws Zend_Json_Exception
     */
    public function postAction()
    {
        $request = $this->getRequest();
        $application = $this->getApplication();
        $appId = $application->getId();

        try {
            $data = $request->getBodyParams();

            Hook::trigger('mobile.forgotpassword', [
                'appId' => $appId,
                'request' => $request,
                'type' => 'account'
            ]);

            if (empty($data['email'])) {
                throw new Exception(__('Please enter your email address'));
            }
            if (!Zend_Validate::is($data['email'], 'EmailAddress')) {
                throw new Exception(__('Please enter a valid email address'));
            }

            $customer = (new Customer_Model_Customer())->find([
                'email' => $data['email'],
                'app_id' => $appId
            ]);

            if (!$customer->getId()) {
                throw new Exception(__('Your email address does not exist'));
            }

            $adminEmail = null;
            $password = generate_strong_password(10);
            $contact = new Contact_Model_Contact();
            $contactPage = $this->getApplication()->getPage('contact');
            if ($contactPage && $contactPage->getId()) {
                $contact->find($contactPage->getId(), 'value_id');
                $adminEmail = $contact->getEmail();
            }

            $customer->setPassword($password)->save();

            $layout = $this->getLayout()->loadEmail('customer', 'forgot_password');
            $layout
                ->getPartial('content_email')
                ->setCustomer($customer)
                ->setPassword($password)
                ->setAdminEmail($adminEmail)
                ->setApp($application->getName());
            $content = $layout->render();

            $mail = new Mail();
            $mail->setBodyHtml($content);
            $mail->addTo($customer->getEmail(), $customer->getName());
            $mail->setSubject(__('%s - Your new password', $application->getName()));
            $mail->send();

            Hook::trigger('mobile.forgotpassword.success', [
                'appId' => $appId,
                'customerId' => $customer->getId(),
                'customer' => $customer,
                'newPassword' => $password,
                'token' => Zend_Session::getId(),
                'type' => 'account'
            ]);

            $payload = [
                'success' => true,
                'message' => __('Your new password has been sent to the entered email address')
            ];
        } catch (\Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage()
            ];

            Hook::trigger('mobile.forgotpassword.error', [
                'appId' => $appId,
                'message' => $e->getMessage(),
                'type' => 'account',
                'request' => $request,
            ]);
        }

        $this->_sendJson($payload);
    }

}
