<?php

namespace Siberian;

use Siberian\Module\Quarantine;
use Siberian\ModuleUpdates\Store;
use Siberian\ModuleUpdates\Transport;

require_once __DIR__ . '/Module/Quarantine.php';

/**
 * Proactive error reporting. Errors, warnings and deprecations are grouped on this server, and the groups are
 * pushed once an hour to the downloader (https://downloader.siberiancms.com/api/v1/errors/reports), which
 * aggregates them across installations to find the bugs that matter. Contract: docs/error-reports.md.
 *
 *  - install()   registers the PHP handlers (errors, fatals at shutdown) and chains the ones already there
 *  - record()    counts an occurrence: same level, message and file:line increments one group
 *  - flush()     merges what this request saw into the local buffer file (at shutdown)
 *  - push()      the hourly cron: sends the buffer, backs off when the downloader is busy or down
 *
 * Nothing leaves this server but: a random install id and secret, the edition, the Siberian and PHP versions, and
 * per group the level, a scrubbed message, a path relative to the install root, the line and the counters. No
 * request data, no stack-trace arguments, no SQL values, no user, no absolute path. Messages are scrubbed
 * (quoted values, numbers, e-mail addresses, long hex strings and URLs query strings go).
 *
 * The reporter must never get in the way of the application: every entry point swallows its own failures.
 * It can be turned off in Backoffice > Settings > Advanced > Configuration (error_reports_enabled).
 */
class ErrorReporter
{
    const BASE_URL = 'https://downloader.siberiancms.com';
    const PATH = '/api/v1/errors/reports';
    const SCHEMA = 1;

    const MAX_GROUPS = 500;
    const MAX_SENT_GROUPS = 200;
    const MAX_MESSAGE = 300;
    const MAX_ENDPOINTS = 20;

    /** Rolling history of the app calls for the dashboard: bucket width and age in seconds, modules kept */
    const LOCAL_BUCKET = 120;
    const LOCAL_KEEP = 86400;
    const LOCAL_MAX_MODULES = 50;
    /** A module with this many fatal errors on app calls within WARN_WINDOW seconds gets a warning */
    const WARN_FATALS = 5;
    const WARN_WINDOW = 600;

    const K_INSTALL_ID = 'error_reports_install_id';
    const K_SECRET = 'error_reports_secret';
    const K_BACKOFF_UNTIL = 'error_reports_backoff_until';
    const K_FAILURES = 'error_reports_failures';

    const BACKOFF_BASE = 3600;
    const BACKOFF_MAX = 86400;

    /** @var int E_* constants that end the request */
    const FATALS = E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR;

    /** @var self|null the instance install() registered */
    private static $instance = null;

    /** @var string buffer file (JSON), shared by every request and the cron */
    private $file;

    /** @var string install root, stripped from paths and messages */
    private $root;

    /** @var callable returns the unix time */
    private $clock;

    /** @var callable|null returns whether reporting is on, evaluated when the buffer is written (needs the database) */
    private $enabled;

    /** @var callable|null returns the $_SERVER of the request (tests give their own) */
    private $server;

    /** @var array key => group, what this request saw and has not flushed yet */
    private $pending = [];

    /** @var int groups this request could not keep */
    private $pendingDropped = 0;

    /** @var callable|null the error handler that was there before */
    private $previousHandler = null;

    /** @var bool swallow handled errors: the application ran with error_reporting(0), so nothing was shown before */
    private $swallow = false;

    /** @var bool */
    private $busy = false;

    /**
     * @param string $file Buffer file (var/cache/...)
     * @param string $root Install root, used to make paths relative
     * @param callable|null $clock
     * @param callable|null $enabled
     */
    public function __construct($file, $root, $clock = null, $enabled = null, $server = null)
    {
        $this->server = $server;
        $this->file = $file;
        $this->root = rtrim(str_replace('\\', '/', (string) $root), '/');
        $this->clock = $clock ?: 'time';
        $this->enabled = $enabled;
    }

    // --- registration ---------------------------------------------------------------------------------

