<?php
/**
 * A PHP without the iconv extension must not end in "Call to undefined function iconv_substr()" (Zend_Date,
 * Zend_Locale_Format, slugify). The fallback gives the same answer as iconv on UTF-8 text, the polyfill declares the
 * functions only when the extension is missing, and the back office says so.
 */
$root = dirname(__DIR__, 3);
require_once "$root/siberian/lib/Siberian/IconvFallback.php";

use Siberian\IconvFallback;

$read = function ($path) {
    return file_get_contents($path);
};

return [
    'length, substr and position agree with iconv on UTF-8 text' => function () {
        if (!function_exists('iconv_strlen')) {
            return;
        }
        foreach (['dd/MM/yyyy', 'Mittwoch', 'mercredi', 'Среда', 'année é€', ''] as $s) {
            t_equal(iconv_strlen($s, 'UTF-8'), IconvFallback::strlen($s, 'UTF-8'), "strlen $s");
            t_equal(iconv_substr($s, 0, 3, 'UTF-8'), IconvFallback::substr($s, 0, 3, 'UTF-8'), "substr $s");
            t_equal(iconv_substr($s, 0, 1, 'UTF-8'), IconvFallback::substr($s, 0, 1, 'UTF-8'), "substr1 $s");
        }
        foreach (['d', 'M', 'y', 'H', 'm', 's', 'é', 'zz'] as $needle) {
            foreach (['dd/MM/yyyy HH:mm:ss', 'é d M'] as $format) {
                t_equal(iconv_strpos($format, $needle), IconvFallback::strpos($format, $needle), "strpos $needle in $format");
            }
        }
        t_equal(iconv_strrpos('a/b/c', '/'), IconvFallback::strrpos('a/b/c', '/'), 'strrpos');
    },

    'the date format positions are the ones Zend_Locale_Format needs' => function () {
        t_equal(0, IconvFallback::strpos('dd.MM.yyyy', 'd'), 'day');
        t_equal(3, IconvFallback::strpos('dd.MM.yyyy', 'M'), 'month');
        t_equal(6, IconvFallback::strpos('dd.MM.yyyy', 'y'), 'year');
        t_equal(false, IconvFallback::strpos('dd.MM.yyyy', 'H'), 'no hour');
        t_equal('Mer', IconvFallback::substr('Mercredi', 0, 3, 'UTF-8'), 'weekday short');
        t_equal('É', IconvFallback::substr('Été', 0, 1, 'UTF-8'), 'one multibyte letter, not half of it');
    },

    'toAscii spells accents and known letters, and never leaves a non ASCII byte' => function () {
        t_equal('Eleve a Strasse', IconvFallback::toAscii('Élève à Straße'), 'latin');
        t_equal('Oslo Orebro', IconvFallback::toAscii('Oslo Örebro'), 'umlaut');
        t_equal('plain text 123', IconvFallback::toAscii('plain text 123'), 'ascii is untouched');
        t_equal(0, preg_match('/[^\x00-\x7F]/', IconvFallback::toAscii('日本語 Привет ✓')), 'unmappable becomes ?');
        t_equal('', IconvFallback::toAscii(''), 'empty');
    },

    'convert honours //TRANSLIT and gives false for an unknown encoding' => function () {
        t_equal('Cafe', IconvFallback::convert('utf-8', 'us-ascii//TRANSLIT', 'Café'), 'translit');
        t_equal(false, IconvFallback::convert('utf-8', 'NOT-AN-ENCODING', 'x'), 'unknown');
        t_equal("caf\xE9", IconvFallback::convert('UTF-8', 'ISO-8859-1', 'café'), 'latin1');
        t_equal('café', IconvFallback::convert('ISO-8859-1', 'UTF-8', "caf\xE9"), 'back to utf8');
    },

    'the polyfill declares every function only when it is missing' => function () use ($root, $read) {
        $src = $read("$root/siberian/lib/Siberian/IconvPolyfill.php");
        foreach (['iconv', 'iconv_strlen', 'iconv_substr', 'iconv_strpos', 'iconv_strrpos', 'iconv_get_encoding', 'iconv_set_encoding'] as $fn) {
            t_true(strpos($src, "if (!function_exists('$fn')) {") !== false, "$fn is guarded");
        }
        $pure = $read("$root/siberian/lib/Siberian/Pure.php");
        t_true(strpos($pure, "extension_loaded('iconv')") !== false && strpos($pure, 'IconvPolyfill.php') !== false, 'Pure.php loads it when the extension is missing');
    },

    'slugify no longer needs the extension' => function () use ($root, $read) {
        $pure = $read("$root/siberian/lib/Siberian/Pure.php");
        t_true(strpos($pure, '\Siberian\IconvFallback::toAscii($text)') !== false, 'slugify falls back');
        t_equal(0, preg_match("/^\s*\\\$text = iconv\('utf-8', 'us-ascii\/\/TRANSLIT', \\\$text\);/m", $pure), 'raw iconv call left');
    },

    'the back office warns when the extension is missing' => function () use ($root, $read) {
        $index = $read("$root/siberian/app/sae/design/desktop/backoffice/template/backoffice/index/index.phtml");
        t_true(strpos($index, "!extension_loaded('iconv')") !== false, 'tested');
        t_true(strpos($index, 'The PHP extension iconv is missing') !== false, 'worded');
        $po = $read("$root/siberian/languages/base/c_backoffice.po");
        t_true(strpos($po, 'msgid "The PHP extension iconv is missing') !== false, 'in the catalog');
    },
];
