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
                /* Either `legacy` or `current` */
                $version = $this->_getVersion($data);

                if ($version == "legacy") {
                    $errors = $mcommerce->validateLegacyCustomer($this, $data['form']['customer']);
                } else {
                    $errors = $mcommerce->validateCustomer($this, $data['form']['customer']);
                }

                if (!empty($errors)) {
                    $message = $this->_('Please fill in the following fields:');
                    foreach ($errors as $field) {
                        $message .= '<br />- ' . $field;
                    }
                    throw new Exception($this->_($message));
                }

                $info = array();
                if ($version == "legacy") {
                    $cart->setLocation(array(
                        'street' => $data['form']['customer']['street'],
                        'postcode' => $data['form']['customer']['postcode'],
                        'city' => $data['form']['customer']['city']
                    ));
                    $info = $this->_getCartData($data['form']['customer']);
                } else {
                    $cart->setLocation($data['form']['customer']['metadatas']['delivery_address']);
                    $info = array("customer_id" => $data['form']['customer']['id']);
                }

                $cart->addData($info)->save();

                $html = array(
                    'customer' => $version == "legacy" ? $data['form']['customer'] : Mcommerce_Model_Customer::getCleanInfos($mcommerce, $data['form']['customer']),
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

    public function hasguestmodeAction()
    {

        $html = array();

        try {
            $option = $this->getCurrentOptionValue();
            $mcommerce = $option->getObject();

            $html = array(
                'success' => 1,
                'activated' =>$mcommerce->getGuestMode()
            );

        } catch (Exception $e) {
            $html = array(
                'error' => 1,
                'message' => $e->getMessage()
            );
        }

        $this->_sendHtml($html);

    }

    /**
     * If the request is made from legacy application return "legacy"
     * If the request is made from current application return "current"
     * NB: Legacy application saves fields directly to the order, current application saves customer data to seperate customer entity.
     *
     * @param $data
     * @return string
     */
    protected function _getVersion($data) {
        $validator = new Zend_Validate_Int();
        if ($validator->isValid($data['form']['customer']['id'])) {
            return "current";
        } else {
            return "legacy";
        }
    }

    /**
     * Returns prepared cart data if it consists of a legacy application.
     *
     * @param array $data
     * @return Mcommerce_Model_Cart
     */
    protected function _getCartData($customer) {
        return array(
            "customer_firstname" => $customer["firstname"],
            "customer_lastname" => $customer["lastname"],
            "customer_email" => $customer["email"],
            "customer_phone" => $customer["phone"],
            "customer_email" => $customer["email"],
            "customer_street" => $customer["street"],
            "customer_postcode" => $customer["postcode"],
            "customer_city" => $customer["city"],
            "customer_birthday" => $customer["birthday"]
        );
    }

}