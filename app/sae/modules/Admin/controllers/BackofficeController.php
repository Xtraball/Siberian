<?php

class Admin_BackofficeController extends Backoffice_Controller_Default
{

    public function listAction() {
        $this->loadPartials();
    }

    public function newAction() {
        $this->_forward('edit');
    }

    public function editAction() {

        $admin = new Admin_Model_Admin();

        if($admin_id = $this->getRequest()->getParam('admin_id')) {
            $admin->find($admin_id);
        }

        $this->loadPartials();
        $this->getLayout()->getPartial('content')->setCurrentAdmin($admin);
    }

    public function savepostAction() {

        if($datas = $this->getRequest()->getPost()) {

            $admin = new Admin_Model_Admin();
            $check_email_admin = new Admin_Model_Admin();

            try {

                if(!empty($datas['admin_id'])) {
                    $admin->find($datas['admin_id']);
                    if(!$admin->getId()) {
                        throw new Exception($this->_('An error occurred while saving your account. Please try again later.'));
                    }
                }
                if(empty($datas['email'])) {
                    throw new Exception($this->_('The email is required'));
                }
                $isNew = (bool) !$admin->getId();

                $check_email_admin->find($datas['email'], 'email');
                if($check_email_admin->getId() AND $check_email_admin->getId() != $admin->getId()) {
                    throw new Exception($this->_('This email address is already used'));
                }


                if(isset($datas['password'])) {
                    if($datas['password'] != $datas['confirm_password']) {
                        throw new Exception($this->_('Your password does not match the entered password.'));
                    }
                    if(!empty($datas['old_password']) AND !$admin->isSamePassword($datas['old_password'])) {
                        throw new Exception($this->_("The old password does not match the entered password."));
                    }
                    if(!empty($datas['password'])) {
                        $admin->setPassword($datas['password']);
                        unset($datas['password']);
                    }
                } else if($isNew) {
                    throw new Exception($this->_('The password is required'));
                }

                $admin->addData($datas)
                    ->save()
                ;

                $this->getSession()->addSuccess($this->_('The account has been successfully saved'));
                $this->_redirect('admin/backoffice/list');

            }
            catch(Exception $e) {
                $this->getSession()->addError($e->getMessage());
                if($admin->getId()) {
                    $this->_redirect('admin/backoffice/edit', array('admin_id' =>  $admin->getId()));
                } else {
                    $this->_redirect('admin/backoffice/new');
                }
            }

        }

    }

    public function deleteAction() {

        if($admin_id = $this->getRequest()->getParam('admin_id')) {

            try {

                $admin = new Admin_Model_Admin();
                $admin->find($admin_id);

                if(!$admin->getId()) {
                    throw new Exception($this->_("This administrator does not exist"));
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

}
