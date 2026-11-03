<?php
/**
 * Opt-in circuit breaker for the app calls (Siberian\Module\CircuitBreaker). A module endpoint that keeps dying with a
 * fatal error on the calls of the mobile app or of the web app gets a fast 503 with Retry-After, before any module code
 * runs, then one call is let through to see whether it works again. Off unless $_config['module_app_circuit_breaker'].
 */
$root = dirname(__DIR__, 3) . '/siberian';
require_once $root . '/lib/Siberian/Module/Quarantine.php';
require_once $root . '/lib/Siberian/Module/CircuitBreaker.php';

use Siberian\Module\CircuitBreaker;
use Siberian\Module\Quarantine;

function cb_tmp(): string
{
    $dir = sys_get_temp_dir() . '/cb_' . bin2hex(random_bytes(4));
    mkdir($dir . '/var/quarantine', 0777, true);

    return $dir;
}

function cb_rm(string $dir): void
{
    if (!is_dir($dir)) {
        return;
    }
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST) as $file) {
        $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
    }
    rmdir($dir);
}

/**
 * Runs $body with a temporary root, a clock and the option on, then cleans up.
 */
function cb_env(callable $body, array $config = ['module_app_circuit_breaker' => true]): void
{
    global $_config;
    $previous = $_config ?? null;
    $dir = cb_tmp();
    $now = 1900000000;
    Quarantine::setRoot($dir);
    CircuitBreaker::setClock(function () use (&$now) {
        return $now;
    });
    $_config = $config;
    try {
        $body($now);
    } finally {
        $_config = $previous;
        CircuitBreaker::setClock(null);
        Quarantine::setRoot(null);
        cb_rm($dir);
    }
}

const CB_ENDPOINT = 'xdelivery/mobile_cart/add';

