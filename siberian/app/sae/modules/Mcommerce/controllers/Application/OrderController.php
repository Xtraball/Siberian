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
                throw new Exception(__('An error occurred during the process. Please try again later.'));
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
                        throw new Exception(__('An error occurred while saving. Please try again later.'));
                    }
                }

                if ($order->getCustomerId()) {
                    $errors = $mcommerce->validateCustomer($this, $datas['customer']);
                } else {
                    $errors = $mcommerce->validateLegacyCustomer($this, $datas['customer']);
                }

                if (!empty($errors)) {
                    $message = __('Please fill in the following fields:');
                    foreach ($errors as $field) {
                        $message .= '<br />- ' . $field;
                    }
                    throw new Exception(__($message));
                }

                if ($order->getCustomerId()) {
                    $order->getCustomer()->populate($mcommerce, $datas['customer'])->save();
                    $order->addData(array("status_id" => $datas["status_id"]))->save();
                } else {
                    $order->addData(array(
                        "status_id" => $datas["status_id"],
                        "customer_firstname" => $datas["customer"]['firstname'],
                        "customer_lastname" => $datas["customer"]['lastname'],
                        "customer_email" => $datas["customer"]['email'],
                        "customer_phone" => $datas["customer"]['phone'],
                        "customer_street" => $datas["customer"]['street'],
                        "customer_postcode" => $datas["customer"]['postcode'],
                        "customer_city" => $datas["customer"]['city']
                    ))->save();
                }

                if($order->getStatusId() == Mcommerce_Model_Order::CANCEL_STATUS) {
                    $layout = $this->getLayout()->loadEmail('mcommerce', 'send_order_cancelled_to_customer');
                    $content = $layout->render();

                    # @version 4.8.7 - SMTP
                    $mail = new Siberian_Mail();
                    $mail->setBodyHtml($content);
                    $mail->setFrom($order->getStore()->getEmail(), __('%s - Customer Service', $order->getStore()->getName()));
                    $mail->addTo($order->fetchCustomerName(), $order->fetchCustomerName());
                    $mail->setSubject(__('Order %s cancelled', $order->getNumber()));
                    $mail->send();
                }

                $html = array(
                    'success' => '1',
                    'order_id' => $order->getId(),
                    'success_message' => __('Order successfully saved'),
                    'message_timeout' => 2,
                    'message_button' => 0,
                    'message_loader' => 0
                );


                $html['status'] = $order->getStatus();
                $html['customer_name'] = $order->fetchCustomerName();

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