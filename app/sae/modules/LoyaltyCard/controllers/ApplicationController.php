<?php

class LoyaltyCard_ApplicationController extends Application_Controller_Default
{

    public function editpostAction()
    {

        if ($datas = $this->getRequest()->getPost()) {

            try {

                $html = '';

                // Test s'il y a une erreur dans la saisie
                if (empty($datas['name']) OR empty($datas['number_of_points']) OR empty($datas['advantage']) OR empty($datas['conditions'])) {
                    throw new Exception($this->_('An error occurred while saving your loyalty card. Please fill all fields in.'));
                }

                // Test s'il y a un value_id
                if (empty($datas['value_id'])) throw new Exception($this->_('An error occurred while saving your loyalty card.'));

                // Récupère l'option_value en cours
                $option_value = new Application_Model_Option_Value();
                $option_value->find($datas['value_id']);
                $application = $this->getApplication();
                $card = new LoyaltyCard_Model_LoyaltyCard();

                $card->setData($datas)
                    ->setValueId($option_value->getId())
                    ->save();

                $html = array(
                    'success' => '1',
                    'success_message' => $this->_('Your loyalty card has been saved successfully'),
                    'message_timeout' => 2,
                    'message_button' => 0,
                    'message_loader' => 0
                );

            } catch (Exception $e) {
                $html = array(
                    'message' => $e->getMessage(),
                    'message_button' => 1,
                    'message_loader' => 1
                );
            }

            $this->getLayout()->setHtml(Zend_Json::encode($html));
        }

    }

    public function savepasswordAction()
    {

        if ($datas = $this->getRequest()->getPost()) {

            $html = '';

            try {

                $isNew = true;

                $password = new LoyaltyCard_Model_Password();
                $application = $this->getApplication();
                $password_id = $datas['password_id'];

                if (!empty($datas['password_id'])) {
                    $password->find($datas['password_id']);
                    if ($password->getAppId() != $application->getId()) {
                        throw new Exception($this->_("An error occurred while saving the password. Please try again later"));
                    }
                    $isNew = false;
                } else {
                    $datas['app_id'] = $application->getId();
                }

                if (empty($datas['is_deleted'])) {
                    if (empty($datas['name'])) throw new Exception($this->_('Please enter a name'));

                    if (empty($datas['password'])/* OR empty($datas['confirm_password'])*/) {
                        throw new Exception($this->_('Please enter a password'));
                    }
                    if (strlen($datas['password']) < 4 OR !ctype_digit($datas['password'])/* OR empty($datas['confirm_password'])*/) {
                        throw new Exception($this->_('Your password must be 4 digits'));
                    }

                    $password->setPassword(sha1($datas['password']));
                    if ($datas['password']) unset($datas['password']);
                } else if (!$password->getId()) {
                    throw new Exception($this->_('An error occurred while saving the password. Please try again later.'));
                }
                $password->addData($datas)
                    ->save();

                $html = array('success' => 1, 'id' => $password->getId());
                if (!empty($datas['is_deleted'])) {
                    $html['is_deleted'] = 1;
                    $html['id'] = $password_id;
                } else if ($isNew) {
                    $html['is_new'] = 1;
                    $html['name'] = $password->getName();
                }
//                }
//                else {
//                    $employee->delete();
//                    $html = array();
//                }
            } catch (Exception $e) {
                $html = array('message' => $e->getMessage());
            }

            $this->getLayout()->setHtml(Zend_Json::encode($html));

        }
    }

}