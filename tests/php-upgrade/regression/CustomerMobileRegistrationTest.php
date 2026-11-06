<?php
/**
 * Registration with a mobile number (phone input with country flags, intl-tel-input).
 *
 * What blocked the registration:
 * - checkMobile() refused an empty field even when the number is optional, and the library answers null (not false)
 *   while its utils.js is not loaded, which was read as "invalid";
 * - getIpInfo() threw inside the XHR handler on an answer that is not JSON, so the promise stayed pending, the widget
 *   was never initialized, customer.mobile stayed empty and the server answered that the mobile was missing;
 * - the fallback country was the language code ("sv" is El Salvador, "en" or "ja" are not countries);
 * - a failed load of the library left TelInput.isLoaded() pending forever (the login modal waits on it).
 *
 * The logic is checked on the sources, and the three committed bundles must carry it.
 */
$root = dirname(__DIR__, 3);
$read = function ($path) use ($root) {
    return file_get_contents("$root/$path");
};
$bundles = [
    'ionic/www/dist',
    'siberian/var/apps/browser/dist',
    'siberian/var/apps/overview/dist',
];

return [
    'an empty field is not an invalid number' => function () use ($read) {
        $src = $read('ionic/www/js/controllers/customer.js');
        t_true(preg_match("/raw === ''\\) \\{\\s*\\\$scope\\.customer\\.mobile = '';\\s*return true;/", $src) === 1, 'empty returns true');
        t_true(strpos($src, 'iti.isValidNumber()') !== false, 'the library is still used');
        t_true(strpos($src, '(iti && window.intlTelInputUtils) ? iti.isValidNumber() : null') !== false, 'null without utils');
        t_true(strpos($src, 'Customer.normalizeMobile(raw, dialCode)') !== false, 'fallback check without the library');
    },

    'the country lookup cannot leave the widget uninitialized' => function () use ($read) {
        $factory = $read('ionic/www/js/factory/customer.js');
        t_true(preg_match('/try \{\s*defer\.resolve\(JSON\.parse\(this\.responseText\)\);\s*\} catch \(e\) \{\s*defer\.reject\(e\);/', $factory) === 1, 'JSON.parse guarded');
        t_true(strpos($factory, 'oReq.timeout = 4000;') !== false, 'timeout');
        t_true(strpos($factory, 'oReq.ontimeout = ipFail;') !== false, 'timeout rejects');

        $ctrl = $read('ionic/www/js/controllers/customer.js');
        t_equal(3, substr_count($ctrl, 'initMobile('), 'all three branches go through initMobile');
        t_equal(0, preg_match('/Customer\.initIti\(\'customer_mobile\', (defaultFallbackLanguage|CURRENT_LANGUAGE)\)/', $ctrl), 'no language code as a country');
    },

    'the fallback country comes from the language' => function () use ($read) {
        $factory = $read('ionic/www/js/factory/customer.js');
        foreach (["sv: 'se'", "en: 'gb'", "ja: 'jp'", "da: 'dk'", "el: 'gr'"] as $pair) {
            t_true(strpos($factory, $pair) !== false, $pair);
        }
        t_true(strpos($factory, 'factory.countryFromLanguage = function') !== false, 'helper');
        t_true(strpos($factory, 'if (!input || typeof window.intlTelInput !== \'function\')') !== false, 'no input, no library');
    },

    'a failed load of the phone library does not hang the login modal' => function () use ($read) {
        $src = $read('ionic/www/js/services/tel-input.js');
        t_true(preg_match('/\}, function \(\) \{[^}]*service\.failed = true;\s*service\.loadedPromise\.resolve\(\);/s', $src) === 1, 'rejection resolves');
    },

    'the committed bundles carry the fix' => function () use ($read, $bundles) {
        foreach ($bundles as $dir) {
            $chunks = $read("$dir/onloadchunks.bundle.min.js");
            t_true(strpos($chunks, 'normalizeMobile') !== false, "$dir: normalizeMobile");
            t_true(strpos($chunks, 'countryFromLanguage') !== false, "$dir: countryFromLanguage");
            t_true(strpos($chunks, 'ontimeout') !== false, "$dir: ipinfo timeout");
            $services = $read("$dir/services.bundle.min.js");
            t_true(strpos($services, 'failed=!0') !== false, "$dir: TelInput failure");
        }
    },
];
