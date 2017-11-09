<?php

class Mcommerce_Application_Settings_PrinterController extends Application_Controller_Default_Ajax {

    public function saveAction() {

        if($datas = $this->getRequest()->getPost()) {

            try {

                if(empty($datas['email'])) throw new Exception($this->_('An error occurred during the process. Please try again later.'));

                $mcommerce = $this->getCurrentOptionValue()->getObject();

                foreach($datas['email'] as $store_id => $email) {

                    $printer = new Mcommerce_Model_Store_Printer();
                    $store = new Mcommerce_Model_Store();
                    $store->find($store_id);

                    if($store->getId() AND $mcommerce->getId() != $store->getMcommerceId()) {
                        throw new Exception($this->_('An error occurred while saving. Please try again later.'));
                    }

                    if(is_null($email)) throw new Exception($this->_('Please, enter an email address for the store %s', $store->getName()));
                    elseif(!Zend_Validate::is($email, 'emailAddress')) throw new Exception($this->_('Please, enter an email address for the store %s', $store->getName()));

                    $printer->find($store->getId(), 'store_id');

                    if(!$printer->getId()) {
                        $printer->setStoreId($store_id);
                    }

                    $printer->setEmail($email)->save();

                }

                $html = array(
                    'success' => '1',
                    'success_message' => $this->_('Settings successfully saved'),
                    'message_timeout' => 2,
                    'message_button' => 0,
                    'message_loader' => 0
                );

            }
            catch(Exception $e) {
                $html = array(
                    'error' => 1,
                    'message' => $e->getMessage(),
                    'message_button' => 1,
                    'message_loader' => 1
                );
            }

            $this->_sendHtml($html);

        }

    }

}