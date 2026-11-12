<?php
/**
 * getColorizedImage() is called with a null color (images that are not colorizable, apps without a color set).
 * str_replace('#', '', null) is deprecated since PHP 8.1 and was the most frequent deprecation in the error log
 * (Core/View/Default/Abstract.php:335). The color is cast to a string first; the result is the same ('').
 */
$root = dirname(__DIR__, 3);
$src = file_get_contents("$root/siberian/app/sae/modules/Core/View/Default/Abstract.php");

return [
    'getColorizedImage casts the color before str_replace' => function () use ($src) {
        t_true(strpos($src, "str_replace('#', '', (string) \$color)") !== false, 'cast present');
        t_equal(0, preg_match("/str_replace\('#', '', \\\$color\)/", $src), 'raw call left');
    },

    'the cast gives what the null call used to give, without a deprecation' => function () {
        $deprecations = 0;
        set_error_handler(function ($no) use (&$deprecations) {
            if ($no === E_DEPRECATED) {
                $deprecations++;
            }
            return true;
        });
        $null = str_replace('#', '', (string) null);
        $hex = str_replace('#', '', (string) '#ff8800');
        restore_error_handler();
        t_equal('', $null, 'null color');
        t_equal('ff8800', $hex, 'hex color');
        t_equal(0, $deprecations, 'deprecations');
    },
];