    /**
     * Registers the handlers. In production Siberian runs with error_reporting(0), which also hides @-suppressed
     * errors from handlers; to tell them apart the level is raised to E_ALL, display and PHP's own log are kept
     * off, and handled errors are swallowed, so what is shown or logged stays what it was.
     *
     * @return $this
     */
    public function install()
    {
        self::$instance = $this;
        $this->swallow = error_reporting() === 0;
        if ($this->swallow) {
            error_reporting(E_ALL);
            ini_set('display_errors', '0');
            ini_set('log_errors', '0');
        }
        $previous = set_error_handler([$this, 'handleError']);
        $this->previousHandler = $previous;
        register_shutdown_function([$this, 'handleShutdown']);

        return $this;
    }

    /**
     * @return self|null
     */
    public static function instance()
    {
        return self::$instance;
    }

    /**
     * @param int $errno
     * @param string $errstr
     * @param string $errfile
     * @param int $errline
     * @return bool|null what the previous handler said, true when swallowing, false to let PHP carry on
     */
    public function handleError($errno, $errstr, $errfile = '', $errline = 0)
    {
        // @-suppressed (the level excludes it): not a problem anybody asked to hear about.
        if ((error_reporting() & $errno) && !$this->busy) {
            $this->busy = true;
            try {
                $this->record(self::levelOf($errno), (string) $errstr, (string) $errfile, (int) $errline);
            } catch (\Throwable $e) {
                // never get in the way
            }
            $this->busy = false;
        }
        if ($this->previousHandler !== null) {
            return call_user_func($this->previousHandler, $errno, $errstr, $errfile, $errline);
        }

        return $this->swallow ? true : false;
    }

    /**
     * Fatal errors end the request without a handler: they are found here. Then the buffer is written.
     */
    public function handleShutdown()
    {
        try {
            $last = error_get_last();
            if ($last !== null && ($last['type'] & self::FATALS)) {
                $this->record('fatal', (string) $last['message'], (string) $last['file'], (int) $last['line']);
            }
            $this->flush();
        } catch (\Throwable $e) {
            // never get in the way
        }
    }

    // --- collecting ------------------------------------------------------------------------------------

    /**
     * @param int $errno
     * @return string fatal | error | warning | notice | deprecated
     */
    public static function levelOf($errno)
    {
        if ($errno & self::FATALS) {
            return 'fatal';
        }
        if ($errno & (E_USER_ERROR | E_RECOVERABLE_ERROR)) {
            return 'error';
        }
        if ($errno & (E_WARNING | E_CORE_WARNING | E_COMPILE_WARNING | E_USER_WARNING)) {
            return 'warning';
        }
        if ($errno & (E_DEPRECATED | E_USER_DEPRECATED)) {
            return 'deprecated';
        }

        return 'notice';
    }

    /**
     * Counts one occurrence. The same level, message and place is one group.
     *
     * @param string $level fatal | exception | error | warning | notice | deprecated
     * @param string $message
     * @param string $file
     * @param int $line
     */
    public function record($level, $message, $file, $line)
    {
        $message = $this->normalize($message);
        if ($message === '') {
            return;
        }
        $module = Quarantine::moduleFromFile($file);
        $file = $this->relativePath($file);
        [$surface, $endpoint] = $this->requestContext();
        $key = sha1($level . "\n" . $message . "\n" . $file . ':' . (int) $line . "\n" . (string) $module . "\n" . $surface);
        $now = call_user_func($this->clock);
        if (isset($this->pending[$key])) {
            ++$this->pending[$key]['count'];
            $this->pending[$key]['last'] = $now;
            $this->pending[$key]['eps'] = self::mergeEndpoints($this->pending[$key]['eps'], $endpoint === null ? [] : [$endpoint]);

            return;
        }
        if (count($this->pending) >= self::MAX_GROUPS) {
            ++$this->pendingDropped;

            return;
        }
        $this->pending[$key] = [
            'level' => $level, 'message' => $message, 'file' => $file, 'line' => (int) $line, 'count' => 1, 'first' => $now, 'last' => $now,
            'module' => $module, 'surface' => $surface, 'eps' => $endpoint === null ? [] : [$endpoint],
        ];
    }

    /**
     * Where the request comes from: the surface (app | editor | backoffice | api | cron) and a short number for the
     * endpoint (module/controller/action, numbers dropped, nothing of the query string), counted per group.
     *
     * @return array [surface, endpoint number or null]
     */
    private function requestContext()
    {
        try {
            $server = $this->server !== null ? (array) call_user_func($this->server) : (isset($_SERVER) ? $_SERVER : []);

            return [self::surfaceOf($server, $this->server !== null ? 'fpm' : PHP_SAPI), self::endpointOf($server)];
        } catch (\Throwable $e) {
            return ['editor', null];
        }
    }

