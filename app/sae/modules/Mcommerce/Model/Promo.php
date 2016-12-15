<?php

class Mcommerce_Model_Promo extends Core_Model_Default {

    public function __construct($datas = array()) {
        parent::__construct($datas);
        $this->_db_table = 'Mcommerce_Model_Db_Table_Promo';
    }

    /**
     * Returns the promo applicable to the cart(if applicable)
     * The cart must have a discount code
     *
     * @param Mcommerce_Model_Cart $cart
     * @return Mcommerce_Model_Promo
     */
    public static function getApplicablePromo($cart) {
        $promo = new Mcommerce_Model_Promo();
        // 1st condition: The code must be correct
        $promo = $promo->find(array('code' => $cart->getDiscountCode(), 'mcommerce_id' => $cart->getMcommerceId()));

        if ($promo->getPromoId()) {
            return $promo;
        }

        return null;
    }

    /**
     * Deduces discount from cart total
     *
     * @param Mcommerce_Model_Cart $cart
     * @return $this
     */
    public function applyOn($cart) {
        $cart->setTotal(array_sum($cart->getTotalsFromLines()) + $cart->getTip() - $this->getDeduction($cart));
        $cart->save();
        return $this;
    }

    /**
     * returns the amount of money to be deduced from the cart TTC
     *
     * @param $cart
     * @return float
     */
    public function getDeduction($cart) {
        $ttc = array_sum($cart->getTotalsFromLines());
        if ($this->getType() == "percentage") {
            return $this->getDiscount() * $ttc / 100;
        } else {
            return min(array($this->getDiscount(), $ttc));
        }
    }

    /**
     * Returns true if the cart is illegible to a discount
     * Verifies the amount, the validity date, and used once
     *
     * @param Mcommerce_Model_Cart $cart
     * @return bool
     */
    public function validate($cart) {
        // 2nd condition: The TTC amount must be more or equal to the minimum amount required by the promotion
        if (array_sum($cart->getTotalsFromLines()) < $this->getMinimumAmount()) {
            return -1;
        }
        // 3rd condition: At the time of discount request, we should not exceed the validity date
        if (strtotime($this->getValidUntil()) < time()) {
            return -2;
        }
        // 4th condition: If usable once, the user can use the code only once
        if ($this->getUseOnce() && $this->getPreviousCodeUses($cart) > 0) {
            return -3;
        }
        return 1;
    }

    /**
     * Get the old code uses (if exist)
     *
     * @param $cart
     * @return int
     */
    public function getPreviousCodeUses($cart) {
        $log = new Mcommerce_Model_Promo_Log();
        $logs = $log->findAll(array(
            'promo_id' => $this->getPromoId(),
            'customer_uuid' => $cart->getCustomerUUID()
        ));
        return sizeof($logs);
    }

    /**
     * Verifies that there are no other discounts with the same code
     *
     * @return $this
     * @throws Exception
     */
    public function verifyUnique() {
        $table = new Mcommerce_Model_Db_Table_Promo();
        $select = $table->select(Zend_Db_Table::SELECT_WITH_FROM_PART);
        $select
            ->setIntegrityCheck(false)
            ->where('mcommerce_promo.mcommerce_id = ?', $this->getMcommerceId())
            ->where('mcommerce_promo.code = ?', $this->getcode());

        if($this->getPromoId()) {
            $select->where('mcommerce_promo.promo_id != ?', $this->getPromoId());
        }

        $rows = $table->fetchAll($select);
        if (sizeof($rows) > 0) {
            throw new Exception("Discount code already exists");
        }
        return $this;
    }
}