<?php

namespace Siberian\Module;

require_once __DIR__ . '/Quarantine.php';

/**
 * Opt-in circuit breaker for the calls of the mobile app and of the web app.
 *
 * Since 5.1.9 a fatal error on an app call is not quarantined (see Quarantine::isAppRequest), the visitor gets the plain 5xx.
 * A module endpoint that is broken then runs its broken code on every call. With
 *
 *     $_config['module_app_circuit_breaker'] = true;
 *
 * N fatal errors of a module (default 5, `module_app_circuit_breaker_threshold`) on one endpoint within M minutes
 * (default 10, `module_app_circuit_breaker_window_minutes`) open the circuit of that endpoint for a cool-down (default
 * 5 minutes, `module_app_circuit_breaker_cooldown_minutes`): its app calls get a fast 503 with `Retry-After`, before
 * any module code runs. Then one call is let through (half open): when it ends without a fatal error the circuit closes,
 * when it dies again the circuit opens for another cool-down.
 *
 * The endpoint is Quarantine::endpointPath() (the module is not known before the dispatch), the module of the fatal
 * error is kept in the record to name it in the answer. Only app calls count; the editor, the backoffice, the api and
 * the cron are never touched. The state is in var/quarantine/breaker.json, outside the caches that "clear cache"
 * empties. Nothing here may get in the way of the application: every entry point swallows its own failures.
 *
 * Off by default: without the option no file is read or written.
 */
class CircuitBreaker
{
    public const DEFAULT_THRESHOLD = 5;
    public const DEFAULT_WINDOW_MINUTES = 10;
    public const DEFAULT_COOLDOWN_MINUTES = 5;

    /** A half open call that never ended (killed) frees the slot after this many seconds. */
    public const PROBE_LEASE = 30;

    public const MAX_ENTRIES = 200;

    /**
     * @var callable|null returns the unix time, tests give their own
     */
    private static $clock;

    /**
     * @var string|null the endpoint this request was let through for, as the one call of a half open circuit
     */
    private static $probe;

    public static function setClock(?callable $clock): void
    {
        self::$clock = $clock;
    }

    private static function now(): int
    {
        return self::$clock !== null ? (int) call_user_func(self::$clock) : time();
    }

    public static function path(): string
    {
        return Quarantine::root() . '/var/quarantine/breaker.json';
    }

    private static function option(string $key, $default)
    {
        global $_config;

        return is_array($_config) && array_key_exists($key, $_config) ? $_config[$key] : $default;
    }

    public static function isEnabled(): bool
    {
        return self::option('module_app_circuit_breaker', false) === true;
    }

    private static function positive(string $key, int $default): int
    {
        $value = self::option($key, $default);

        return is_int($value) && $value > 0 ? $value : $default;
    }

    public static function threshold(): int
    {
        return self::positive('module_app_circuit_breaker_threshold', self::DEFAULT_THRESHOLD);
    }

    public static function windowSeconds(): int
    {
        return 60 * self::positive('module_app_circuit_breaker_window_minutes', self::DEFAULT_WINDOW_MINUTES);
    }

    public static function cooldownSeconds(): int
    {
        return 60 * self::positive('module_app_circuit_breaker_cooldown_minutes', self::DEFAULT_COOLDOWN_MINUTES);
    }

    // --- the state ------------------------------------------------------------------------------------

    /**
     * Runs $change on the state under an exclusive lock and writes the result atomically.
     *
     * @param callable $change receives the state array by reference
     */
    private static function withState(callable $change): bool
    {
        $path = self::path();
        $dir = dirname($path);
        if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
            return false;
        }
        $lock = @fopen($path . '.lock', 'c');
        if ($lock === false) {
            return false;
        }
        $ok = false;
        if (flock($lock, LOCK_EX)) {
            $state = [];
            if (is_readable($path)) {
                $decoded = json_decode((string) file_get_contents($path), true);
                $state = is_array($decoded) ? $decoded : [];
            }
            $change($state);
            $state = self::prune($state);
            $tmp = $path . '.' . getmypid() . '.tmp';
            $json = json_encode($state, JSON_UNESCAPED_SLASHES);
            $ok = $json !== false && file_put_contents($tmp, $json . PHP_EOL) !== false && @rename($tmp, $path);
            if (!$ok) {
                @unlink($tmp);
            }
            flock($lock, LOCK_UN);
        }
        fclose($lock);