    /**
     * @param array $server
     * @param string $sapi
     * @return string app | editor | backoffice | api | cron
     */
    public static function surfaceOf(array $server, $sapi = 'fpm')
    {
        if ($sapi === 'cli' || !isset($server['REQUEST_URI'])) {
            return 'cron';
        }
        if (Quarantine::isAppRequest($server)) {
            return 'app';
        }
        $segments = self::segments($server);
        if (isset($segments[0]) && $segments[0] === 'api') {
            return 'api';
        }
        foreach (array_slice($segments, 0, 2) as $segment) {
            if (strpos($segment, 'backoffice') === 0) {
                return 'backoffice';
            }
        }

        return 'editor';
    }

    /**
     * @param array $server
     * @return int|null crc32 of the first three path segments without the numeric ones
     */
    public static function endpointOf(array $server)
    {
        $path = Quarantine::endpointPath($server);

        return $path === '' ? null : crc32($path);
    }

    /**
     * @param array $server
     * @return string[] lower case path segments, numeric ones dropped
     */
    private static function segments(array $server)
    {
        $path = strtolower((string) parse_url((string) (isset($server['REQUEST_URI']) ? $server['REQUEST_URI'] : ''), PHP_URL_PATH));

        return array_values(array_filter(explode('/', $path), function ($segment) {
            return $segment !== '' && !ctype_digit($segment);
        }));
    }

    /**
     * @param int[] $a
     * @param int[] $b
     * @return int[] the union, at most MAX_ENDPOINTS
     */
    private static function mergeEndpoints(array $a, array $b)
    {
        return array_slice(array_values(array_unique(array_merge($a, $b))), 0, self::MAX_ENDPOINTS);
    }

    /**
     * One line, no values: what makes two occurrences of the same bug look different is removed.
     *
     * @param string $message
     * @return string
     */
    public function normalize($message)
    {
        // "Uncaught Exception: boom in /path:12\nStack trace:\n#0 ..." keeps its first line only.
        $message = preg_replace('/\s*\R?Stack trace:.*$/s', '', (string) $message);
        $message = preg_split('/\R/', (string) $message)[0];
        if ($this->root !== '') {
            $message = str_replace([$this->root . '/', $this->root], ['', ''], str_replace('\\', '/', $message));
        }
        $message = preg_replace([
            '/[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}/',   // e-mail addresses
            '#https?://[^\s\'"]+#i',                              // URLs, with their query strings
            '/\'[^\']*\'/',                                       // quoted values
            '/"[^"]*"/',
            '/\b[0-9a-f]{16,}\b/i',                               // keys, hashes, tokens
            '/\b\d+(?:\.\d+)*\b/',                                // numbers
            '#(?<![\w.])(?:/[\w.\-]+){2,}#',                      // absolute paths left in the text
        ], ['?', '?', '?', '?', '?', 'N', '?'], $message);

        return trim(mb_substr((string) $message, 0, self::MAX_MESSAGE));
    }

    /**
     * @param string $file
     * @return string relative to the install root; outside it only the last two segments are kept
     */
    public function relativePath($file)
    {
        $file = str_replace('\\', '/', (string) $file);
        if ($this->root !== '' && strpos($file, $this->root . '/') === 0) {
            return substr($file, strlen($this->root) + 1);
        }
        if ($file !== '' && ($file[0] === '/' || preg_match('#^[A-Za-z]:/#', $file))) {
            return implode('/', array_slice(explode('/', $file), -2));
        }

        return $file;
    }

    // --- the buffer file -------------------------------------------------------------------------------

    /**
     * Merges what this request saw into the buffer file. Dropped when reporting is off.
     */
    public function flush()
    {
        if ($this->pending === [] && $this->pendingDropped === 0) {
            return;
        }
        $pending = $this->pending;
        $dropped = $this->pendingDropped;
        $this->pending = [];
        $this->pendingDropped = 0;
        if ($this->enabled !== null && !call_user_func($this->enabled)) {
            return;
        }
        $this->merge($pending, $dropped);
        $this->remember($pending);
    }

    // --- the local history of the app calls (the dashboard card) ---------------------------------------

    /**
     * @return string the rolling history, apart from the buffer: the hourly push empties the buffer
     */
    public function localFile()
    {
        return dirname($this->file) . '/error-reporter-local.json';
    }

