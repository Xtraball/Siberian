<?php
/**
 * The fatal errors of the SAE core that installations report to the downloader (checked against the groups of the
 * error reports, 2026-11-03): PHP 8 refuses what PHP 7 let through. Source-level checks, each fails on the code
 * before the fix.
 */
$siberian = dirname(__DIR__, 3) . '/siberian';
$modules = $siberian . '/app/sae/modules/';
$read = function ($path) use ($siberian) {
    return file_get_contents($siberian . '/' . $path);
};

return [
    'analytics store: no gmmktime() without argument, it is an ArgumentCountError in PHP 8' => function () use ($read) {
        $code = $read('app/sae/modules/Analytics/controllers/Mobile/StoreController.php');
        t_equal(0, preg_match('/gmmktime\(\s*\)/', $code), 'no gmmktime()');
        t_true(preg_match_all('/=\s*time\(\);/', $code) >= 4, 'time() instead');
    },

    'device size: unknown or zero size never divides by zero' => function () use ($read) {
        foreach (['app/sae/modules/Front/controllers/AssetsController.php', 'app/sae/modules/Front/controllers/MobileController.php'] as $file) {
            $code = $read($file);
            t_true(preg_match('/\$device_width <= 0 \|\| \$device_height <= 0/', $code) === 1, "$file guards the size");
            // the guard comes before the first division
            t_true(strpos($code, '$device_width <= 0') < strpos($code, '$ratio = $device_height / $device_width'), "$file guards first");
        }
    },

    'events list: the groups are not read before they exist' => function () use ($read) {
        $code = $read('app/sae/modules/Event/controllers/Mobile/ListController.php');
        t_equal(0, preg_match('/in_array\([^;]*\$data\[\'groups\'\]\)/', $code), 'no in_array on an unset key');
        t_true(strpos($code, "\$data['groups'] ?? []") !== false, 'default to an empty list');
    },

    'mobile controllers: no application answers an error, _sendJson accepts no data' => function () use ($read) {
        $code = $read('app/sae/modules/Application/Controller/Mobile/Default.php');
        t_true(strpos($code, 'if (!$this->getApplication()) {') !== false, 'the null application is answered');
        t_true(strpos($code, 'if (!$this->getApplication()) {') < strpos($code, '->valueIdBelongsTo('), 'before the call on it');
        t_true(strpos($code, 'is_array($data) && array_key_exists("cache", $data)') !== false, '_sendJson checks its data');
    },

    'video list: a youtube answer without items is handled' => function () use ($read) {
        $code = $read('app/sae/modules/Media/controllers/Mobile/Gallery/Video/ListController.php');
        t_equal(0, preg_match('/count\(\$results\[\'items\'\]\)/', $code), 'no count() on a missing key');
        t_true(strpos($code, "empty(\$results['items'])") !== false, 'empty() instead');
    },

    'image: a file that cannot be decoded does not reach imagesavealpha()' => function () use ($read) {
        $code = $read('app/sae/modules/Core/Model/Lib/Image.php');
        t_equal(0, preg_match('/\$this->_resources = imagecreatefromstring/', $code), 'the result is checked before it is kept');
        t_true(preg_match('/if \(\$resources\) \{\s+imagesavealpha\(\$resources, true\);/', $code) === 1, 'saved only for a real image');
    },

    'geocoding: an address given as a string is accepted' => function () use ($read) {
        $code = $read('lib/Siberian/Google/Geocoding.php');
        t_true(strpos($code, '!is_array($address)') !== false && strpos($code, "['address' => (string) \$address]") !== false, 'a string becomes an address');
        t_true(strpos($code, '!is_array($address)') < strpos($code, 'array_key_exists("refresh", $address)'), 'before the first array use');
    },

    'sample option value: getDummy is static because it is called statically' => function () use ($read) {
        $code = $read('app/sae/modules/Application/Model/Option/Value.php');
        t_true(strpos($code, 'public static function getDummy()') !== false, 'static');
        t_equal(0, preg_match('/function getDummy\(\)[^}]*\$this->/s', $code), 'no instance use inside');
        foreach (['app/sae/modules/Application/Model/Application/Abstract.php', 'app/sae/modules/Front/controllers/IndexController.php'] as $file) {
            t_true(strpos($read($file), 'Application_Model_Option_Value::getDummy()') !== false, "$file calls it statically");
        }
    },
];
