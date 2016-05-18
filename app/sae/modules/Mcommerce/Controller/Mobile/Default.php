<?php

class Mcommerce_Controller_Mobile_Default extends Application_Controller_Mobile_Default {

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
     * Initialise le panier et le magasin en cours de session
     *
     * @return Mcommerce_Mobile_CartController
     */
    public function init() {

        $logger = Zend_Registry::get("logger");

        parent::init();

        $this->_current_option_value = $this->getApplication()->getPage('m_commerce');

        $store = new Mcommerce_Model_Store();
        if($this->getSession()->store_id) {
            $store->find($this->getSession()->store_id);
        }

        if(!$store->getId()) {
            $store = $this->getCurrentOptionValue()->getObject()->getDefaultStore();
//            if(!$store->getId()) {
//                $this->_forward('noroute');
//                return $this;
//            }
            $this->getSession()->setStore($store);
        }
        $this->_store = $store;

        $cart = $this->getSession()->getCart();
        if(!$cart->getId() AND $store->getId()) {
            
            $logger->debug("Create new cart in session.");
            
            $cart->setMcommerceId($this->getCurrentOptionValue()->getObject()->getId())
                ->setStoreId($store->getId())
                ->save()
            ;
            $this->getSession()->setCart($cart);
        }else{
            $logger->debug("Cart already exists: " . print_r($cart, true));
        }
        $this->_cart = $cart;

        return $this;
    }

    /**
     * Récupère le panier en cours de session
     *
     * @return Mcommerce_Model_Cart
     */
    public function getCart() {
        return $this->_cart;
    }

    /**
     * Récupère le magasin en cours de session
     *
     * @return Mcommerce_Model_Store
     */
    public function getStore() {
        return $this->_store;
    }

}
