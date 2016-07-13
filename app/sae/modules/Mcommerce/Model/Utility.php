<?php

class Mcommerce_Model_Utility {

    static public function addTax($price_excl_tax, $tax_rate) {
        return ($price_excl_tax * (1 + $tax_rate / 100));
    }

    /**
     * Decide whether the display price (with tax) should be rounded, or truncated
     */
    static public function displayPrice($price_excl_tax, $tax_rate, $quantity = 1, $decimals = 2) {
        $price_incl_tax = self::addTax($price_excl_tax, $tax_rate);
        $price_incl_tax_tenth = $price_incl_tax * 10;

        /** add tax on total */
        $price_tenth_incl_tax = self::addTax($price_excl_tax * 10, $tax_rate);
        $difference = abs($price_tenth_incl_tax - $price_incl_tax_tenth);

        $price_with_quantity = $price_excl_tax * $quantity;
        $price_with_quantity_incl_tax = self::addTax($price_with_quantity, $tax_rate);

        if($difference <= 0.03 && $difference > 0) {
            return self::formatCurrency(self::truncatePrice($price_with_quantity_incl_tax, $decimals));
        }
        return self::formatCurrency(self::roundPrice($price_with_quantity_incl_tax, $decimals));
    }

    static public function truncatePrice($price, $decimals = 2) {
        return number_format(floor(($price * pow(10, $decimals))) / pow(10, $decimals), $decimals, '.', '');
    }

    static public function roundPrice($price, $decimals = 2) {
        return round($price, $decimals);
    }

    static public function formatCurrency($price, $currency = null) {
        $price = preg_replace(array('/(,)/', '/[^0-9.-]/'), array('.', ''), $price);

        $currency = ($currency) ? new Zend_Currency($currency) : Core_Model_Language::getCurrentCurrency();

        return $currency->toCurrency($price);
    }

}