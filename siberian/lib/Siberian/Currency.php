<?php

namespace Siberian;

/**
 * Class \Siberian\Currency
 *
 * @description Utility class for pricing.
 *
 */
class Currency
{

    /**
     * @var null
     */
    public static $jsonSource = null;

    /**
     * @param bool $withStripeSuffix
     * @return array|false
     */
    public static function getAllCurrencies ($withStripeSuffix = false)
    {
        if (self::$jsonSource === null) {
            $contents = file_get_contents(path(self::$configFile));
            self::$jsonSource = Json::decode($contents);
        }

        $stripeSuffix = $withStripeSuffix ? " (Stripe)" : "";

        $common = array_keys(self::$jsonSource);
        $commonCurrencies = array_combine($common, $common);
        foreach (self::$supported as $stripeSupported) {
            $commonCurrencies[$stripeSupported] = "{$stripeSupported}{$stripeSuffix}";
        }

        ksort($commonCurrencies);

        return $commonCurrencies;
    }

    /**
     * @param $code
     * @return mixed
     * @throws Exception
     */
    public static function getCurrency ($code)
    {
        if (self::$jsonSource === null) {
            $contents = file_get_contents(path(self::$configFile));
            self::$jsonSource = Json::decode($contents);
        }

        if (array_key_exists($code, self::$jsonSource)) {
            return self::$jsonSource[$code];
        }

        throw new Exception(p__("payment_stripe", "Invalid currency `%s`.", $code));
    }

    public static function getVat($priceEclxVat, $vatRate) {
        return ($priceEclxVat * $vatRate / 100);
    }

    public static function addVat($priceExclVat, $vatRate) {
        return $priceExclVat * (1 + ($vatRate / 100));
    }
}
