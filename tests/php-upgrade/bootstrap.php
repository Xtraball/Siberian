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

/**
 * Start (once) a local php -S server running fixtures/router.php and return its
 * base URL. Killed when the test process exits.
 */
function t_server()
{
    static $base = null;
    if ($base !== null) {
        return $base;
    }
    $probe = stream_socket_server('tcp://127.0.0.1:0');
    $port = (int) substr(stream_socket_get_name($probe, false), strrpos(stream_socket_get_name($probe, false), ':') + 1);
    fclose($probe);

    $proc = proc_open(
        [PHP_BINARY, '-d', 'display_errors=0', '-S', "127.0.0.1:$port", __DIR__ . '/fixtures/router.php'],
        [0 => ['pipe', 'r'], 1 => ['file', '/dev/null', 'w'], 2 => ['file', '/dev/null', 'w']],
        $pipes
    );
    register_shutdown_function(function () use ($proc) {
        proc_terminate($proc);
    });
    for ($i = 0; $i < 50; $i++) {
        if (($c = @fsockopen('127.0.0.1', $port)) !== false) {
            fclose($c);
            return $base = "http://127.0.0.1:$port";
        }
        usleep(100000);
    }
    throw new RuntimeException('fixture server did not start');
}

// Siberian global helpers the vendored libraries were patched to call.
require_once $root . '/lib/System/polyfills.php';
