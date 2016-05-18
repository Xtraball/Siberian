<?php

class Customer_Mobile_Account_EditController extends Application_Controller_Mobile_Default {

    public function findAction() {

        $customer = $this->getSession()->getCustomer();
        $data = array();
        if($customer->getId()) {
            $data = array(
                "id" => $customer->getId(),
                "civility" => $customer->getCivility(),
                "firstname" => $customer->getFirstname(),
                "lastname" => $customer->getLastname(),
                "email" => $customer->getEmail(),
                "show_in_social_gaming" => (bool) $customer->getShowInSocialGaming()
            );

        }

        $this->_sendHtml($data);

    }

    public function postAction() {

        if($data = Zend_Json::decode($this->getRequest()->getRawBody())) {

            $customer = $this->getSession()->getCustomer();

            try {

                $clearCache = false;

                if(!$customer->getId()) {
                    throw new Exception($this->_("An error occurred while saving. Please try again later."));
                }

                if(!Zend_Validate::is($data['email'], 'EmailAddress')) {
                    throw new Exception($this->_('Please enter a valid email address'));
                }

                $dummy = new Customer_Model_Customer();
                $dummy->find(array('email' => $data['email'], "app_id" => $this->getApplication()->getId()));

                if($dummy->getId() AND $dummy->getId() != $customer->getId()) {
                    throw new Exception($this->_('We are sorry but this address is already used.'));
                }

                if(empty($data['show_in_social_gaming'])) $data['show_in_social_gaming'] = 0;

                if($data['show_in_social_gaming'] != $customer->getShowInSocialGaming()) $clearCache = true;

                if(isset($data['id'])) unset($data['id']);
                if(isset($data['customer_id'])) unset($data['customer_id']);

                $password = "";
                if(!empty($data['password'])) {

                    if(empty($data['old_password']) OR (!empty($data['old_password']) AND !$customer->isSamePassword($data['old_password']))) {
                        throw new Exception($this->_("The old password does not match the entered password."));
                    }

                    $password = $data['password'];
                }

                $customer->setData($data);
                if(!empty($password)) $customer->setPassword($password);
                $customer->save();

                $html = array(
                    "success" => 1,
                    "message" => $this->_("Info successfully saved"),
                    "clearCache" => $clearCache
                );

            }
            catch(Exception $e) {
                $html = array('error' => 1, 'message' => $e->getMessage());
            }

            $this->_sendHtml($html);

        }

    }

}
