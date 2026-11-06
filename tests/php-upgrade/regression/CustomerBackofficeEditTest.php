<?php
/**
 * Issue #1044: editing an app user in the back office failed with "This customer doesn't exists." because
 * saveAction compared the customer app_id and the application id with !==. The database layer returns one as a
 * string and the other as an int, so the strict comparison was always true for an existing customer.
 */
$root = dirname(__DIR__, 3);
$src = file_get_contents("$root/siberian/app/sae/modules/Customer/controllers/ApplicationController.php");

return [
    'saveAction compares the customer app id as integers' => function () use ($src) {
        t_true(strpos($src, '(int) $customer->getAppId() !== (int) $appId') !== false, 'cast on both sides');
        t_equal(0, preg_match('/\$customer->getAppId\(\) !== \$appId/', $src), 'raw strict comparison left');
    },

    'the cast comparison accepts "12" against 12 and still refuses another app' => function () {
        $same = (int) '12' !== (int) 12;
        $other = (int) '13' !== (int) 12;
        t_true(!$same, 'same app');
        t_true($other, 'other app');
    },
];
