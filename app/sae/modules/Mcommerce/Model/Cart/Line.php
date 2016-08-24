<?php

class Mcommerce_Model_Cart_Line extends Core_Model_Default {

    /**
     * @var Catalog_Model_Product Produit du catalogue
     */
    protected $_product;

    /**
     * @var array Options de la ligne
     */
    protected $_options;

    /**
     * @var array Format de la ligne
     */
    protected $_format;

    /**
     * @var Mcommerce_Model_Cart Panier
     */
    protected $_cart;

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Mcommerce_Model_Db_Table_Cart_Line';
        return $this;
    }

    public function getQty() {
        $qty = 0;
        if($this->getData('qty')) {
            $qty = round($this->getData('qty'));
        }

        return $qty;
    }

    public function isRecurrent() {
        return false;
    }

    public function calcTotal() {

        $unitPrice = $this->getBasePrice();

        foreach($this->getOptions() as $option) {
            $unitPrice += $option->getPrice();
        }

        /** @wip #1688 */
        $price = $unitPrice * $this->getQty();
        $vat = Siberian_Currency::getVat($unitPrice, $this->getTaxRate());
        $unitPriceInclTax = $unitPrice + $vat;

        /** @wip #1688 */
        $total = $price;
        $vat = Siberian_Currency::getVat($price, $this->getTaxRate());
        $totalInclTax = $total + $vat;

        $this->setPrice($unitPrice)
            ->setPriceInclTax($unitPriceInclTax)
            ->setTotal($total)
            ->setTotalInclTax($totalInclTax)
            ->setPricecomputed("yes")
        ;

        return $this;
    }

    public function getProduct() {

        if(!$this->_product) {

            $this->_product = new Catalog_Model_Product();
            if($this->getProductId()) {
                $this->_product->find($this->getProductId());
            }
        }

        return $this->_product;
    }

    public function getCart() {

        if(!$this->_cart) {

            $this->_cart = new Mcommerce_Model_Cart();
            if($this->getCartId()) {
                $this->_cart->find($this->getCartId());
            }
        }

        return $this->_cart;
    }

    /** @note should avoid usage of @ to suppress warning/notice */
    public function getOptions() {

        if(!$this->_options) {
            $this->_options = array();
            if($this->getData('options')) {
                $options = @unserialize($this->getData('options'));
                if(is_array($options)) {
                    foreach($options as $option_datas) {
                        $this->_options[] = new Core_Model_Default($option_datas);
                    }
                }
            }
        }

        return $this->_options;
    }

    public function getChoices() {

        if(!$this->_choices) {
            $this->_choices = @unserialize($this->getData('choices'));
        }

        return $this->_choices;
    }

    /** @note should avoid usage of @ to suppress warning/notice */
    public function getFormat() {

        if(!$this->_format AND $this->getData('format')) {
            $format = @unserialize($this->getData('format'));
            $this->_format = new Core_Model_Default($format);
        }

        return $this->_format;
    }
}
