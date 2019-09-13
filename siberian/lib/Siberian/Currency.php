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
     * @var string
     */
    public static $configFile = "/lib/Siberian/Currency/data/common-currency.json";

    /**
     * @var null
     */
    public static $jsonSource = null;

    /**
     * @param bool $withStripeSuffix
     * @return array|false
     */
    public static function getAllCurrencies()
    {
        if (self::$jsonSource === null) {
            $contents = file_get_contents(path(self::$configFile));
            self::$jsonSource = Json::decode($contents);
        }

        $commonCurrencies = self::$jsonSource;
        ksort($commonCurrencies);

        return $commonCurrencies;
    }

    /**
     * @param $code
     * @return mixed
     * @throws Exception
     */
    public static function getCurrency($code)
    {
        if (self::$jsonSource === null) {
            $contents = file_get_contents(path(self::$configFile));
            self::$jsonSource = Json::decode($contents);
        }

        if (array_key_exists($code, self::$jsonSource)) {
            return self::$jsonSource[$code];
        }

        throw new Exception(p__("currency", "Invalid currency `%s`.", $code));
    }

    /**
     * @param $priceEclxVat
     * @param $vatRate
     * @return float|int
     */
    public static function getVat($priceEclxVat, $vatRate)
    {
        return ($priceEclxVat * $vatRate / 100);
    }

    /**
     * @param $priceExclVat
     * @param $vatRate
     * @return float|int
     */
    public static function addVat($priceExclVat, $vatRate)
    {
        return $priceExclVat * (1 + ($vatRate / 100));
    }
}
