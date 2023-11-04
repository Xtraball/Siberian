<?php

// PHP Polyfill
abstract class __polyfill_mixed {}
if (version_compare(PHP_VERSION, '8.0', '<')) {
    class_alias(\__polyfill_mixed::class, 'mixed');
}

/**
 * This polyfill guesses the order of the arguments
 *
 * @param $firstArg
 * @param $secondArg
 * @return string
 */
function implode_polyfill($firstArg, $secondArg = null): string
{
    if (is_array($firstArg)) {
        $array = $firstArg;
        $separator = $secondArg ?? "";
    } else {
        $array = $secondArg;
        $separator = $firstArg;
    }

    // If php version <= 7.3 then use old syntax other use the new
    if (version_compare(PHP_VERSION, '7.4.0') === -1) {
        return implode($array, $separator);
    } else {
        return implode($separator, $array);
    }
}

/**
 * @param $string
 * @return string
 */
function filter_sanitize_string_polyfill($string): string
{
    $str = preg_replace('/\x00|<[^>]*>?/', '', $string);
    return str_replace(["'", '"'], ['&#39;', '&#34;'], $str);
}