<?php

/**
 * Class Siberian_Currency
 *
 * @description Utility class for pricing.
 *
 */
class Siberian_Currency
{

    /**
    public static function truncatePrice($price, $decimals = 2 ) {
        return number_format(floor(($price * pow(10, $decimals))) / pow(10, $decimals), $decimals);
    }

    public static function toPaypal($price, $decimals = 2 ) {
        return self::preFormat($price, $decimals);
    }

    public static function preFormat($price, $decimals = 2 ) {
        return number_format(preg_replace("#\.*0+$#", "", $price), $decimals);
    }

    public static function getPriceExclVat($price, $decimals = 2) {
        return self::truncatePrice($price, $decimals);
    }*/

    public static function getVat($priceEclxVat, $vatRate) {
        return ($priceEclxVat * $vatRate / 100);
    }

    public static function addVat($priceExclVat, $vatRate) {
        return $priceExclVat * (1 + ($vatRate / 100));
    }
}
