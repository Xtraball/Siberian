<?php

class Admin_AccountController extends Admin_Controller_Default
{

    public function editAction() {
        $this->loadPartials();
        $current_admin = $this->getSession()->getAdmin();
        $this->getLayout()->getPartial("content")->setMode("edit")->setEditAdmin($current_admin);
    }

    public function savepostAction() {

        if($data = $this->getRequest()->getPost()) {

            try {

                $admin = new Admin_Model_Admin();
                $current_admin = $this->getSession()->getAdmin();
                $check_email_admin = new Admin_Model_Admin();
                $html = '';

                if(!empty($data['admin_id'])) {
                    $admin->find($data['admin_id']);
                    if(!$admin->getId()) {
                        throw new Exception(__('An error occurred while saving your account. Please try again later.'));
                    }
                }
                if(empty($data['email'])) {
                    throw new Exception(__('The email is required'));
                }
    
                if( $admin->getId() AND $admin->getId() != $this->getAdmin()->getId() AND (
                        $admin->getParentId() AND $admin->getParentId() != $this->getAdmin()->getId() OR
                        !$admin->getParentId()
                    )) {

                        throw new Exception(__("An error occurred while saving your account. Please try again later."));

                }
                
                if(!$admin->getId() OR $admin->getId() != $this->getAdmin()->getId()) {
                    $admin->setParentId($this->getAdmin()->getId());
                }

                $check_email_admin->find($data['email'], 'email');
                if($check_email_admin->getId() AND $check_email_admin->getId() != $admin->getId()) {
                    throw new Exception(__('This email address is already used'));
                }

                if(isset($data['password'])) {
                    if($data['password'] != $data['confirm_password']) {
                        throw new Exception(__('Your password does not match the entered password.'));
                    }
                    if(!empty($data['old_password']) AND !$admin->isSamePassword($data['old_password'])) {
                        throw new Exception(__("The old password does not match the entered password."));
                    }
                    if(!empty($data['password'])) {
                        $admin->setPassword($data['password']);
                        unset($data['password']);
                    }
                }

                if(empty($data["role_id"]) AND $data["mode"]=="management") {
                    throw new Exception(__('The account role is required'));
                } else {
                    if($data["mode"]=="management") {
                        $admin->setRoleId($data["role_id"]);
                    }
                }

                $admin->addData($data)
                    ->save()
                ;

                //For SAE we link automatically the user to the uniq app
                if(Siberian_Version::is("sae")) {
                    $this->getApplication()->addAdmin($admin);
                }

                $html = array('success' => 1);
                    $html = array_merge($html, array(
                        'success_message' => __('The account has been successfully saved'),
                        'message_timeout' => false,
                        'message_button' => false,
                        'message_loader' => 1
                    ));
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

    public function deleteAction() {

        if($admin_id = $this->getRequest()->getParam('admin_id') AND !$this->getSession()->getAdmin()->getParentId()) {

            try {

                $admin = new Admin_Model_Admin();
                $admin->find($admin_id);

                if(!$admin->getId()) {
                    throw new Exception(__("This administrator does not exist"));
                } else if(!$admin->getParentId()) {
                    throw new Exception(__("You can't delete the main account"));
                }

                $admin->delete();

                $html = array(
                    'success' => 1,
                    'admin_id' => $admin_id
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

        if(!$this->getSession()->isLoggedIn() && ($datas = $this->getRequest()->getPost())) {

            $this->getSession()->resetInstance();
            $canBeLoggedIn = false;

            try {

                if(empty($datas['email']) OR empty($datas['password'])) {
                    throw new Exception(__('Authentication failed. Please check your email and/or your password'));
                }
                $admin = new Admin_Model_Admin();
                $admin->findByEmail($datas['email']);

                if($admin->authenticate($datas['password'])) {
                    $this->getSession()
                        ->setAdmin($admin)
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

        $this->_redirect('/');
        return $this;

    }

    public function signuppostAction() {

        if($data = $this->getRequest()->getPost()) {
            try {

                // Check l'email et le mot de passe
                if(empty($data['email']) OR !Zend_Validate::is($data['email'], 'emailAddress')) {
                    throw new Exception(__('Please enter a valid email address.'));
                }
                if(empty($data['password']) OR strlen($data['password']) < 6) {
                    throw new Exception(__('The password must be at least 6 characters.'));
                }
                if(empty($data['confirm_password']) OR $data['password'] != $data['confirm_password']) {
                    throw new Exception(__('The password and the confirmation does not match.'));
                }

                $admin = new Admin_Model_Admin();
                $admin->findByEmail($data['email']);

                if($admin->getId()) {
                    throw new Exception(__('We are sorry but this email address is already used.'));
                }

                $role = new Acl_Model_Role();
                if($default_role = $role->findDefaultRoleId()) {
                    $admin->setRoleId($default_role);
                }

                // Créé le user
                $admin->setEmail($data['email'])
                    ->setPassword($data['password'])
                    ->save()
                ;

                // Met le user en session
                $this->getSession()
                    ->setAdmin($admin)
                ;

                $admin->sendAccountCreationEmail($data["password"]);

                $redirect_to = 'admin/application/list';

            }
            catch(Exception $e) {
                if($this->getSession()->isLoggedIn()) {
                    $redirect_to = 'admin/application/list';
                } else {
                    $this->getSession()->addError($e->getMessage());
                    $redirect_to = "/";
                }
            }

            $this->redirect($redirect_to);

        }

    }

    public function forgotpasswordpostAction() {

        if($datas = $this->getRequest()->getPost() AND !$this->getSession()->isLoggedIn('admin') AND !$this->getSession()->isLoggedIn('pos')) {

            try {

                if(empty($datas['email'])) {
                    throw new Exception(__('Please enter your email address'));
                }

                $admin = new Admin_Model_Admin();
                $admin->findByEmail($datas['email']);

                if(!$admin->getId()) {
                    throw new Exception(__("Your email address does not exist"));
                }

                $password = Core_Model_Lib_String::generate(8);

                $admin->setPassword($password)->save();

                $layout = $this->getLayout()->loadEmail('admin', 'forgot_password');
                $subject = __('%s - Your new password');
                $layout->getPartial('content_email')->setPassword($password);

                $content = $layout->render();

                # @version 4.8.7 - SMTP
                $mail = new Siberian_Mail();
                $mail->setBodyHtml($content);
                $mail->addTo($admin->getEmail(), $admin->getName());
                $mail->setSubject($subject, array("_sender_name"));
                $mail->send();

                $this->getSession()->addSuccess(__('Your new password has been sent to the entered email address'));

            }
            catch(Exception $e) {
                $this->getSession()->addError($e->getMessage());
            }
        }

        $this->_redirect('/');
        return $this;

    }

    public function logoutAction() {
        $this->getSession()->resetInstance();
        $this->_redirect('');
        return $this;
    }

}
