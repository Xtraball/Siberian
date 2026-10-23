<?php
/**
 * Minimal bootstrap for the PHP-upgrade regression suite.
 *
 * Loads Zend (bundled ZF1) and the committed composer vendor tree without the
 * full application (no database, no config.php). Anything that needs more than
 * this belongs in an integration test, not here.
 */
$root = dirname(__DIR__, 2) . '/siberian';

set_include_path($root . '/lib' . PATH_SEPARATOR . get_include_path());

require_once $root . '/lib/vendor/autoload.php';
require_once $root . '/lib/Zend/Loader/Autoloader.php';

Zend_Loader_Autoloader::getInstance()->registerNamespace(['Siberian_']);

/** Tiny assertion helpers; no phpunit so the suite runs on a bare php image. */
function t_equal($expected, $actual, $label = '')
{
    if ($expected !== $actual) {
        throw new RuntimeException(sprintf(
            '%s expected %s, got %s',
            $label,
            var_export($expected, true),
            var_export($actual, true)
        ));
    }
}

function t_true($actual, $label = '')
{
    t_equal(true, (bool) $actual, $label);
}

// Siberian global helpers the vendored libraries were patched to call.
require_once $root . '/lib/System/polyfills.php';