    /**
     * Nothing is kept while reporting is off: the history goes with the buffer.
     */
    public function forgetLocal()
    {
        @unlink($this->localFile());
    }

    /**
     * Keeps the fatal errors and errors of app calls that belong to a module, in buckets of LOCAL_BUCKET seconds for
     * LOCAL_KEEP seconds, so the dashboard can say which module breaks the apps. Written under the same switch as
     * the buffer. Never throws.
     *
     * @param array $groups key => group, from flush()
     */
    private function remember(array $groups)
    {
        try {
            $rows = array_filter($groups, function ($group) {
                return isset($group['surface'], $group['module']) && $group['surface'] === 'app' && $group['module'] !== '' &&
                    in_array($group['level'], ['fatal', 'error', 'exception'], true);
            });
            if ($rows === []) {
                return;
            }
            $handle = @fopen($this->localFile(), 'c+');
            if ($handle === false) {
                return;
            }
            if (flock($handle, LOCK_EX)) {
                $now = call_user_func($this->clock);
                $bucket = $now - ($now % self::LOCAL_BUCKET);
                $state = $this->decodeLocal(stream_get_contents($handle));
                foreach ($rows as $group) {
                    $name = (string) $group['module'];
                    $module = isset($state['modules'][$name]) ? $state['modules'][$name] : ['last' => 0, 'msg' => '', 'b' => []];
                    $slot = isset($module['b'][$bucket]) ? $module['b'][$bucket] : ['f' => 0, 'e' => 0, 'x' => []];
                    $slot[$group['level'] === 'fatal' ? 'f' : 'e'] += (int) $group['count'];
                    $slot['x'] = self::mergeEndpoints($slot['x'], isset($group['eps']) ? (array) $group['eps'] : []);
                    $module['b'][$bucket] = $slot;
                    if ($group['last'] >= $module['last']) {
                        $module['last'] = (int) $group['last'];
                        if ($group['level'] === 'fatal' || $module['msg'] === '') {
                            $module['msg'] = (string) $group['message'];
                        }
                    }
                    $state['modules'][$name] = $module;
                }
                foreach ($state['modules'] as $name => $module) {
                    foreach ($module['b'] as $start => $slot) {
                        if ($start + self::LOCAL_BUCKET <= $now - self::LOCAL_KEEP) {
                            unset($state['modules'][$name]['b'][$start]);
                        }
                    }
                    if ($state['modules'][$name]['b'] === []) {
                        unset($state['modules'][$name]);
                    }
                }
                uasort($state['modules'], function ($a, $b) {
                    return $b['last'] <=> $a['last'];
                });
                $state['modules'] = array_slice($state['modules'], 0, self::LOCAL_MAX_MODULES, true);
                ftruncate($handle, 0);
                rewind($handle);
                fwrite($handle, json_encode($state));
                fflush($handle);
                flock($handle, LOCK_UN);
            }
            fclose($handle);
        } catch (\Throwable $e) {
            // never get in the way
        }
    }

    /**
     * @param string|false $raw
     * @return array {modules: name => {last, msg, b: bucket start => {f, e, x}}}
     */
    private function decodeLocal($raw)
    {
        $state = json_decode((string) $raw, true);
        if (!is_array($state) || !isset($state['modules']) || !is_array($state['modules'])) {
            return ['modules' => []];
        }

        return ['modules' => array_filter($state['modules'], function ($module) {
            return is_array($module) && isset($module['b']) && is_array($module['b']);
        })];
    }

