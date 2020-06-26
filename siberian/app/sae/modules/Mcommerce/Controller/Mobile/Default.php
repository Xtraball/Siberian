<?php

class Mcommerce_Controller_Mobile_Default extends Application_Controller_Mobile_Default
{

    /**
     * Panier en cours
     *
     * @var Mcommerce_Model_Cart
     */
    protected $_cart;

    /**
     * Boutique en cours
     *
     * @var Mcommerce_Model_Store
     */
    protected $_store;

    /**
     * @return $this|Application_Controller_Mobile_Default|Core_Controller_Default|void
     * @throws Zend_Exception
     * @throws Zend_Session_Exception
     * @throws \Siberian\Exception
     */
    public function init()
    {
        parent::init();

        $logger = Zend_Registry::get('logger');

        $request = $this->getRequest();
        $application = $this->getApplication();
        $session = $this->getSession();

        $this->_current_option_value = $application->getPage('m_commerce');

        $store = new Mcommerce_Model_Store();
        if ($session->store_id) {
            $store->find($session->store_id);
        }

        if (!$store->getId()) {
            $store = $this->getCurrentOptionValue()->getObject()->getDefaultStore();
            $session->setStore($store);
        }
        $this->_store = $store;

        $uriCartId = $request->getParam('cart_id', false);
        $cart = (new Mcommerce_Model_Cart())->find($uriCartId);

        if ($cart->getId()) {
            $session->setCart($cart);
        } else if (!$session->getCart()->getId() && $store->getId()) {
            $cart = $session->getCart();

            $logger->debug('Create new cart in session.');

            $cart
                ->setMcommerceId($this->getCurrentOptionValue()->getObject()->getId())
                ->setStoreId($store->getId())
                ->save();
            $session->setCart($cart);
        } else {
            // Adding a condition for when the cart has already been validated.
            // We have to check if an order which corresponds to the cart has been saved.
            // In the latter case we create a new cart, to avoid carrying cart lines from old purshases.
            if ($this->cartAlreadyValidated()) {
                $logger->debug('Create new cart in session upon cart validation.');
                $cart = new Mcommerce_Model_Cart();
                $cart
                    ->setMcommerceId($this->getCurrentOptionValue()->getObject()->getId())
                    ->setStoreId($store->getId())
                    ->save();
                $session->setCart($cart);
            } else {
                $cart = $session->getCart();
                $logger->debug('Cart already exists.');
            }
        }

        $this->_cart = $cart;

        return $this;
    }

    /**
     * @return int
     * @throws Zend_Session_Exception
     */
    protected function cartAlreadyValidated()
    {
        $cart = $this->getSession()->getCart();
        $order = new Mcommerce_Model_Order();
        $order->find([
            'cart_id' => $cart->getCartId()
        ]);

        return ($order->getId());
    }

    /**
     * Récupère le panier en cours de session
     *
     * @return Mcommerce_Model_Cart
     */
    public function getCart()
    {
        return $this->_cart;
    }

    /**
     * Récupère le magasin en cours de session
     *
     * @return Mcommerce_Model_Store
     */
    public function getStore()
    {
        return $this->_store;
    }

    protected function computeDiscount()
    {
        $cart = $this->getCart();
        $cart->setCustomerUUID($this->getRequest()->getParam("customer_uuid", ""));
        $promo = Mcommerce_Model_Promo::getApplicablePromo($cart);
        $result = ['success' => false];
        if ($promo) {
            $valid = $promo->validate($cart);
            $result['show_message'] = true;
            if ($valid == -1) {
                $result['message'] = $this->_("Discount only for carts with total more than: ") . $promo->getMinimumAmount();
            } else if ($valid == -2) {
                $result['message'] = $this->_("Discount no longer available");
            } else if ($valid == -3) {
                $result['message'] = $this->_("You used the code before");
            } else {
                $promo->applyOn($cart);
                $result['show_message'] = false;
                $result['success'] = true;
                $result['discount'] = $promo->formatPrice($promo->getDeduction($cart));
                $result['message'] = $promo->getLabel();
            }
        } else {
            $result['message'] = $this->_("Invalid code") . '(' . $cart->getDiscountCode() . ')';
        }
        // If discount failed, re-apply original prices
        if (!$result['success']) {
            $cart->_compute()->setDiscountCode("")->save();
        }
        return $result;
    }

    /**
     * @return Mcommerce_Model_Promo|null
     */
    protected function getPromo()
    {
        $cart = $this->getCart();
        $promo = Mcommerce_Model_Promo::getApplicablePromo($cart);
        if ($promo) {
            $valid = $promo->validate($cart);
            if ($valid > 0) {
                return $promo;
            }
        }
        return null;
    }
}
