<?php

namespace Siberian;

use Siberian\ModuleUpdates\Store;
use Siberian\ModuleUpdates\Transport;

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
    public function __construct($file, $root, $clock = null, $enabled = null)
    {
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
        $file = $this->relativePath($file);
        $key = sha1($level . "\n" . $message . "\n" . $file . ':' . (int) $line);
        $now = call_user_func($this->clock);
        if (isset($this->pending[$key])) {
            ++$this->pending[$key]['count'];
            $this->pending[$key]['last'] = $now;

            return;
        }
        if (count($this->pending) >= self::MAX_GROUPS) {
            ++$this->pendingDropped;

            return;
        }
        $this->pending[$key] = ['level' => $level, 'message' => $message, 'file' => $file, 'line' => (int) $line, 'count' => 1, 'first' => $now, 'last' => $now];
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
            'groups' => array_slice($groups, 0, self::MAX_SENT_GROUPS),
        ];
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
