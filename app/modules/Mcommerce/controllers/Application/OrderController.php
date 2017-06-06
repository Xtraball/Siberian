<?php

class Mcommerce_Application_OrderController extends Application_Controller_Default_Ajax {

    public function newAction() {
        $this->_forward('edit');
    }

    public function editAction() {

        $order = new Mcommerce_Model_Order();
        $mcommerce = $this->getCurrentOptionValue()->getObject();
        if($id = $this->getRequest()->getParam('order_id')) {
            $order->find($id);
            if($order->getId() AND $mcommerce->getId() != $order->getMcommerceId()) {
                throw new Exception($this->_('An error occurred during the process. Please try again later.'));
            }
        }

        $html = $this->getLayout()->addPartial('store_form', 'admin_view_default', 'mcommerce/application/edit/order/edit.phtml')
            ->setOptionValue($this->getCurrentOptionValue())
            ->setCurrentOrder($order)
            ->toHtml();

        $html = array('form_html' => $html);

        $this->_sendHtml($html);

    }

    public function editpostAction() {

        if($datas = $this->getRequest()->getPost()) {

            try {

                $mcommerce = $this->getCurrentOptionValue()->getObject();
                $order = new Mcommerce_Model_Order();
                if(!empty($datas['order_id'])) {
                    $order->find($datas['order_id']);
                    if($order->getId() AND $mcommerce->getId() != $order->getMcommerceId()) {
                        throw new Exception($this->_('An error occurred while saving. Please try again later.'));
                    }
                }

				$allowed_fields = array('customer_firstname', 'customer_lastname', 'customer_email', 'customer_phone', 'customer_street', 'customer_streetc', 'customer_postcode', 'customer_city', 'customer_birthday', 'status_id');
				
				if($mcommerce->getData("require_datedelivery")) 
				{$allowed_fields_compl = array('delivery_datetime');
				$allowed_fields = array_merge($allowed_fields, $allowed_fields_compl);
				}

				if($mcommerce->getData("show_customercomment")) 
				{$allowed_fields_compl = array('delivery_comment');
				$allowed_fields = array_merge($allowed_fields, $allowed_fields_compl);
				}
				

				

                
                foreach($datas as $key => $data) {
                    if(!in_array($key, $allowed_fields)) {
                        unset($datas[$key]);
                    }
                }

                $order->addData($datas)->save();

                if($order->getStatusId() == Mcommerce_Model_Order::CANCEL_STATUS) {
                    $layout = $this->getLayout()->loadEmail('mcommerce', 'send_order_cancelled_to_customer');
                    $content = $layout->render();

                    $mail = new Zend_Mail('UTF-8');
                    $mail->setBodyHtml($content);
                    $mail->setFrom($order->getStore()->getEmail(), $this->_('%s - Customer Service', $order->getStore()->getName()));
                    $mail->addTo($order->getCustomerEmail(), $order->getCustomerFirstname() . ' ' . $order->getCustomerLastname());
                    $mail->setSubject($this->_('Order %s cancelled', $order->getNumber()));
                    $mail->send();
                }

                $html = array(
                    'success' => '1',
                    'order_id' => $order->getId(),
                    'success_message' => $this->_('Order successfully saved'),
                    'message_timeout' => 2,
                    'message_button' => 0,
                    'message_loader' => 0
                );


                $html['status'] = $order->getStatus();
                $html['customer_name'] = $order->getCustomerFirstname() . ' ' . $order->getCustomerLastname();

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