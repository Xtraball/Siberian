<?php
/**
 * #709: getFormatted* formats numbers through formatPrice(). The pattern used by Core_Model_Default_Abstract is
 * read from the source and exercised directly, so the test follows the code without needing the application.
 */
$siberian = dirname(__DIR__, 3) . '/siberian';

$numberPattern = function () use ($siberian) {
    $code = file_get_contents($siberian . '/app/sae/modules/Core/Model/Default/Abstract.php');
    $ok = preg_match("/preg_match\('([^']+)', \\\$data\)\) \{\s*return \\\$this->formatPrice/", $code, $m);
    t_true($ok, 'number pattern found before formatPrice()');
    return $m[1];
};

return [
    '709 positive numbers are still formatted as before' => function () use ($numberPattern) {
        $p = $numberPattern();
        foreach (['0', '12', '12.50', '1000.999', ' 7 '] as $value) {
            t_equal(1, preg_match($p, $value), "'$value' is a number");
        }
    },

    '709 negative numbers are formatted too' => function () use ($numberPattern) {
        $p = $numberPattern();
        foreach (['-3', '-3.25', ' -10 '] as $value) {
            t_equal(1, preg_match($p, $value), "'$value' is a number");
        }
    },

    '709 non numbers and dates are left to the other branches' => function () use ($numberPattern) {
        $p = $numberPattern();
        foreach (['', 'abc', '1.', '--3', '3-', '-', '1,5', '2020-01-02', '12px'] as $value) {
            t_equal(0, preg_match($p, $value), "'$value' is not a number");
        }
    },
];
