<?php

class Mcommerce_Application_StoreController extends Application_Controller_Default_Ajax {

    public function newAction() {
        $this->_forward('edit');
    }

    public function editAction() {

        $store = new Mcommerce_Model_Store();
        $mcommerce = $this->getCurrentOptionValue()->getObject();
        if($id = $this->getRequest()->getParam('store_id')) {
            $store->find($id);
            if($store->getId() AND $mcommerce->getId() != $store->getMcommerceId()) {
                throw new Exception($this->_('An error occurred during the process. Please try again later.'));
            }
        }

        $html = $this->getLayout()->addPartial('store_form', 'admin_view_default', 'mcommerce/application/edit/store/edit.phtml')
            ->setOptionValue($this->getCurrentOptionValue())
            ->setCurrentStore($store)
            ->toHtml();

        $html = array('form_html' => $html);

        $this->_sendHtml($html);

    }

    public function editpostAction() {

        if($datas = $this->getRequest()->getPost()) {

            try {
                $isNew = false;
                $mcommerce = $this->getCurrentOptionValue()->getObject();
                $store = new Mcommerce_Model_Store();
                if(!empty($datas['store_id'])) {
                    $store->find($datas['store_id']);
                    if($store->getId() AND $mcommerce->getId() != $store->getMcommerceId()) {
                        throw new Exception($this->_('An error occurred while saving. Please try again later.'));
                    }
                }

                if(!empty($datas['details_delivery_methods'])) {
                    foreach($datas['details_delivery_methods'] as $method_id => $delivery_details) {
                        foreach($datas['new_delivery_methods'] as $key => $delivery_method) {
                            if($delivery_method['method_id'] == $method_id) {
                                $datas['new_delivery_methods'][$key] = array_merge($delivery_details, $delivery_method);
                            }
                        }
                    }
                    unset($datas['details_delivery_methods']);
                }

                if(!empty($datas['details_payment_methods'])) {
                    foreach($datas['details_payment_methods'] as $method_id => $payment_details) {
                        foreach($datas['new_payment_methods'] as $key => $payment_method) {
//                            if($payment_method['method_id'] == $method_id) {
                                $datas['new_payment_methods'][$key] = array_merge($payment_details, $payment_method);
//                            }
                        }
                    }
                    unset($datas['details_payment_methods']);
                }

                $datas["clients_calculate_change"] = !empty($datas["clients_calculate_change"]);

                $latitude = null;
                $longitude = null;
                if(!empty($datas['street']) AND !empty($datas['postcode']) AND !empty($datas['city']) AND !empty($datas['country'])) {
                    $address = array_intersect_key($datas, array('street'=>'street', 'postcode'=>'postcode', 'city'=>'city', 'country'=>'country'));
                    list($latitude, $longitude) = Siberian_Google_Geocoding::getLatLng($address);
                }

                $datas['latitude'] = $latitude;
                $datas['longitude'] = $longitude;

                if(!$store->getId()) {
                    $datas['mcommerce_id'] = $mcommerce->getId();
                    $isNew = true;
                }
                $store->setData($datas)->save();

                $html = array(
                    'store_id' => $store->getId(),
                    'success' => '1',
                    'success_message' => $this->_('Store successfully saved'),
                    'message_timeout' => 2,
                    'message_button' => 0,
                    'message_loader' => 0
                );

                if($isNew) {
                    $html['row_html'] = $this->getLayout()->addPartial('row_store_'.$store->getId(), 'admin_view_default', 'mcommerce/application/edit/store/li.phtml')
                        ->setCurrentOptionValue($this->getCurrentOptionValue())
                        ->setCurrentStore($store)
                        ->toHtml()
                    ;

                }
                else {
                    $html['store_name'] = $store->getFullAddress(', ');
                }

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

    public function removeAction() {

        $store = new Mcommerce_Model_Store();

        try {
            if($id = $this->getRequest()->getParam('store_id')) {

                $mcommerce = $this->getCurrentOptionValue()->getObject();
                $store->find($id);
                if(!$store->getId() OR $mcommerce->getId() != $store->getMcommerceId()) {
                    throw new Exception($this->_('An error occurred during the process. Please try again later.'));
                }

                $store->setIsVisible(0)->save();

                $html = array(
                    'store_id' => $store->getId(),
                    'success' => '1',
                    'success_message' => $this->_('Store successfully deleted'),
                    'message_timeout' => 2,
                    'message_button' => 0,
                    'message_loader' => 0
                );

            }
            else {
                throw new Exception($this->_('An error occurred during the process. Please try again later.'));
            }
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