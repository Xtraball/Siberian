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
        $price = preg_replace(['/(,)/', '/[^0-9.-]/'], ['.', ''], $price);

        $currency = ($currency) ? new Zend_Currency($currency) : Core_Model_Language::getCurrentCurrency();

        return $currency->toCurrency($price);
    }

    /**
     * @param $deliveryTime
     * @return bool|string
     */
    public static function getHumanDeliveryTime ($deliveryTime)
    {
        $textualDeliveryTime = false;
        if ($deliveryTime > 0) {

            $days = floor($deliveryTime / 1440);
            $hours = floor(($deliveryTime % 1440) / 60);
            $minutes = floor(($deliveryTime % 1440 % 60));

            $textualDeliveryTime = '';

            if ($days == 1) {
                $textualDeliveryTime .= ' ' . p__('m_commerce', '%s day', 1);
            } else if ($days > 1) {
                $textualDeliveryTime .= ' ' . p__('m_commerce', '%s days', $days);
            }

            if ($hours == 1) {
                $textualDeliveryTime .= ' ' . p__('m_commerce', '%s hour', 1);
            } else if ($hours > 1) {
                $textualDeliveryTime .= ' ' . p__('m_commerce', '%s hours', $hours);
            }

            if ($minutes == 1) {
                $textualDeliveryTime .= ' ' . p__('m_commerce', '%s minute', 1);
            } else if ($minutes > 1) {
                $textualDeliveryTime .= ' ' . p__('m_commerce', '%s minutes', $minutes);
            }

            $textualDeliveryTime = trim($textualDeliveryTime);
        }

        return $textualDeliveryTime;
    }

}