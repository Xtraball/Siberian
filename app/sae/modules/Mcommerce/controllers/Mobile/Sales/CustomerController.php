<?php

class Mcommerce_Mobile_Sales_CustomerController extends Mcommerce_Controller_Mobile_Default
{

    public function updateAction()
    {

        if ($data = Zend_Json::decode($this->getRequest()->getRawBody())) {

            $html = array();

            try {
                $option = $this->getCurrentOptionValue();
                $mcommerce = $option->getObject();
                $cart = $this->getCart();

                $errors = $mcommerce->validateCustomer($this, $data['form']['customer']);

                if (!empty($errors)) {
                    $message = $this->_('Please fill in the following fields:');
                    foreach ($errors as $field) {
                        $message .= '<br />- ' . $field;
                    }
                    throw new Exception($this->_($message));
                }
                $info = array("customer_id" => $data['form']['customer']['id']);
                $this->getCart()->addData($info)->save();

                $html = array(
                    'customer' => Mcommerce_Model_Customer::getCleanInfos($mcommerce, $data['form']['customer']),
                    'cartId' => $cart->getId()
                );
            } catch (Exception $e) {
                $html = array(
                    'error' => 1,
                    'message' => $e->getMessage()
                );
            }

            $this->_sendHtml($html);
        }

    }

    public function findAction()
    {
        $option = $this->getCurrentOptionValue();
        $mcommerce = $option->getObject();

        $customer = $this->getSession()->getCustomer();
        $data = array();

        if ($customer->getId()) {
            $metadatas = $customer->getMetadatas();
            if (empty($metadatas))
                $metadatas = json_decode("{}"); // we really need a javascript object here

            $data["customer"] = array(

                "id" => $customer->getId(),
                "civility" => $customer->getCivility(),
                "firstname" => $customer->getFirstname(),
                "lastname" => $customer->getLastname(),
                "nickname" => $customer->getNickname(),
                "email" => $customer->getEmail(),
                "show_in_social_gaming" => (bool)$customer->getShowInSocialGaming(),
                "is_custom_image" => (bool)$customer->getIsCustomImage(),
                "metadatas" => $metadatas
            );
            $data['settings'] = $mcommerce->getSettings();
        }

        $this->_sendHtml($data);

    }

}