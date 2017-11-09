<?php

class Backoffice_AccountController extends Backoffice_Controller_Default
{

    public function listAction() {
        $this->loadPartials();
    }

    public function newAction() {
        $this->forward('edit');
    }

    public function editAction() {

        $current_user = $this->getSession()->getBackofficeUser();
        $user = new Backoffice_Model_User();

        if($user_id = $this->getRequest()->getParam('user_id')) {

            $user->find($user_id);
            if(!$user->getId()) {
                $this->getSession()->addError(__('This administrator does not exist'));
                $this->_redirect('backoffice/account/list');
                return $this;
            }
        }

        $this->loadPartials();
        $this->getLayout()->getPartial('content')->setCurrentUser($user);
    }

    public function savepostAction() {

        if($data = $this->getRequest()->getPost()) {

            $user = new Backoffice_Model_User();
            $check_email_user = new Backoffice_Model_User();

            try {

                if(!empty($data['user_id'])) {
                    $user->find($data['user_id']);
                    if(!$user->getId()) {
                        throw new Exception(__('An error occurred while saving your account. Please try again later.'));
                    }
                }
                if(empty($data['email'])) {
                    throw new Exception(__('The email is required'));
                }

                $isNew = (bool) !$user->getId();

                $check_email_user->find($data['email'], 'email');
                if($check_email_user->getId() AND $check_email_user->getId() != $user->getId()) {
                    throw new Exception(__('This email address is already used'));
                }

                if(isset($data['password'])) {
                    if($data['password'] != $data['confirm_password']) {
                        throw new Exception(__('Your password does not match the entered password.'));
                    }
                    if(!empty($data['old_password']) AND !$user->isSamePassword($data['old_password'])) {
                        throw new Exception(__("The old password does not match the entered password."));
                    }
                    if(!empty($data['password'])) {
                        $user->setPassword($data['password']);
                        unset($data['password']);
                    }
                } else if($isNew) {
                    throw new Exception(__('The password is required'));
                }

                $user->addData($data)
                    ->save()
                ;

                $this->getSession()->addSuccess(__('The account has been successfully saved'));
                $this->_redirect('backoffice/account/list');

            }
            catch(Exception $e) {
                $this->getSession()->addError($e->getMessage());
                if($user->getId()) {
                    $this->_redirect('backoffice/account/edit', array('user_id' =>  $user->getId()));
                } else {
                    $this->_redirect('backoffice/account/new');
                }
            }

        }

    }

    public function deleteAction() {

        if($user_id = $this->getRequest()->getParam('user_id')) {

            try {

                $user = new Backoffice_Model_User();
                $users = $user->findAll();

                if($users->count() == 1) {
                    throw new Exception(__("This account can't be deleted, it's the only one"));
                }
                $user->find($user_id);

                if(!$user->getId()) {
                    throw new Exception(__("This administrator does not exist"));
                }

                $user->delete();

                $html = array(
                    'success' => 1,
                    'user_id' => $user_id
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

    }

    public function loginAction() {
        $this->loadPartials();
    }

    public function loginpostAction() {

        if($data = $this->getRequest()->getPost()) {

            $this->getSession()->resetInstance();

            try {

                if(empty($data['email']) OR empty($data['password'])) {
                    throw new Exception(__('Authentication failed. Please check your email and/or your password'));
                }
                $user = new Backoffice_Model_User();
                $user->find($data['email'], 'email');

                if($user->authenticate($data['password'])) {
                    $this->getSession()
                        ->setBackofficeUser($user)
                    ;
                }
                
                if(!$this->getSession()->isLoggedIn()) {
                    throw new Exception(__('Authentication failed. Please check your email and/or your password'));
                }

            }
            catch(Exception $e) {
                $this->getSession()->addError($e->getMessage());
            }
        }

        $this->_redirect('backoffice');
        return $this;

    }

    public function forgotpasswordpostAction() {

        if($datas = $this->getRequest()->getPost() AND !$this->getSession()->isLoggedIn(Core_Model_Session::TYPE_BACKOFFICE)) {

            try {

                if(empty($datas['email'])) {
                    throw new Exception(__('Please enter your email address'));
                }

                $user = new Backoffice_Model_User();
                $user->find($datas['email'], 'email');

                if(!$user->getId()) {
                    throw new Exception(__("Your email address does not exist"));
                }

                $password = Core_Model_Lib_String::generate(8);

                $user->setPassword($password)->save();

                $layout = $this->getLayout()->loadEmail('admin', 'forgot_password');
                $subject = __('Your new password');
                $layout->getPartial('content_email')->setPassword($password);

                $content = $layout->render();

                # @version 4.8.7 - SMTP
                $mail = new Siberian_Mail();
                $mail->setBodyHtml($content);
                $mail->addTo($user->getEmail(), $user->getName());
                $mail->setSubject($subject);
                $mail->send();

                $this->getSession()->addSuccess(__('Your new password has been sent to the entered email address'));

            }
            catch(Exception $e) {
                $this->getSession()->addError($e->getMessage());
            }
        }

        $this->_redirect('backoffice');
        return $this;

    }

    public function logoutAction() {
        $this->getSession()->resetInstance();
        $this->_redirect('');
        return $this;
    }

}
