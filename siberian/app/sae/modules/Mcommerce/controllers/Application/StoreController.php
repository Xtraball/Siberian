<?php

class Mcommerce_Application_StoreController extends Application_Controller_Default_Ajax {

    public function newAction() {
        $this->_forward('edit');
    }

    public function editAction() {

        $store = new Mcommerce_Model_Store();
        $mcommerce = $this->getCurrentOptionValue()->getObject();
        if ($id = $this->getRequest()->getParam('store_id')) {
            $store->find($id);
            if ($store->getId() AND $mcommerce->getId() != $store->getMcommerceId()) {
                throw new Exception(__('An error occurred during the process. Please try again later.'));
            }
        }

        $html = $this->getLayout()->addPartial('store_form', 'admin_view_default', 'mcommerce/application/edit/store/edit.phtml')
            ->setOptionValue($this->getCurrentOptionValue())
            ->setCurrentStore($store)
            ->toHtml();

        $html = ['form_html' => $html];

        $this->_sendHtml($html);

    }

    public function editpostAction() {
        try {
            $request = $this->getRequest();
            $params = $request->getParams();
            if (empty($params)) {
                throw new Siberian_Exception(__('Missing params!'));
            }

            $optionValue = $this->getCurrentOptionValue();
            $mcommerce = $optionValue->getObject();

            if (!empty($params['store_id'])) {
                $store = (new Mcommerce_Model_Store())
                    ->find($params['store_id']);
                if ($store->getId() && $mcommerce->getId() !== $store->getMcommerceId()) {
                    throw new Siberian_Exception(__('The store & mcommerce instances mismatch!'));
                }
            } else {
                $store = new Mcommerce_Model_Store();
            }

            // Upsert delivery methods!
            if (!empty($params['details_delivery_methods'])) {
                foreach ($params['details_delivery_methods'] as $methodId => $deliveryDetails) {
                    foreach ($params['new_delivery_methods'] as $key => $deliveryMethod) {
                        if ($deliveryMethod['method_id'] == $methodId) {
                            $params['new_delivery_methods'][$key] = array_merge($deliveryDetails, $deliveryMethod);
                        }
                    }
                }
                unset($params['details_delivery_methods']);
            }

            $params['clients_calculate_change'] = !empty($params['clients_calculate_change']);

            $latitude = null;
            $longitude = null;
            if (!empty($params['street']) &&
                !empty($params['postcode']) &&
                !empty($params['city']) &&
                !empty($params['country'])) {
                $address = array_intersect_key($params, [
                    'street' => 'street',
                    'postcode' => 'postcode',
                    'city' => 'city',
                    'country' => 'country'
                ]);
                list($latitude, $longitude) = Siberian_Google_Geocoding::getLatLng($address, $this->getApplication()->getGooglemapsKey());
            }

            $params['latitude'] = $latitude;
            $params['longitude'] = $longitude;

            if (!$store->getId()) {
                $params['mcommerce_id'] = $mcommerce->getId();
                $isNew = true;
            }
            $store
                ->setData($params)
                ->save();

            $payload = [
                'store_id' => $store->getId(),
                'success' => '1',
                'success_message' => __('Store successfully saved'),
                'message_timeout' => 2,
                'message_button' => 0,
                'message_loader' => 0
            ];

            if ($isNew) {
                $payload['row_html'] = $this->getLayout()->addPartial('row_store_'.$store->getId(), 'admin_view_default', 'mcommerce/application/edit/store/li.phtml')
                    ->setCurrentOptionValue($this->getCurrentOptionValue())
                    ->setCurrentStore($store)
                    ->toHtml()
                ;

            } else {
                $payload['store_name'] = $store->getFullAddress(', ');
            }
        } catch(Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }

        $this->_sendJson($payload);
    }

    public function removeAction() {

        $store = new Mcommerce_Model_Store();

        try {
            if($id = $this->getRequest()->getParam('store_id')) {

                $mcommerce = $this->getCurrentOptionValue()->getObject();
                $store->find($id);
                if(!$store->getId() OR $mcommerce->getId() != $store->getMcommerceId()) {
                    throw new Exception(__('An error occurred during the process. Please try again later.'));
                }

                $store->setIsVisible(0)->save();

                $html = [
                    'store_id' => $store->getId(),
                    'success' => '1',
                    'success_message' => __('Store successfully deleted'),
                    'message_timeout' => 2,
                    'message_button' => 0,
                    'message_loader' => 0
                ];

            }
            else {
                throw new Exception(__('An error occurred during the process. Please try again later.'));
            }
        } catch(Exception $e) {
            $html = [
                'error' => 1,
                'message' => $e->getMessage(),
                'message_button' => 1,
                'message_loader' => 1
            ];
        }

        $this->_sendHtml($html);

    }

}