<?php
/**
 * #933: a machine translation with a broken placeholder ("Súbor % neexistuje" for "The file '%s' does not exist")
 * makes vsprintf() throw a ValueError on PHP 8, and every page or upload that shows the message ended with an empty
 * HTTP 500. gettext_vsprintf() falls back to the original text.
 */
$helperFile = dirname(__DIR__, 3) . '/siberian/lib/vendor/gettext/gettext/src/translator_functions.php';

/** Loads gettext_vsprintf() alone: the file also defines __(), p__() ... which other tests of the suite stub. */
$loadHelper = function () use ($helperFile) {
    if (function_exists('gettext_vsprintf')) {
        return;
    }
    $code = file_get_contents($helperFile);
    $start = strpos($code, "if (!function_exists('gettext_vsprintf'))");
    t_true($start !== false, 'the helper exists');
    $end = strpos($code, "\n}\n", $start);
    eval(substr($code, $start, $end + 3 - $start));
};

return [
    'gettext_vsprintf formats a good translation' => function () use ($loadHelper) {
        $loadHelper();
        t_equal("Súbor 'a.png' neexistuje", gettext_vsprintf("Súbor '%s' neexistuje", ['a.png'], "The file '%s' does not exist"), 'good translation');
        t_equal('2 of 3', gettext_vsprintf('%d of %d', [2, 3], '%d of %d'), 'two placeholders');
    },

    'gettext_vsprintf falls back to the original text when the translation is broken' => function () use ($loadHelper) {
        $loadHelper();
        t_equal("The file 'a.png' does not exist", gettext_vsprintf('Súbor % neexistuje', ['a.png'], "The file '%s' does not exist"), 'unknown specifier');
        t_equal("The file 'a.png' does not exist", gettext_vsprintf("Súbor '%s' '%s' neexistuje", ['a.png'], "The file '%s' does not exist"), 'one placeholder too many');
        t_equal('Súbor % neexistuje', gettext_vsprintf('Súbor % neexistuje', ['a.png']), 'no original: the raw text, no exception');
    },

    'every vsprintf of the translation functions goes through gettext_vsprintf' => function () use ($helperFile) {
        $code = file_get_contents($helperFile);
        t_equal(8, substr_count($code, 'gettext_vsprintf($text, $args, $original)'), 'the eight translation functions use it');
        $functions = substr($code, strpos($code, 'function __('));
        t_equal(substr_count($functions, 'gettext_vsprintf('), substr_count($functions, 'vsprintf('), 'no bare vsprintf() in the translation functions');
    },
];