return [
    'the endpoint is the first three segments, numbers dropped, no query string' => function () {
        t_equal('xdelivery/mobile_cart/add', Quarantine::endpointPath(['REQUEST_URI' => '/Xdelivery/mobile_cart/add/12?customer=4']));
        t_equal('front/mobile_home', Quarantine::endpointPath(['REQUEST_URI' => '/front/mobile_home/12']));
        t_equal('', Quarantine::endpointPath([]));
    },

    'the threshold opens the circuit and the fatal errors outside the window do not count' => function () {
        cb_env(function (&$now) {
            for ($i = 0; $i < CircuitBreaker::DEFAULT_THRESHOLD - 1; $i++) {
                CircuitBreaker::recordFatal(CB_ENDPOINT, 'Xdelivery');
            }
            t_equal('allow', CircuitBreaker::decide(CB_ENDPOINT)[0], 'one short of the threshold');

            $now += CircuitBreaker::windowSeconds() + 1;
            CircuitBreaker::recordFatal(CB_ENDPOINT, 'Xdelivery');
            t_equal('allow', CircuitBreaker::decide(CB_ENDPOINT)[0], 'the old ones left the window');

            for ($i = 0; $i < CircuitBreaker::DEFAULT_THRESHOLD - 1; $i++) {
                CircuitBreaker::recordFatal(CB_ENDPOINT, 'Xdelivery');
            }
            [$verdict, $retry, $module] = CircuitBreaker::decide(CB_ENDPOINT);
            t_equal('block', $verdict, 'open');
            t_equal(CircuitBreaker::cooldownSeconds(), $retry, 'Retry-After is the cool-down');
            t_equal('Xdelivery', $module, 'the module is named');
            t_equal('allow', CircuitBreaker::decide('xdelivery/mobile_cart/list')[0], 'another endpoint is not affected');
        });
    },

    'after the cool-down one call is let through, the others wait, success closes the circuit' => function () {
        cb_env(function (&$now) {
            for ($i = 0; $i < CircuitBreaker::DEFAULT_THRESHOLD; $i++) {
                CircuitBreaker::recordFatal(CB_ENDPOINT, 'Xdelivery');
            }
            $now += CircuitBreaker::cooldownSeconds() - 1;
            t_equal('block', CircuitBreaker::decide(CB_ENDPOINT)[0], 'still cooling down');

            $now += 1;
            t_equal('probe', CircuitBreaker::decide(CB_ENDPOINT)[0], 'half open: the test call');
            t_equal('block', CircuitBreaker::decide(CB_ENDPOINT)[0], 'the others wait for its result');

            CircuitBreaker::recordSuccess(CB_ENDPOINT);
            t_equal('allow', CircuitBreaker::decide(CB_ENDPOINT)[0], 'closed');
        });
    },

    'a failed test call opens the circuit for another cool-down, a test call that never ended frees the slot' => function () {
        cb_env(function (&$now) {
            for ($i = 0; $i < CircuitBreaker::DEFAULT_THRESHOLD; $i++) {
                CircuitBreaker::recordFatal(CB_ENDPOINT, 'Xdelivery');
            }
            $now += CircuitBreaker::cooldownSeconds();
            t_equal('probe', CircuitBreaker::decide(CB_ENDPOINT)[0]);
            CircuitBreaker::recordFatal(CB_ENDPOINT, 'Xdelivery');
            [$verdict, $retry] = CircuitBreaker::decide(CB_ENDPOINT);
            t_equal('block', $verdict, 'open again');
            t_equal(CircuitBreaker::cooldownSeconds(), $retry);

            $now += CircuitBreaker::cooldownSeconds();
            t_equal('probe', CircuitBreaker::decide(CB_ENDPOINT)[0], 'half open again');
            $now += CircuitBreaker::PROBE_LEASE;
            t_equal('probe', CircuitBreaker::decide(CB_ENDPOINT)[0], 'the killed test call did not keep the slot');
        });
    },

    'only a fatal error of a module on an app call counts' => function () {
        cb_env(function (&$now) {
            $file = Quarantine::root() . '/app/local/modules/Xdelivery/Model/Store.php';
            $fatal = ['type' => E_ERROR, 'message' => 'boom', 'file' => $file, 'line' => 3];
            $app = ['REQUEST_URI' => '/xdelivery/mobile_cart/add?x=1'];
            $count = function () {
                $state = json_decode((string) @file_get_contents(CircuitBreaker::path()), true);

                return count($state[CB_ENDPOINT]['fatals'] ?? []);
            };

            CircuitBreaker::onShutdown($fatal, $app);
            t_equal(1, $count(), 'recorded');
            CircuitBreaker::onShutdown($fatal, ['REQUEST_URI' => '/application/customization_features/list']);
            CircuitBreaker::onShutdown($fatal, ['REQUEST_URI' => '/backoffice/advanced_module/save']);
            CircuitBreaker::onShutdown($fatal, null);
            CircuitBreaker::onShutdown(['type' => E_WARNING] + $fatal, $app);
            CircuitBreaker::onShutdown(['file' => '/var/www/app/sae/modules/Core/Model/X.php'] + $fatal, $app);
            CircuitBreaker::onShutdown(null, $app);
            t_equal(1, $count(), 'editor, backoffice, cli, warnings, core files and clean calls are ignored');
        });
    },

    'a test call that ends without a fatal error closes the circuit through the shutdown handler' => function () {
        cb_env(function (&$now) {
            $file = Quarantine::root() . '/app/local/modules/Xdelivery/Model/Store.php';
            $fatal = ['type' => E_ERROR, 'message' => 'boom', 'file' => $file, 'line' => 3];
            $app = ['REQUEST_URI' => '/xdelivery/mobile_cart/add'];
            for ($i = 0; $i < CircuitBreaker::DEFAULT_THRESHOLD; $i++) {
                CircuitBreaker::onShutdown($fatal, $app);
            }
            t_equal('block', CircuitBreaker::decide(CB_ENDPOINT)[0]);

            $now += CircuitBreaker::cooldownSeconds();
            CircuitBreaker::guard($app); // half open: this call is the test, nothing is sent
            CircuitBreaker::onShutdown(null, $app);
            t_equal('allow', CircuitBreaker::decide(CB_ENDPOINT)[0], 'closed after the good call');
        });
    },

    'the options change the numbers, a bad value falls back to the default' => function () {
        cb_env(function () {
            t_equal(2, CircuitBreaker::threshold());
            t_equal(120, CircuitBreaker::windowSeconds());
            t_equal(60, CircuitBreaker::cooldownSeconds());
        }, [
            'module_app_circuit_breaker' => true,
            'module_app_circuit_breaker_threshold' => 2,
            'module_app_circuit_breaker_window_minutes' => 2,
            'module_app_circuit_breaker_cooldown_minutes' => 1,
        ]);
        cb_env(function () {
            t_equal(CircuitBreaker::DEFAULT_THRESHOLD, CircuitBreaker::threshold());
            t_equal(CircuitBreaker::DEFAULT_WINDOW_MINUTES * 60, CircuitBreaker::windowSeconds());
        }, ['module_app_circuit_breaker' => true, 'module_app_circuit_breaker_threshold' => 'lots', 'module_app_circuit_breaker_window_minutes' => -3]);
    },

    'off by default: nothing is read, written or blocked' => function () {
        foreach ([[], ['module_app_circuit_breaker' => 'yes'], ['module_app_circuit_breaker' => false]] as $config) {
            cb_env(function () {
                $file = Quarantine::root() . '/app/local/modules/Xdelivery/Model/Store.php';
                $fatal = ['type' => E_ERROR, 'message' => 'boom', 'file' => $file, 'line' => 3];
                for ($i = 0; $i < 10; $i++) {
                    CircuitBreaker::onShutdown($fatal, ['REQUEST_URI' => '/xdelivery/mobile_cart/add']);
                }
                t_true(!CircuitBreaker::isEnabled(), 'off');
                t_true(!file_exists(CircuitBreaker::path()), 'no state written');
                CircuitBreaker::guard(['REQUEST_URI' => '/xdelivery/mobile_cart/add']); // returns, does not exit
            }, $config);
        }
    },

    'an open circuit answers 503 with Retry-After and JSON before the code goes on, in a separate process' => function () {
        $dir = cb_tmp();
        $now = time();
        file_put_contents($dir . '/var/quarantine/breaker.json', json_encode([
            CB_ENDPOINT => ['fatals' => [], 'module' => 'Xdelivery', 'open_until' => $now + 120],
        ]));
        $script = $dir . '/child.php';
        $lib = dirname(__DIR__, 3) . '/siberian/lib/Siberian/Module';
        file_put_contents($script, "<?php\n\$_config = ['module_app_circuit_breaker' => true];\nrequire " . var_export($lib . '/CircuitBreaker.php', true) .
            ";\nSiberian\\Module\\Quarantine::setRoot(" . var_export($dir, true) . ");\n" .
            "Siberian\\Module\\CircuitBreaker::guard(['REQUEST_URI' => '/xdelivery/mobile_cart/add?x=1']);\necho 'CONTINUED';\n");
        $output = [];
        exec(escapeshellarg(PHP_BINARY) . ' -d display_errors=0 ' . escapeshellarg($script) . ' 2>&1', $output);
        $body = implode("\n", $output);
        $json = json_decode($body, true);
        t_true(strpos($body, 'CONTINUED') === false, 'the request ended');
        t_equal(true, $json['error'] ?? null);
        t_equal('Xdelivery', $json['circuit_open'] ?? null);
        t_true(($json['retry_after'] ?? 0) > 100 && $json['retry_after'] <= 120, 'retry_after is the time left');

        file_put_contents($script, str_replace('/xdelivery/mobile_cart/add?x=1', '/application/customization_features/list', (string) file_get_contents($script)));
        $output = [];
        exec(escapeshellarg(PHP_BINARY) . ' -d display_errors=0 ' . escapeshellarg($script) . ' 2>&1', $output);
        t_equal('CONTINUED', implode("\n", $output), 'the editor is never blocked');
        cb_rm($dir);
    },

    'the state is capped and quiet endpoints are forgotten' => function () {
        cb_env(function (&$now) {
            for ($i = 0; $i < CircuitBreaker::MAX_ENTRIES + 30; $i++) {
                CircuitBreaker::recordFatal('m/mobile_x/a' . $i, 'Foo');
            }
            $state = json_decode((string) file_get_contents(CircuitBreaker::path()), true);
            t_equal(CircuitBreaker::MAX_ENTRIES, count($state), 'capped');

            $now += CircuitBreaker::windowSeconds() + 1;
            CircuitBreaker::recordFatal('m/mobile_x/last', 'Foo');
            $state = json_decode((string) file_get_contents(CircuitBreaker::path()), true);
            t_equal(['m/mobile_x/last'], array_keys($state), 'the quiet ones are gone');
        });
    },

    'the breaker is wired: index.php after the config, the shutdown handler first, the error reports share the endpoint' => function () {
        $root = dirname(__DIR__, 3) . '/siberian';
        $index = file_get_contents($root . '/index.php');
        $config = strpos($index, "require_once './config.php';");
        $guard = strpos($index, 'CircuitBreaker::guard($_SERVER);');
        $limit = strpos($index, 'set_time_limit(300);');
        t_true($config !== false && $guard !== false && $limit !== false && $config < $guard && $guard < $limit, 'guard between the config and the rest');

        $quarantine = file_get_contents($root . '/lib/Siberian/Module/Quarantine.php');
        $hook = strpos($quarantine, 'CircuitBreaker::onShutdown(');
        $handle = strpos($quarantine, '$handled = self::handleError(');
        t_true($hook !== false && $handle !== false && $hook < $handle, 'recorded before the quarantine decides');

        t_true(strpos(file_get_contents($root . '/lib/Siberian/ErrorReporter.php'), 'Quarantine::endpointPath($server)') !== false, 'same endpoint notion');
    },
];
