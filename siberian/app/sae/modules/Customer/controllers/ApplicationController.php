<?php

class Customer_ApplicationController extends Application_Controller_Default {

    public function listAction() {
        $this->loadPartials();
    }

    public function newAction() {
        $this->_forward("edit");
    }

    public function editAction() {

        $customer = new Customer_Model_Customer();
        if($customer_id = $this->getRequest()->getParam('customer_id')) {
            $customer->find($customer_id);
            if(!$customer->getId()) {
                $this->getRequest()->addError($this->_("This user does not exist."));
            }
        }

        $this->loadPartials();
        $this->getLayout()->getPartial('content')->setCurrentCustomer($customer);

    }

    public function saveAction() {

        if($data = $this->getRequest()->getPost()) {

            try {
                $customer = new Customer_Model_Customer();
                if(!empty($data['customer_id'])) {
                    $customer->find($data['customer_id']);
                    if(!$customer->getId() || $customer->getAppId() != $this->getApplication()->getId()) {
                        throw new Exception($this->_("An error occurred while saving. Please try again later."));
                    }
                }

                $isNew = !$customer->getId();
                $errors = array();

                if(empty($data['civility'])) $errors[] = $this->_("the gender");
                if(empty($data['firstname'])) $errors[] = $this->_("the first name");
                if(empty($data['lastname'])) $errors[] = $this->_("the last name");
                if(empty($data['email'])) $errors[] = $this->_("the email address");
                if($isNew AND empty($data['password'])) $errors[] = $this->_("the password");

                if(!empty($errors)) {
                    $message = array($this->_("Please fill in the following fields:"));
                    foreach($errors as $error) {
                        $message[] = $error;
                    }
                    $message = join('<br />- ', $message);
                    throw new Exception($message);
                }

                if(!empty($data['email']) AND !Zend_Validate::is($data['email'], 'emailAddress')) throw new Exception($this->_("Please enter a valid email address"));

                //Test if the email is already used
                if(empty($data['customer_id'])) {
                    $customers = $customer->findAll(array("email = ?" => $data["email"], "app_id = ?" => $this->getApplication()->getId()));
                    if ($customers->count()) {
                        $message = $this->_("We are sorry but the %s account is already linked to one of our customers", $data["email"]);
                        throw new Exception($message);
                    }
                }

                $data['show_in_social_gaming'] = (int) !empty($data['show_in_social_gaming']);
                $data['can_access_locked_features'] = (int) !empty($data['can_access_locked_features']);

                if($isNew) {
                    $data['app_id'] = $this->getApplication()->getId();
                }

                if(isset($data['password']) AND empty($data['password'])) {
                    unset($data['password']);
                }

                $customer->setData($data);
                if(!empty($data['password'])) {
                    $customer->setPassword($data['password']);
                }
                $customer->save();

                $this->getSession()->addSuccess($this->_("Info successfully saved"));
                $html = array("success" => 1);

            }
            catch(Exception $e) {
                $html = array(
                    "error" => 1,
                    "message" => $e->getMessage(),
                    'message_button' => 1,
                    'message_loader' => 1
                );
            }

            $this->getResponse()->setBody(Zend_Json::encode($html))->sendResponse();
            die;

        }

    }

    public function deleteAction() {

        if($customer_id = $this->getRequest()->getPost('customer_id')) {

            try {

                $customer = new Customer_Model_Customer();
                $customer->find($customer_id);
                if(!$customer->getId() || $customer->getAppId() != $this->getApplication()->getId()) {
                    throw new Exception($this->_("An error occurred while saving. Please try again later."));
                }

                $customer_id = $customer->getId();
                $customer->delete();

                $html = array("success" => 1, "customer_id" => $customer_id);

            }
            catch(Exception $e) {
                $html = array(
                    'message' => $e->getMessage(),
                    'message_button' => 1,
                    'message_loader' => 1
                );
            }

            $this->getResponse()->setBody(Zend_Json::encode($html))->sendResponse();
            die;

        }

    }

    public function getuserslistpaginateAction() {
        $data = $this->getRequest()->getPost();

        $html = array(
            "customers" => array()
        );

        if($data["limit"]) {

            $search_params = array(
                "app_id" => $this->getApplication()->getId()
            );

            if($data["search_value"]) {
                $search_params["search"] = "(email LIKE '%".$data["search_value"]."%' OR firstname LIKE '%".$data["search_value"]."%' OR lastname LIKE '%".$data["search_value"]."%')";
            }

            $customer_model = new Customer_Model_Customer();
            $customer_model = $customer_model->findAllCustomersByApp($search_params, array("limit" => $data["limit"], "offset" => $data["offset"]));

            $customers = array();
            foreach($customer_model as $customer) {
                $customer->setCreatedAt($customer->getFormattedCreatedAt($this->_(Zend_Date::DATE_MEDIUM)));
                $customers[] = $customer->getData();
            }

            $html["customers"] = $customers;
        }

        $this->getResponse()->setBody(Zend_Json::encode($html))->sendResponse();
        die;
    }

}
