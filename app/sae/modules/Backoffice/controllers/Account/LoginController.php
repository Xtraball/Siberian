<?php

class Backoffice_Account_LoginController extends Backoffice_Controller_Default
{

    public function postAction() {

        if($data = Zend_Json::decode($this->getRequest()->getRawBody())) {

            $this->getSession()->resetInstance();

            try {

                if(empty($data['email']) OR empty($data['password'])) {
                    throw new Exception($this->_('Authentication failed. Please check your email and/or your password'));
                }
                $user = new Backoffice_Model_User();
                $user->find($data['email'], 'email');

                if($user->authenticate($data['password'])) {
                    $this->getSession()
                        ->setBackofficeUser($user)
                    ;
                }

                $notification = new Backoffice_Model_Notification();
                $notification->update();

                if(!$this->getSession()->isLoggedIn()) {
                    throw new Exception($this->_('Authentication failed. Please check your email and/or your password'));
                }

                $data = array("success" => 1, "user" => $user->getData());

            }
            catch(Exception $e) {
                $data = array(
                    "error" => 1,
                    "message" => $e->getMessage()
                );
            }

            $this->_sendHtml($data);

        }

    }

    public function forgottenpasswordAction() {

        if($data = Zend_Json::decode($this->getRequest()->getRawBody())) {

            try {

                if(empty($data['email'])) {
                    throw new Exception($this->_('Please enter your email address'));
                }

                $user = new Backoffice_Model_User();
                $user->find($data['email'], 'email');

                if(!$user->getId()) {
                    throw new Exception($this->_("Your email address does not exist"));
                }

                $password = Core_Model_Lib_String::generate(8);

                $user->setPassword($password)->save();

                $sender = System_Model_Config::getValueFor("support_email");
                $support_name = System_Model_Config::getValueFor("support_name");
                $layout = $this->getLayout()->loadEmail('admin', 'forgot_password');
                $subject = $this->_('Your new password');
                $layout->getPartial('content_email')->setPassword($password);

                $content = $layout->render();

                $mail = new Zend_Mail('UTF-8');
                $mail->setBodyHtml($content);
                $mail->setFrom($sender, $support_name);
                $mail->addTo($user->getEmail(), $user->getName());
                $mail->setSubject($subject);
                $mail->send();

                $data = array(
                    "success" => 1,
                    "message" => $this->_('Your new password has been sent to the entered email address')
                );

            }
            catch(Exception $e) {

                $data = array(
                    "error" => 1,
                    "message" => $e->getMessage()
                );
            }
        }

        $this->_sendHtml($data);

    }

    public function logoutAction() {
        $this->getSession()->resetInstance();
        $this->_sendHtml(array("success" => 1));
    }

}
