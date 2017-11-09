<?php

class Customer_Mobile_Account_ForgottenpasswordController extends Application_Controller_Mobile_Default
{

    public function postAction() {

        if($data = Zend_Json::decode($this->getRequest()->getRawBody())) {

            try {

                if(empty($data['email'])) throw new Exception(__('Please enter your email address'));
                if(!Zend_Validate::is($data['email'], 'EmailAddress')) throw new Exception(__('Please enter a valid email address'));

                $customer = new Customer_Model_Customer();
                $customer->find(array('email' => $data['email'], "app_id" => $this->getApplication()->getId()));

                if(!$customer->getId()) {
                    throw new Exception("Your email address does not exist");
                }

                $admin_email = null;
                $password = Core_Model_Lib_String::generate(8);
                $contact = new Contact_Model_Contact();
                $contact_page = $this->getApplication()->getPage('contact');
                if($contact_page->getId()) {
                    $contact->find($contact_page->getId(), 'value_id');
                    $admin_email = $contact->getEmail();
                }

                $customer->setPassword($password)->save();

                //$sender = 'no-reply@'.Core_Model_Lib_String::format($this->getApplication()->getName(), true).'.com';
                $layout = $this->getLayout()->loadEmail('customer', 'forgot_password');
                $layout->getPartial('content_email')->setCustomer($customer)->setPassword($password)->setAdminEmail($admin_email)->setApp($this->getApplication()->getName());
                $content = $layout->render();

                # @version 4.8.7 - SMTP
                $mail = new Siberian_Mail();
                $mail->setBodyHtml($content);
                //$mail->setFrom($sender, $this->getApplication()->getName());
                $mail->addTo($customer->getEmail(), $customer->getName());
                $mail->setSubject(__('%s - Your new password', $this->getApplication()->getName()));
                $mail->send();

                $html = array(
                    "success" => 1,
                    "message" => __("Your new password has been sent to the entered email address")
                );

            }
            catch(Exception $e) {
                $html = array(
                    'error' => 1,
                    'message' => $e->getMessage()
                );
            }

            $this->_sendHtml($html);

        }

        return $this;
    }

}
