<?php
/**
 * PHP 8 turned several warnings of the shop code into thrown errors that "catch (Exception)" does not catch (a 500 for
 * the app instead of the JSON error):
 *  - PaymentStripe Currency::getAmountForCurrency("") / ("abc"): "abc" * 100 is a TypeError
 *  - Mcommerce Cart: count(null) when a product has no options, sizeof(null) / in_array(x, null) for a choice group the
 *    app did not send, and a division by zero when the discount is computed on an empty cart
 *  - Mcommerce CartController: round("abc" * rate) for the points of a loyalty payment
 *  - LoyaltyCard: "a1" passed the preg_match('/[0-9]/') check and then points + "a1" threw
 */
require_once dirname(__DIR__, 3) . '/siberian/app/sae/modules/PaymentStripe/Model/Currency.php';

if (!function_exists('__')) {
    function __($text)
    {
        return $text;
    }
}

function shop_source(string $path): string
{
    return file_get_contents(dirname(__DIR__, 3) . '/siberian/app/sae/modules/' . $path);
}

return [
    'Stripe amounts are converted to the smallest unit' => function () {
        t_equal(1250, \PaymentStripe\Model\Currency::getAmountForCurrency('12.50', 'EUR'), 'euro');
        t_equal(1250, \PaymentStripe\Model\Currency::getAmountForCurrency(12.5, 'eur'), 'float, lower case');
        t_equal(1250, \PaymentStripe\Model\Currency::getAmountForCurrency(1250, 'JPY'), 'zero-decimal currency');
    },

    'a text or empty Stripe amount is an exception that is caught, not a TypeError' => function () {
        foreach (['', 'abc', null, []] as $amount) {
            try {
                \PaymentStripe\Model\Currency::getAmountForCurrency($amount, 'EUR');
                t_true(false, 'no exception for ' . var_export($amount, true));
            } catch (Exception $e) {
                t_true(true, 'caught');
            }
        }
    },

    'the cart compares option counts without count(null)' => function () {
        $source = shop_source('Mcommerce/Model/Cart.php');

        t_true(strpos($source, 'count($line->getOptions() ?: []) == count($product->getOptions() ?: [])') !== false, 'null-safe counts');
        t_true(strpos($source, 'if ($ttc == 0) {') !== false, 'no division by a zero total');
    },

    'the cart controller reads choices that were not sent as an empty list' => function () {
        $source = shop_source('Mcommerce/controllers/Mobile/CartController.php');

        t_true(strpos($source, "sizeof((array) (\$form['choices'][''.\$group->getGroupId()]['selected_options'] ?? [])) == 0") !== false, 'sizeof');
        t_true(strpos($source, "(array) (\$form['choices'][\"\" . \$group->getId()]['selected_options'] ?? [])") !== false, 'in_array');
        t_true(strpos($source, 'round((float) ($data["points"] ?? 0) * $application->getFidelityRate()') !== false, 'points');
    },

    'the loyalty card points are an int that must be at least 1' => function () {
        foreach (['LoyaltyCard/controllers/Mobile/ViewController.php', 'LoyaltyCard/controllers/MobileController.php'] as $file) {
            $source = shop_source($file);

            t_true(strpos($source, "preg_match('/[0-9]/', \$nbr)") === false, "$file: no digit-anywhere check");
            t_true(strpos($source, '(int) $datas[\'number_of_points\']') !== false, "$file: cast to int");
        }
    },
];
