<?php
/**
 * More fatal errors of the SAE core reported to the downloader (second batch, after ReportedFatalsTest).
 * Source-level checks, each fails on the code before the fix.
 */
$siberian = dirname(__DIR__, 3) . '/siberian';
$read = function ($path) use ($siberian) {
    return file_get_contents($siberian . '/' . $path);
};

return [
    'facebook: the secret key reads the application of the call, not a static call of an instance method' => function () use ($read) {
        $code = $read('app/sae/modules/Core/Model/Lib/Facebook.php');
        t_equal(0, preg_match('/^(?!\s*\/\/).*self::getApplication\(\)/m', $code), 'no self::getApplication() outside of comments');
        t_true(strpos($code, '$application = Siberian::getApplication();') !== false, 'Siberian::getApplication()');
    },

    'module updates: a PHP without ext-sodium does not fatal in verify()' => function () use ($read) {
        $code = $read('lib/Siberian/ModuleUpdates.php');
        $guard = strpos($code, "function_exists('sodium_crypto_sign_verify_detached')");
        t_true($guard !== false, 'the extension is tested');
        t_true($guard < strpos($code, 'SODIUM_CRYPTO_SIGN_BYTES'), 'before the first constant');
        t_true($guard < strpos($code, 'sodium_crypto_sign_verify_detached($signature'), 'before the call');
    },

    'catalog category: a category without products does not count null' => function () use ($read) {
        $code = $read('app/sae/modules/Catalog/Model/Category.php');
        t_true(strpos($code, 'if (is_null($this->_products) || count($this->_products) == 0)') !== false, 'null is tested');
    },

    'analytics aggregate: an unknown feature is skipped before its key is read' => function () use ($read) {
        $code = $read('app/sae/modules/Analytics/Model/Aggregate.php');
        t_equal(0, preg_match('/!array_key_exists\(\$featureId, \$allFeatures\) &&\s+!array_key_exists/', $code), 'not an AND');
        t_equal(1, preg_match('/!array_key_exists\(\$featureId, \$allFeatures\) \|\|\s+!array_key_exists\(\'appId\', \$allFeatures\[\$featureId\]\)/', $code), 'an OR');
    },
];