    /**
     * What the dashboard shows: the modules with fatal errors or errors on app calls in the last 24 hours, the worst
     * first, with a warning when a module had WARN_FATALS fatal errors or more in the last WARN_WINDOW seconds. The
     * buckets are LOCAL_BUCKET wide, one that overlaps the window counts, so the window is 10 to 12 minutes in fact.
     *
     * @param int|null $now
     * @return array {since, threshold, window, modules: [{name, fatals, errors, last, endpoints, message, recent_fatals, warning}]}
     */
    public function appErrors($now = null)
    {
        $now = $now === null ? call_user_func($this->clock) : (int) $now;
        $state = $this->decodeLocal(@file_get_contents($this->localFile()));
        $modules = [];
        foreach ($state['modules'] as $name => $module) {
            $fatals = $errors = $recent = 0;
            $endpoints = [];
            foreach ($module['b'] as $start => $slot) {
                if ($start + self::LOCAL_BUCKET <= $now - self::LOCAL_KEEP) {
                    continue;
                }
                $fatals += (int) $slot['f'];
                $errors += (int) $slot['e'];
                $endpoints = self::mergeEndpoints($endpoints, isset($slot['x']) ? (array) $slot['x'] : []);
                if ($start + self::LOCAL_BUCKET > $now - self::WARN_WINDOW) {
                    $recent += (int) $slot['f'];
                }
            }
            if ($fatals + $errors === 0) {
                continue;
            }
            $modules[] = [
                'name' => (string) $name,
                'fatals' => $fatals,
                'errors' => $errors,
                'last' => (int) $module['last'],
                'endpoints' => count($endpoints),
                'message' => (string) $module['msg'],
                'recent_fatals' => $recent,
                'warning' => $recent >= self::WARN_FATALS,
            ];
        }
        usort($modules, function ($a, $b) {
            return [$b['warning'], $b['fatals'] + $b['errors']] <=> [$a['warning'], $a['fatals'] + $a['errors']];
        });

        return [
            'since' => $now - self::LOCAL_KEEP,
            'threshold' => self::WARN_FATALS,
            'window' => self::WARN_WINDOW,
            'modules' => array_slice($modules, 0, 10),
        ];
    }

