<?php
namespace Dashi\Apns2;

class Utils{

    public static function hyphenJoinedToCamelcase($string, $capitalizeFirstCharacter = true, $joinCharRegex = '/[\-\_]/')
    {
        $str = str_replace(' ', '', ucwords(preg_replace($joinCharRegex, ' ', $string)));

        if (!$capitalizeFirstCharacter) {
            $str[0] = strtolower($str[0]);
        }

        return $str;
    }

    public static function camelcaseToHyphenJoined($str, $joinChar = '-')
    {
        return ltrim(strtolower(preg_replace('/[A-Z]/', $joinChar.'$0', $str)), $joinChar);
    }

}