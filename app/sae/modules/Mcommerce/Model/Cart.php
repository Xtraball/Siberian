<?php

class Mcommerce_Model_Cart extends Core_Model_Default {

    /**
     * Store du panier
     *
     * @var Mcommerce_Model_Cart
     */
    protected $_store;

    /**
     * Mode de livraison
     *
     * @var Mcommerce_Model_Delivery_Method
     */
    protected $_delivery_method;

    /**
     * Mode de paiement
     *
     * @var Mcommerce_Model_Payment_Method
     */
    protected $_payment_method;

    /**
     * Lignes du panier
     *
     * @var array
     */
    protected $_lines;

    /**
     * Commande associée au panier
     *
     * @var Mcommerce_Model_Order
     */
    protected $_order;

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Mcommerce_Model_Db_Table_Cart';
        return $this;
    }

    /**`
     * Ajoute un produit au panier
     *
     * @param type Catalog_Model_Product
     * @return Mcommerce_Model_Cart
     */
    public function addProduct($product) {
        $line_id = null;

        foreach($this->getLines() as $line) {

            $lines_ids = array();
            $products_ids = array();

            if ($line->getProductId() == $product->getId()) {

                if (count($line->getOptions()) == count($product->getOptions())) {

                    foreach ($line->getOptions() as $line_option) {
                        $lines_ids[$line_option->getOptionId()] = $line_option->getQty();
                    }

                    foreach ($product->getOptions() as $product_option) {
                        $products_ids[$product_option->getValueId()] = $product_option->getQty();
                    }

                }

            }

        }

        if($line_id) {
            $this->updateQty($line_id, $this->getLine($line_id)->getQty()+$product->getQty());
        }
        else {
            $line = $this->_createLine($product);
            $line->save();
            $this->addLine($line);
            $this->_compute();
        }

        return $this;
    }

    /**
     * Supprime un produit du panier
     *
     * @param int $line_id
     * @return Mcommerce_Model_Cart
     */
    public function removeProduct($line_id) {

        if($line = $this->getLine($line_id)) {
            $line->delete();
            $this->_lines = null;
            $this->_compute();
        }

        return $this;
    }

    /**
     * Met à jour la quantité d'un produit
     *
     * @param int $line_id
     * @param decimal $qty
     * @return Mcommerce_Model_Cart
     */
    public function updateQty($line_id, $qty) {

        if($line = $this->getLine($line_id)) {
            $line->setQty($qty)->calcTotal()->save();
            $this->_compute();
        }

        return $this;
    }

    /**
     * Ajoute une ligne en mémoire
     *
     * @param Mcommerce_Model_Cart_Line $line
     * @return Mcommerce_Model_Cart
     */
    public function addLine($line) {

        if(!$this->getLine($line->getId())) {
            $this->getLines()->addRow(-1, $line);
        }

        return $this;
    }

    /**
     * Supprime une ligne en mémoire
     *
     * @return \Mcommerce_Model_Cart
     */
    public function removeLine($line_id) {

        foreach($this->getLines() as $pos => $line) {
            if($line->getId() == $line_id) $this->getLines()->removeRow($pos);
        }

        return $this;
    }

    /**
     * Récupère une ligne en mémoire
     *
     * @param type $line_id
     * @return mixed null | Mcommerce_Model_Cart_Line
     */
    public function getLine($line_id) {
        $line = $this->getLines()->findById($line_id);

        return $line->getId() ? $line : null;
    }

    /**
     * Récupère les lignes du panier en base
     *
     * @return Siberian_Db_Table_Rowset Collection de lignes du panier
     */
    public function getLines() {

        if(!$this->_lines) {
            $line = new Mcommerce_Model_Cart_Line();
            $this->_lines = $line->findAll(array('cart_id' => $this->getId()));
        }

        return $this->_lines;
    }

    /**
     * Créer une ligne de panier
     *
     * @param Catalog_Model_Product $product
     * @return Mcommerce_Model_Cart_Line
     */
    protected function _createLine($product) {
        $options_datas = array();

        if($product->getOptions()) {
            foreach($product->getOptions() as $option) {

                /** @wip #1688 */
                $price = $option->getPrice();
                $vat = Siberian_Currency::getVat($option->getPrice(), $product->getTaxRate());
                $priceInclTax = $option->getPrice() + $vat;

                $options_datas[] = array(
                    'option_id'         => $option->getId(),
                    'name'              => $option->getName(),
                    'base_price'        => $priceInclTax,
                    'price'             => $price * $option->getQty(),
                    'price_incl_tax'    => $priceInclTax * $option->getQty(),
                    'qty'               => $option->getQty()
                );
            }
        }

        $product_format = null;
        if($format = $product->getFormat()) {
            $product_format = array(
                "id"    => $format->getOptionId(),
                "title" => $format->getTitle(),
                "price" => $format->getPrice()
            );
            $product->setPrice($format->getPrice());
        }

        /** @wip #1688 */
        $price = $product->getPrice();
        $vat = Siberian_Currency::getVat($product->getPrice(), $product->getTaxRate());
        $priceInclTax = $product->getPrice() + $vat;

        $line = new Mcommerce_Model_Cart_Line();
        $line->setCartId($this->getId())
            ->setProductId($product->getId())
            ->setCategoryId($product->getCategoryId())
            ->setRef($product->getRef())
            ->setName($product->getName())
            ->setBasePrice($price)
            ->setBasePriceInclTax($priceInclTax)
            ->setQty($product->getQty() ? $product->getQty() : 1)
            ->setOptions(serialize($options_datas))
            ->setChoices(serialize($product->choices))
            ->setFormat(serialize($product_format))
            ->setTaxId($product->getTaxId())
            ->setTaxRate($product->getTaxRate())
        ;


        $line->calcTotal();

        return $line;
    }

    /**
     * Ajoute le mode de livraison au panier
     *
     * @param int $delivery_method_id
     * @return $this
     */
    public function setDeliveryMethodId($delivery_method_id) {

        $delivery_method = $this->getStore()->getDeliveryMethod($delivery_method_id);
        if($delivery_method->getStoreDeliveryMethodId()) {
            $this->setData('delivery_method_id', $delivery_method->getId());
            $this->_compute();
        }

        return $this;
    }

    public function getSubtotalInclTax() {
        $total = 0;
        foreach($this->getLines() as $line) {
            $total += $line->getPriceInclTax() * $line->getQty();
        }

        //setting it to use getFormatted magic call
        $this->setSubtotalInclTax($total);

        return $total;
    }

    public function getDeliveryCostInclTax() {

        $delivery_cost = $this->getDeliveryCost();

        if($delivery_cost > 0) {
            $delivery_cost = $delivery_cost * (1+$this->getDeliveryTaxRate()/100);
        }

        return $delivery_cost;

    }

    /**
     * Vérifie que l'utilisateur à bien été authentifié avant/pendant l'achat
     * Used to verify if the customer_id has been set, but was disabled in order to continue supporting legacy applications
     *
     * @return array
     */
    public function check() {
        $errors = array();
        // if (!$this->getCustomerId()) $errors[] = $this->_('You did not login.');
        return $errors;
    }

    /**
     * Récupère le store du panier
     *
     * @return Mcommerce_Model_Store
     */
    public function getStore() {

        if(!$this->_store) {
            $this->_store = new Mcommerce_Model_Store();
            $this->_store->find($this->getStoreId());
        }

        return $this->_store;

    }

    /**
     * Récupère le mode de livraison
     *
     * @return Mcommerce_Model_Delivery_Method
     */
    public function getDeliveryMethod() {

        if(!$this->_delivery_method) {
            $this->_delivery_method = new Mcommerce_Model_Delivery_Method();
            $this->_delivery_method->find($this->getDeliveryMethodId());
            $this->_delivery_method->setCart($this);
        }

        return $this->_delivery_method;

    }

    /**
     * Récupère la commande associée au panier
     *
     * @return Mcommerce_Model_Order
     */
    public function getOrder() {

        if(!$this->_order) {
            $this->_order = new Mcommerce_Model_Order();
            $this->_order->find($this->getId(), 'cart_id');
            $this->_order->setCart($this);
        }

        return $this->_order;

    }

    /**
     * Récupère le mode de livraison
     *
     * @return Mcommerce_Model_Payment_Method
     */
    public function getPaymentMethod() {

        if(!$this->_payment_method) {
            $this->_payment_method = new Mcommerce_Model_Payment_Method();
            $this->_payment_method->find($this->getPaymentMethodId());
            $this->_payment_method->setCart($this)->setStoreId($this->getStoreId());
        }

        return $this->_payment_method;

    }

    /**
     * Récupère l'url de paiement
     *
     * @return string
     */
    public function getPaymentUrl() {
        return $this->getPaymentMethod()->getUrl();
    }

    /**
     * Calcule l'ensemble des montants du panier
     *
     * @return Mcommerce_Model_Cart
     */
    public function _compute() {
        //note : subtotal_ is with delivery // $toal is with delivery
        list($subtotal_excl_tax, $delivery_cost_excl_tax, $total_tax) = $this->getTotalsFromLines();
        $tip = $this->getTip();
        $total_excl_tax = $subtotal_excl_tax + $delivery_cost_excl_tax;
        $total = $total_excl_tax + $total_tax + $tip;

        $this->setSubtotalExclTax($subtotal_excl_tax)
            ->setTotalExclTax($total_excl_tax)
            ->setTotalTax($total_tax)
            ->setTotal($total);

        return $this;
    }

    /**
     * Returns the corresponding mcommerce object
     *
     * @return Mcommerce_Model_Mcommerce
     */
    public function getMcommerce() {
        $mcommerce = new Mcommerce_Model_Mcommerce();
        $mcommerce->find(array("mcommerce_id" => $this->getMcommerceId()));
        return $mcommerce;
    }

    /**
     * Given a customer address, find the customer coordinates (used to set the `longitude` and `latitude` properties of the cart).
     * These coordinates are impportant as they are needed in the delivery.
     * The address consists of an array like: array('street' => ..., 'postcode' => ..., 'city' => ...)
     *
     * @param $address_parts
     * @return $this
     */
    public function setLocation($address_components) {
        if (!empty($address_components['street']) AND !empty($address_components['postcode']) AND !empty($address_components['city'])) {
            $address = join(', ', array(
                $address_components['street'],
                $address_components['postcode'],
                $address_components['city']
            ));

            $address = str_replace(' ', '+', $address);
            $url = "https://maps.googleapis.com/maps/api/geocode/json?sensor=false&address=$address";
            if ($results = @file_get_contents($url) AND $results = @json_decode($results)) {
                if (!empty($results->results[0]->geometry->location)) {
                    $cordinates = $results->results[0]->geometry->location;
                    if (!empty($cordinates->lat) && !empty($cordinates->lng)) {
                        $this->setCustomerLatitude($cordinates->lat);
                        $this->setCustomerLongitude($cordinates->lng);
                    }
                }
            }
        }
        return $this;
    }

    /**
     * Calcule le total hors tax, cout de livraison, et le total des taxes
     *
     * @return array
     */
    public function getTotalsFromLines(){
        $subtotal_excl_tax = 0;
        $total_tax = 0;
        $delivery_cost = 0;
        $delivery_cost_excl_tax = 0;
        $delivery_tax = 0;
        $delivery_tax_rate = 0;

        foreach($this->getLines() as $line) {
            /** @wip #1688 */
            $subtotal_excl_tax += $line->getTotal();
            $total_tax += $line->getTotalInclTax() - $line->getTotal();
        }

        $total_incl_tax = $subtotal_excl_tax + $total_tax;

        if($this->getDeliveryMethodId()) {
            $delivery_method = $this->getStore()->getDeliveryMethod($this->getDeliveryMethodId());
            $tax = $this->getStore()->getTax($delivery_method->getTaxId());
            $delivery_cost = $delivery_method->getPrice();
            if($delivery_method->getMinAmountForFreeDelivery() AND $total_incl_tax >= $delivery_method->getMinAmountForFreeDelivery()) {
                $delivery_cost = 0;
            } else if($tax->getId()) {
                $delivery_tax_rate = $tax->getRate();
                if($delivery_cost > 0) {
                    $delivery_tax = $delivery_cost - ($delivery_cost / (1+$delivery_tax_rate/100));
                    $delivery_cost_excl_tax = $delivery_cost - $delivery_tax;
                }
            }

            $this->setDeliveryCost($delivery_cost_excl_tax)
                ->setDeliveryTaxRate($delivery_tax_rate)
            ;

            $total_tax += $delivery_tax;
        }
        return array($subtotal_excl_tax, $delivery_cost_excl_tax, $total_tax);
    }

    public function getDeductedTva() {
        if($this->getDiscountCode()) {
            $promo = new Mcommerce_Model_Promo();
            $promo->find($this->getDiscountCode(),"code");

            $ttc = array_sum($this->getTotalsFromLines());
            $reductedttc = round($ttc - $promo->getDeduction($this),2);
            $ratio = round(1 - ($reductedttc/$ttc),2);
            return round($this->getTotalTax() - $this->getTotalTax() * $ratio,2);
        } else {
            return $this->getTotalTax();
        }
    }

    public function getFormattedDeductedTva() {
        return $this->formatPrice($this->getDeductedTva());
    }

    public function getDeductedTotalHT() {
        return $this->getTotal() - $this->getTip() -$this->getDeductedTva();
    }

    public function getFormattedDeductedTotalHT() {
        return $this->formatPrice($this->getDeductedTotalHT());
    }

    public function getFormattedSubtotal() {
        return $this->formatPrice($this->getSubtotalInclTax() + $this->getDeliveryTTC());
    }

    public function getDeliveryTTC() {
        return $this->getDeliveryCost() * (1 + $this->getDeliveryTaxRate()/100);
    }

    public function getFormattedDeliveryTTC() {
        return $this->formatPrice($this->getDeliveryTTC());
    }

    public function getFormattedSubtotalWithDiscount() {
        return $this->formatPrice(($this->getDeductedTotalHT()+$this->getDeductedTva()));
    }

    public function getTip() {
        return abs(floatval($this->getData("tip")));
    }

}