    /**
     * @param array $groups key => group
     * @param int $dropped
     */
    private function merge(array $groups, $dropped)
    {
        $dir = dirname($this->file);
        if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
            return;
        }
        $handle = @fopen($this->file, 'c+');
        if ($handle === false) {
            return;
        }
        if (flock($handle, LOCK_EX)) {
            $state = $this->decode(stream_get_contents($handle));
            foreach ($groups as $key => $group) {
                if (isset($state['groups'][$key])) {
                    $state['groups'][$key]['count'] += $group['count'];
                    $state['groups'][$key]['last'] = max($state['groups'][$key]['last'], $group['last']);
                    $state['groups'][$key]['first'] = min($state['groups'][$key]['first'], $group['first']);
                    $state['groups'][$key]['eps'] = self::mergeEndpoints(
                        isset($state['groups'][$key]['eps']) ? (array) $state['groups'][$key]['eps'] : [],
                        isset($group['eps']) ? (array) $group['eps'] : []
                    );
                } elseif (count($state['groups']) < self::MAX_GROUPS) {
                    $state['groups'][$key] = $group;
                } else {
                    ++$state['dropped'];
                }
            }
            $state['dropped'] += $dropped;
            ftruncate($handle, 0);
            rewind($handle);
            fwrite($handle, json_encode($state));
            fflush($handle);
            flock($handle, LOCK_UN);
        }
        fclose($handle);
    }

    /**
     * @param string|false $raw
     * @return array {groups: key => group, dropped: int}
     */
    private function decode($raw)
    {
        $state = json_decode((string) $raw, true);
        if (!is_array($state) || !isset($state['groups']) || !is_array($state['groups'])) {
            return ['groups' => [], 'dropped' => 0];
        }

        return ['groups' => $state['groups'], 'dropped' => isset($state['dropped']) ? (int) $state['dropped'] : 0];
    }

    /**
     * Empties the buffer and hands its content over, for the push.
     *
     * @return array {groups: key => group, dropped: int}
     */
    public function take()
    {
        $handle = @fopen($this->file, 'c+');
        if ($handle === false) {
            return ['groups' => [], 'dropped' => 0];
        }
        $state = ['groups' => [], 'dropped' => 0];
        if (flock($handle, LOCK_EX)) {
            $state = $this->decode(stream_get_contents($handle));
            ftruncate($handle, 0);
            flock($handle, LOCK_UN);
        }
        fclose($handle);

        return $state;
    }

    /**
     * A push that failed gives the groups back, merged with what came in meanwhile.
     *
     * @param array $state from take()
     */
    public function putBack(array $state)
    {
        $this->merge($state['groups'], $state['dropped']);
    }

    // --- sending ---------------------------------------------------------------------------------------

    /**
     * @param array $state from take()
     * @param array $install edition, siberian, php
     * @return array the report body, groups most frequent first
     */
    public static function buildReport(array $state, array $install)
    {
        $groups = array_values($state['groups']);
        usort($groups, function ($a, $b) {
            return $b['count'] <=> $a['count'];
        });
        $dropped = $state['dropped'] + max(0, count($groups) - self::MAX_SENT_GROUPS);

        return [
            'v' => self::SCHEMA,
            'install' => [
                'edition' => strtolower((string) $install['edition']),
                'siberian' => (string) $install['siberian'],
                'php' => (string) $install['php'],
            ],
            'dropped' => $dropped,
            'groups' => array_map([self::class, 'wireGroup'], array_slice($groups, 0, self::MAX_SENT_GROUPS)),
        ];
    }

    /**
     * What leaves for one group: the fields of the contract, with module (a module of app/local/modules, by its
     * folder name, omitted for the others), surface and endpoints (how many distinct endpoints it came from). The
     * endpoint numbers themselves stay here.
     *
     * @param array $group
     * @return array
     */
    private static function wireGroup(array $group)
    {
        $wire = $group;
        unset($wire['eps'], $wire['module'], $wire['surface']);
        if (!empty($group['module'])) {
            $wire['module'] = (string) $group['module'];
        }
        if (isset($group['surface'])) {
            $wire['surface'] = (string) $group['surface'];
            $wire['endpoints'] = isset($group['eps']) ? count((array) $group['eps']) : 0;
        }

        return $wire;
    }

    /**
     * The hourly job.
     *
     * @param Transport $transport
     * @param Store $store install id, secret and back-off state
     * @param array $install edition, siberian, php
     * @param string $baseUrl
     * @return string empty | backoff | sent | retry | dropped
     */
    public function push(Transport $transport, Store $store, array $install, $baseUrl = self::BASE_URL)
    {
        $now = call_user_func($this->clock);
        if ((int) $store->get(self::K_BACKOFF_UNTIL) > $now) {
            return 'backoff';
        }
        $state = $this->take();
        if ($state['groups'] === []) {
            return 'empty';
        }
        $id = $store->get(self::K_INSTALL_ID);
        $secret = $store->get(self::K_SECRET);
        if (!preg_match('/^[a-f0-9]{32}$/', (string) $id) || !preg_match('/^[a-f0-9]{48}$/', (string) $secret)) {
            $id = bin2hex(random_bytes(16));
            $secret = bin2hex(random_bytes(24));
            $store->set(self::K_INSTALL_ID, $id);
            $store->set(self::K_SECRET, $secret);
        }
        $body = json_encode(self::buildReport($state, $install));
        try {
            $response = $transport->request('POST', rtrim($baseUrl, '/') . self::PATH, [
                'Content-Type: application/json',
                'Accept: application/json',
                'X-Install-Id: ' . $id,
                'X-Install-Key: ' . $secret,
                'User-Agent: Siberian/' . $install['siberian'],
            ], $body, 5, 16384);
        } catch (\Throwable $e) {
            $response = ['status' => 0, 'headers' => [], 'body' => ''];
        }
        $status = (int) $response['status'];
        if ($status === 202) {
            $store->set(self::K_FAILURES, '0');
            $store->set(self::K_BACKOFF_UNTIL, '0');

            return 'sent';
        }
        if (in_array($status, [400, 403, 413, 422], true)) {
            // The downloader will not take this report: keep nothing, ask for a new identity after a refusal of the key.
            if ($status === 403) {
                $store->set(self::K_INSTALL_ID, '');
                $store->set(self::K_SECRET, '');
            }
            $store->set(self::K_BACKOFF_UNTIL, (string) ($now + 6 * 3600));

            return 'dropped';
        }
        // Down, busy or throttled: keep the groups and wait, longer each time.
        $this->putBack($state);
        $failures = (int) $store->get(self::K_FAILURES) + 1;
        $store->set(self::K_FAILURES, (string) $failures);
        $wait = min(self::BACKOFF_MAX, self::BACKOFF_BASE * (1 << min($failures - 1, 5)));
        $decoded = json_decode((string) $response['body'], true);
        if (isset($decoded['error']['retry_after'])) {
            $wait = max($wait, min(self::BACKOFF_MAX, (int) $decoded['error']['retry_after']));
        } elseif (isset($decoded['retry_after'])) {
            $wait = max($wait, min(self::BACKOFF_MAX, (int) $decoded['retry_after']));
        }
        $store->set(self::K_BACKOFF_UNTIL, (string) ($now + $wait));

        return 'retry';
    }
}
