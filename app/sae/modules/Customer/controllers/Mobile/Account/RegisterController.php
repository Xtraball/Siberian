<?php

class Customer_Mobile_Account_RegisterController extends Application_Controller_Mobile_Default
{

    public function postAction() {

        if($data = Zend_Json::decode($this->getRequest()->getRawBody())) {


            $customer = new Customer_Model_Customer();

            try {

                if(empty($data["privacy_policy"])) {
                    throw new Exception($this->_("You must agree to our privacy policy to create an account."));
                }

                if(!Zend_Validate::is($data['email'], 'EmailAddress')) {
                    throw new Exception($this->_('Please enter a valid email address'));
                }

                $dummy = new Customer_Model_Customer();
                $dummy->find(array('email' => $data['email'], "app_id" => $this->getApplication()->getId()));

                if($dummy->getId()) {
                    throw new Exception($this->_('We are sorry but this address is already used.'));
                }

                if(empty($data['show_in_social_gaming'])) {
                    $data['show_in_social_gaming'] = 0;
                }

                if(empty($data['password'])) {
                    throw new Exception($this->_('Please enter a password'));
                }

                $customer->setData($data)
                    ->setAppId($this->getApplication()->getId())
                    ->setPassword($data['password'])
                    ->save()
                ;

                //PUSH TO USER ONLY
                if(Push_Model_Message::hasTargetedNotificationsModule()) {
                    if (!empty($data["device_uid"])) {
                        if (strlen($data["device_uid"]) == 36) {
                            $device = new Push_Model_Iphone_Device();
                            $device->find($data["device_uid"], 'device_uid');
                        } else {
                            $device = new Push_Model_Android_Device();

                            if($this->getApplication()->useIonicDesign()) {
                                $device->find($data["device_uid"], 'device_uid');
                            } else {
                                $device->find($data["device_uid"], 'registration_id');
                            }
                        }

                        if ($device->getId()) {
                            $device->setCustomerId($customer->getId())->save();
                        }
                    }
                }

                $this->getSession()->setCustomer($customer);

                $this->_sendNewAccountEmail($customer, $data['password']);

                $html = array(
                    'success' => 1,
                    'customer_id' => $customer->getId(),
                    'can_access_locked_features' => $customer->canAccessLockedFeatures()
                );

            }
            catch(Exception $e) {
                $html = array('error' => 1, 'message' => $e->getMessage());
            }

            $this->_sendHtml($html);

        }

    }

    protected function _sendNewAccountEmail($customer, $password) {

        $admin_email = null;
        $contact = new Contact_Model_Contact();
        $contact_page = $this->getApplication()->getPage('contact');
        $sender = 'no-reply@'.Core_Model_Lib_String::format($this->getApplication()->getName(), true).'.com';

        if($contact_page->getId()) {
            $contact->find($contact_page->getId(), 'value_id');
            $admin_email = $contact->getEmail();
        }

        $layout = $this->getLayout()->loadEmail('customer', 'create_account');
        $layout->getPartial('content_email')->setCustomer($customer)->setPassword($password)->setAdminEmail($admin_email)->setApp($this->getApplication()->getName());
        $content = $layout->render();

        $mail = new Zend_Mail('UTF-8');
        $mail->setBodyHtml($content);
        $mail->setFrom($sender, $this->getApplication()->getName());
        $mail->addTo($customer->getEmail(), $customer->getName());
        $mail->setSubject($this->_('%s - Account creation', $this->getApplication()->getName()));
        $mail->send();

        return $this;

    }

}
