<?php

/**
 * The iconv functions that Zend and Siberian call, defined only when the PHP iconv extension is missing.
 * Loaded by Pure.php. With the extension present nothing here is declared, so the real functions are used.
 */

if (!function_exists('iconv_strlen')) {
    function iconv_strlen($string, $encoding = null)
    {
        return \Siberian\IconvFallback::strlen($string, $encoding);
    }
}

if (!function_exists('iconv_substr')) {
    function iconv_substr($string, $offset, $length = null, $encoding = null)
    {
        return \Siberian\IconvFallback::substr($string, $offset, $length, $encoding);
    }
}

if (!function_exists('iconv_strpos')) {
    function iconv_strpos($haystack, $needle, $offset = 0, $encoding = null)
    {
        return \Siberian\IconvFallback::strpos($haystack, $needle, $offset, $encoding);
    }
}

if (!function_exists('iconv_strrpos')) {
    function iconv_strrpos($haystack, $needle, $encoding = null)
    {
        return \Siberian\IconvFallback::strrpos($haystack, $needle, $encoding);
    }
}

if (!function_exists('iconv')) {
    function iconv($fromEncoding, $toEncoding, $string)
    {
        return \Siberian\IconvFallback::convert($fromEncoding, $toEncoding, $string);
    }
}

if (!function_exists('iconv_get_encoding')) {
    function iconv_get_encoding($type = 'all')
    {
        $encoding = ini_get('default_charset') ?: 'UTF-8';
        if ($type === 'all') {
            return [
                'input_encoding' => $encoding,
                'output_encoding' => $encoding,
                'internal_encoding' => $encoding,
            ];
        }
        return $encoding;
    }
}

if (!function_exists('iconv_set_encoding')) {
    function iconv_set_encoding($type, $encoding)
    {
        return true;
    }
}