        return $ok;
    }

    /**
     * Quiet endpoints go: no fatal in the window, circuit closed. The state stays small.
     */
    private static function prune(array $state): array
    {
        $now = self::now();
        $window = self::windowSeconds();
        foreach ($state as $endpoint => $entry) {
            $fatals = array_values(array_filter((array) ($entry['fatals'] ?? []), static function ($at) use ($now, $window) {
                return is_int($at) && $at > $now - $window;
            }));
            $state[$endpoint]['fatals'] = $fatals;
            $busy = (int) ($entry['open_until'] ?? 0) > 0 || (int) ($entry['probe_until'] ?? 0) > $now;
            if ($fatals === [] && !$busy) {
                unset($state[$endpoint]);
            }
        }
        if (count($state) > self::MAX_ENTRIES) {
            $state = array_slice($state, -self::MAX_ENTRIES, null, true);
        }

        return $state;
    }

    private static function read(): array
    {
        $path = self::path();
        if (!is_readable($path)) {
            return [];
        }
        $decoded = json_decode((string) file_get_contents($path), true);

        return is_array($decoded) ? $decoded : [];
    }

    // --- the decision ---------------------------------------------------------------------------------

    /**
     * What happens to a call of this endpoint.
     *
     * @return array [verdict, retry_after, module] verdict is allow (closed), probe (half open, this call is the test) or block
     */
    public static function decide(string $endpoint): array
    {
        $now = self::now();
        $entry = self::read()[$endpoint] ?? null;
        $openUntil = (int) ($entry['open_until'] ?? 0);
        if ($entry === null || $openUntil === 0) {
            return ['allow', 0, null];
        }
        $module = (string) ($entry['module'] ?? '');
        if ($openUntil > $now) {
            return ['block', max(1, $openUntil - $now), $module];
        }

        // Cool-down over: one call goes through, the others wait for its result.
        $verdict = ['block', 1, $module];
        self::withState(static function (array &$state) use ($endpoint, $now, &$verdict, $module) {
            $current = $state[$endpoint] ?? null;
            if ($current === null || (int) ($current['open_until'] ?? 0) === 0) {
                $verdict = ['allow', 0, null];

                return;
            }
            if ((int) ($current['probe_until'] ?? 0) > $now) {
                $verdict = ['block', max(1, (int) $current['probe_until'] - $now), $module];

                return;
            }
            $state[$endpoint]['probe_until'] = $now + self::PROBE_LEASE;
            $verdict = ['probe', 0, $module];
        });

        return $verdict;
    }

    /**
     * A fatal error of a module on an app call. Opens the circuit at the threshold, and again after a failed half open call.
     */
    public static function recordFatal(string $endpoint, string $module): void
    {
        $now = self::now();
        self::withState(static function (array &$state) use ($endpoint, $module, $now) {
            $entry = $state[$endpoint] ?? ['fatals' => []];
            $entry['module'] = $module;
            if ((int) ($entry['probe_until'] ?? 0) > 0) {
                // The call that was let through died: another cool-down.
                $entry['open_until'] = $now + self::cooldownSeconds();
                $entry['fatals'] = [];
                unset($entry['probe_until']);
            } else {
                $entry['fatals'][] = $now;
                $window = self::windowSeconds();
                $entry['fatals'] = array_values(array_filter($entry['fatals'], static function ($at) use ($now, $window) {
                    return $at > $now - $window;
                }));
                if ((int) ($entry['open_until'] ?? 0) === 0 && count($entry['fatals']) >= self::threshold()) {
                    $entry['open_until'] = $now + self::cooldownSeconds();
                    $entry['fatals'] = [];
                }
            }
            $state[$endpoint] = $entry;
        });
    }

    /**
     * The call that was let through ended without a fatal error: the circuit closes.
     */
    public static function recordSuccess(string $endpoint): void
    {
        self::withState(static function (array &$state) use ($endpoint) {
            unset($state[$endpoint]);
        });
    }

    // --- the two entry points -------------------------------------------------------------------------

    /**
     * Called by index.php once the configuration is loaded and before anything else runs. Answers and ends the request
     * when the circuit of the endpoint is open. Never throws.
     */
    public static function guard(array $server): void
    {
        try {
            if (!self::isEnabled() || !Quarantine::isAppRequest($server)) {
                return;
            }
            $endpoint = Quarantine::endpointPath($server);
            if ($endpoint === '') {
                return;
            }
            [$verdict, $retryAfter, $module] = self::decide($endpoint);
            if ($verdict === 'probe') {
                self::$probe = $endpoint;

                return;
            }
            if ($verdict !== 'block') {
                return;
            }
            if (!headers_sent()) {
                http_response_code(503);
                header('Retry-After: ' . $retryAfter, true, 503);
                header('Content-Type: application/json', true, 503);
            }
            echo json_encode(['error' => true, 'circuit_open' => $module, 'retry_after' => $retryAfter]);
            exit;
        } catch (\Throwable $e) {
            // the application goes on
        }
    }

    /**
     * Called first by the shutdown handler. Counts the fatal error of a module on an app call; closes the circuit when
     * the call that was let through ended well. Never throws.
     *
     * @param array|null $error error_get_last()
     * @param array|null $server null on the command line
     */
    public static function onShutdown(?array $error, ?array $server): void
    {
        try {
            if ($server === null || !self::isEnabled() || !Quarantine::isAppRequest($server)) {
                return;
            }
            $endpoint = Quarantine::endpointPath($server);
            if ($endpoint === '') {
                return;
            }
            $module = $error !== null && Quarantine::isFatal($error['type'] ?? null)
                ? Quarantine::moduleFromFile($error['file'] ?? null)
                : null;
            if ($module !== null) {
                self::recordFatal($endpoint, $module);
            } elseif (self::$probe === $endpoint && ($error === null || !Quarantine::isFatal($error['type'] ?? null))) {
                self::recordSuccess($endpoint);
            }
            self::$probe = null;
        } catch (\Throwable $e) {
            // the application goes on
        }
    }
}
