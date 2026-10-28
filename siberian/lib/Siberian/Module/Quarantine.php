<?php

namespace Siberian\Module;

/**
 * Quarantine for the modules of app/local/modules.
 *
 * A module written for an older PHP or an older core can stop the whole application with a fatal error that no
 * try/catch can stop, for example a method that does not match the core one any more (a compile error since PHP 8).
 * One broken module then breaks every page that touches it, and the editor shows an error page.
 *
 * A module is quarantined for one of three reasons:
 *  - fatal:   a fatal error came from a file of app/local/modules/<Module>/ while a page was served. The shutdown
 *             handler records it and the request is replayed once, without the module.
 *  - probe:   a compatibility check (`php cli module:quarantine probe|scan|recheck`, or the dashboard) loaded the
 *             module's classes in a separate process and that process died.
 *  - version: the module asks for a newer core than the one installed (package.json, dependencies.system.version).
 *
 * A quarantined module is treated as disabled: controllers, bootstrap, cron, design cache and the backoffice list
 * all ask Installer_Model_Installer_Module::sGetIsEnabled(), and the classes are not autoloaded. A quarantine never
 * expires by itself: after the module, the core or PHP changed it is flagged "stale", and the dashboard asks to
 * recheck it (the module is loaded again in a separate process, and released when it works) or to re-enable it.
 *
 * The records live in var/quarantine/modules.json, outside the caches that "clear cache" empties. Core modules are
 * never quarantined. `$_config['module_quarantine'] = false;` turns the whole thing off.
 */
class Quarantine
{
    /**
     * The error types that end the request.
     */
    public const FATAL = E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR | E_RECOVERABLE_ERROR;

    public const RETRY_COOKIE = 'sb_quarantine_retry';

    public const REASON_FATAL = 'fatal';
    public const REASON_PROBE = 'probe';
    public const REASON_VERSION = 'version';

    /**
     * The folders of a module whose classes are loaded by a probe. Install scripts, resources and bootstrap files
     * are not, they may do things when included.
     */
    public const PROBE_FOLDERS = ['Model', 'Form', 'Plugin', 'controllers'];

    /**
     * @var string|null
     */
    private static $root;

    /**
     * @var array|null
     */
    private static $entries;

    /**
     * @var string
     */
    private static $reason = self::REASON_FATAL;

    /**
     * @var string|null The module a probe process is loading, any fatal error there is its fault
     */
    private static $probing;

    /**
     * @param string|null $root The siberian folder, tests point it to a temporary one
     */
    public static function setRoot(?string $root): void
    {
        self::$root = $root;
        self::$entries = null;
    }

    public static function root(): string
    {
        return self::$root ?? dirname(__DIR__, 3);
    }

    public static function path(): string
    {
        return self::root() . '/var/quarantine/modules.json';
    }

    public static function scanPath(): string
    {
        return self::root() . '/var/quarantine/scan.json';
    }

    public static function isEnabled(): bool
    {
        global $_config;

        return !(is_array($_config) && array_key_exists('module_quarantine', $_config) &&
            $_config['module_quarantine'] === false);
    }

    /**
     * The module of app/local/modules a file belongs to, null for anything else.
     */
    public static function moduleFromFile($file): ?string
    {
        if (!is_string($file) || $file === '') {
            return null;
        }

        $file = str_replace('\\', '/', $file);
        if (preg_match('#/app/local/modules/([A-Za-z][A-Za-z0-9_]*)/#', $file, $matches) === 1) {
            return $matches[1];
        }

        return null;
    }

    public static function isFatal($type): bool
    {
        return is_int($type) && ($type & self::FATAL) !== 0;
    }

    public static function coreVersion(): string
    {
        $version = self::root() . '/lib/Siberian/Version.php';
        if (is_readable($version) &&
            preg_match("/const\s+VERSION\s*=\s*'([^']*)'/", (string) file_get_contents($version), $matches) === 1) {
            return $matches[1];
        }

        return '';
    }

    public static function moduleVersion(string $module): string
    {
        $package = self::package($module);

        return isset($package['version']) ? (string) $package['version'] : '';
    }

    private static function package(string $module): array
    {
        $file = self::root() . '/app/local/modules/' . $module . '/package.json';
        if (!is_readable($file)) {
            return [];
        }
        $json = json_decode((string) file_get_contents($file), true);

        return is_array($json) ? $json : [];
    }

    /**
     * What the module, the core and PHP were when the module was quarantined or checked.
     */
    public static function fingerprint(string $module): string
    {
        return sprintf(
            'php%d.%d|core%s|module%s',
            PHP_MAJOR_VERSION,
            PHP_MINOR_VERSION,
            self::coreVersion(),
            self::moduleVersion($module));
    }

    /**
     * The environment a scan ran in.
     */
    public static function environment(): string
    {
        return sprintf('php%d.%d|core%s', PHP_MAJOR_VERSION, PHP_MINOR_VERSION, self::coreVersion());
    }

    /**
     * The message when the module asks for a newer core than the installed one.
     */
    public static function checkVersion(string $module): ?string
    {
        $package = self::package($module);
        $required = $package['dependencies']['system']['version'] ?? null;
        $core = self::coreVersion();
        if (!is_string($required) || $required === '' || $core === '') {
            return null;
        }

        if (version_compare($core, $required, '<')) {
            return sprintf('The module needs Siberian %s or newer, this one is %s.', $required, $core);
        }

        return null;
    }

    /**
     * Every record in the file.
     */
    private static function readFile(): array
    {
        $path = self::path();
        if (!is_readable($path)) {
            return [];
        }

        $json = json_decode((string) file_get_contents($path), true);

        return is_array($json) ? $json : [];
    }

    /**
     * The modules that are quarantined, by name.
     */
    public static function all(): array
    {
        if (self::$entries === null) {
            self::$entries = [];
            foreach (self::readFile() as $module => $entry) {
                if (is_array($entry)) {
                    self::$entries[(string) $module] = $entry;
                }
            }
        }

        return self::$entries;
    }

    /**
     * The record of a quarantined module, with "stale" when the module, the core or PHP changed since.
     */
    public static function get($module): ?array
    {
        if (!self::isEnabled()) {
            return null;
        }

        $entry = self::all()[(string) $module] ?? null;
        if ($entry === null) {
            return null;
        }

        $entry['name'] = (string) $module;
        $entry['stale'] = ($entry['fingerprint'] ?? null) !== self::fingerprint((string) $module);
        $entry['version'] = self::moduleVersion((string) $module);

        return $entry;
    }

    public static function isQuarantined($module): bool
    {
        return self::get($module) !== null;
    }

    /**
     * The folders of app/local/modules.
     */
    public static function localModules(): array
    {
        $modules = [];
        $base = self::root() . '/app/local/modules';
        if (is_dir($base)) {
            foreach (scandir($base) as $name) {
                if (preg_match('/^[A-Za-z][A-Za-z0-9_]*$/', $name) === 1 && is_dir($base . '/' . $name)) {
                    $modules[] = $name;
                }
            }
        }

        return $modules;
    }

    /**
     * What the dashboard shows.
     */
    public static function summary(): array
    {
        $items = [];
        foreach (array_keys(self::all()) as $module) {
            $entry = self::get($module);
            if ($entry !== null) {
                $items[] = $entry;
            }
        }
        usort($items, function ($a, $b) {
            return strcmp($a['name'], $b['name']);
        });

        $scan = self::scanState();
        $stale = count(array_filter($items, function ($item) {
            return $item['stale'];
        }));

        return [
            'total' => count(self::localModules()),
            'quarantined' => count($items),
            'stale' => $stale,
            'items' => $items,
            'scan' => $scan,
            'scan_outdated' => !empty($scan['environment']) && $scan['environment'] !== self::environment(),
        ];
    }

    public static function scanState(): array
    {
        $path = self::scanPath();
        if (!is_readable($path)) {
            return [];
        }
        $json = json_decode((string) file_get_contents($path), true);

        return is_array($json) ? $json : [];
    }

    private static function writeJson(string $path, array $data): bool
    {
        $dir = dirname($path);
        if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
            return false;
        }

        $tmp = $path . '.' . getmypid() . '.tmp';
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if ($json === false || file_put_contents($tmp, $json . PHP_EOL) === false) {
            return false;
        }
        @chmod($tmp, 0664);
        $renamed = @rename($tmp, $path);
        if (!$renamed) {
            @unlink($tmp);
        }
        self::$entries = null;

        return $renamed;
    }

    /**
     * Autoloading refuses the classes of a quarantined module, unless a core module has the same name.
     */
    public static function blocksClass($class): bool
    {
        if (!self::isEnabled() || !is_string($class) || strpos($class, '_') === false) {
            return false;
        }

        $all = self::all();
        if (empty($all)) {
            return false;
        }

        $module = strstr($class, '_', true);
        if (!isset($all[$module]) || $module === self::$probing) {
            return false;
        }

        foreach (['sae', 'mae', 'pe'] as $edition) {
            if (is_dir(self::root() . '/app/' . $edition . '/modules/' . $module)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param string $module
     * @param array $error An error_get_last() like array
     * @param string|null $reason One of the REASON_ constants, the current one by default
     * @return bool True when the module was not quarantined before
     */
    public static function add(string $module, array $error, ?string $reason = null): bool
    {
        try {
            $isNew = !self::isQuarantined($module);
            $records = self::readFile();
            $records[$module] = [
                'reason' => $reason ?? self::$reason,
                'fingerprint' => self::fingerprint($module),
                'type' => $error['type'] ?? null,
                'message' => substr((string) ($error['message'] ?? ''), 0, 1000),
                'file' => (string) ($error['file'] ?? ''),
                'line' => (int) ($error['line'] ?? 0),
                'at' => date('c'),
            ];

            return self::writeJson(self::path(), $records) && $isNew;
        } catch (\Throwable $e) {
            return false;
        }
    }

    public static function release(string $module): bool
    {
        try {
            $records = self::readFile();
            if (!array_key_exists($module, $records)) {
                return false;
            }
            unset($records[$module]);

            return self::writeJson(self::path(), $records);
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Records the module a fatal error comes from.
     *
     * @param array|null $error error_get_last()
     * @return array|null [module, isNew] or null when the error is not a module one
     */
    public static function handleError(?array $error): ?array
    {
        if (!self::isEnabled() || $error === null || !self::isFatal($error['type'] ?? null)) {
            return null;
        }

        $module = self::$probing ?? self::moduleFromFile($error['file'] ?? null);
        if ($module === null) {
            return null;
        }

        return [$module, self::add($module, $error)];
    }

    /**
     * What the visitor gets once the module is quarantined: the same page again when it can be replayed, an error
     * with an explanation otherwise.
     *
     * @return array [code, headers, body, cookie]
     */
    public static function plan(string $module, array $server, array $cookies, bool $isNew): array
    {
        $message = sprintf('The module %s stopped the application and was put in quarantine, reload the page.', $module);
        $method = strtoupper((string) ($server['REQUEST_METHOD'] ?? 'GET'));
        $uri = (string) ($server['REQUEST_URI'] ?? '');
        $isAjax = strtolower((string) ($server['HTTP_X_REQUESTED_WITH'] ?? '')) === 'xmlhttprequest' ||
            stripos((string) ($server['HTTP_ACCEPT'] ?? ''), 'application/json') === 0;
        $canReplay = in_array($method, ['GET', 'HEAD'], true) &&
            !$isAjax &&
            $isNew &&
            !isset($cookies[self::RETRY_COOKIE]) &&
            preg_match('#^/(?!/)[^\r\n]*$#', $uri) === 1;

        if ($canReplay) {
            return [302, ['Location: ' . $uri], '', [self::RETRY_COOKIE, $module, time() + 60]];
        }

        if ($isAjax) {
            return [
                503,
                ['Content-Type: application/json'],
                json_encode(['error' => true, 'quarantined' => $module, 'message' => $message]),
                null,
            ];
        }

        return [503, ['Content-Type: text/plain; charset=utf-8'], $message, null];
    }

    public static function register(): void
    {
        register_shutdown_function([self::class, 'onShutdown']);
    }

    public static function onShutdown(): void
    {
        $error = error_get_last();
        $handled = self::handleError($error);
        if ($handled === null) {
            return;
        }

        [$module, $isNew] = $handled;
        error_log(sprintf('[module quarantine] %s: %s', $module, (string) ($error['message'] ?? '')));

        if (PHP_SAPI === 'cli' || headers_sent()) {
            return;
        }

        [$code, $headers, $body, $cookie] = self::plan($module, $_SERVER, $_COOKIE, $isNew);
        while (ob_get_level() > 0) {
            @ob_end_clean();
        }
        http_response_code($code);
        foreach ($headers as $header) {
            // The fatal error already set a 500, the code is forced with every header.
            header($header, true, $code);
        }
        if ($cookie !== null) {
            setcookie($cookie[0], $cookie[1], $cookie[2], '/');
        }
        echo $body;
    }

    /**
     * The PHP command line binary that matches the PHP serving the pages (PHP_BINARY is php-fpm for the web).
     */
    public static function phpBinary(): ?string
    {
        $candidates = [];
        if (PHP_SAPI === 'cli') {
            $candidates[] = PHP_BINARY;
        }
        $candidates[] = dirname(PHP_BINARY) . '/php';
        $candidates[] = dirname(PHP_BINARY, 2) . '/bin/php';
        $candidates[] = '/usr/bin/php' . PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION;
        $candidates[] = '/usr/local/bin/php';
        $candidates[] = '/usr/bin/php';

        foreach ($candidates as $candidate) {
            if ($candidate === '' || !is_file($candidate) || !is_executable($candidate)) {
                continue;
            }
            $output = [];
            $code = 1;
            @exec(escapeshellarg($candidate) . ' -r ' . escapeshellarg('echo PHP_SAPI, " ", PHP_MAJOR_VERSION, ".", PHP_MINOR_VERSION;'), $output, $code);
            if ($code === 0 && ($output[0] ?? '') === 'cli ' . PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * The files a probe loads.
     */
    public static function probeFiles(string $module): array
    {
        $files = [];
        foreach (self::PROBE_FOLDERS as $folder) {
            $dir = self::root() . '/app/local/modules/' . $module . '/' . $folder;
            if (!is_dir($dir)) {
                continue;
            }
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS));
            foreach ($iterator as $file) {
                if ($file->isFile() && strtolower($file->getExtension()) === 'php') {
                    $files[] = $file->getPathname();
                }
            }
        }
        sort($files);

        return $files;
    }

    /**
     * Loads the classes of a module, called in a separate process because a fatal error ends it.
     */
    public static function probe(string $module): int
    {
        self::$probing = $module;
        self::$reason = self::REASON_PROBE;
        self::register();

        ob_start();
        foreach (self::probeFiles($module) as $file) {
            // Two files of a module can declare the same class (a leftover copy), the dispatcher only loads one of
            // them per URL, so a class that is already there is not a reason to quarantine the module.
            if (self::declaresLoadedType($file)) {
                continue;
            }
            include_once $file;
        }
        ob_end_clean();

        return 0;
    }

    /**
     * True when the file declares a class, interface or trait that exists already.
     */
    public static function declaresLoadedType(string $file): bool
    {
        $tokens = @token_get_all((string) file_get_contents($file));
        $namespace = '';
        $count = count($tokens);
        for ($i = 0; $i < $count; $i++) {
            $token = $tokens[$i];
            if (!is_array($token)) {
                continue;
            }

            if ($token[0] === T_NAMESPACE) {
                $namespace = '';
                for ($j = $i + 1; $j < $count; $j++) {
                    $next = $tokens[$j];
                    if (is_array($next) && in_array($next[0], [T_STRING, T_NAME_QUALIFIED], true)) {
                        $namespace = $next[1];
                        break;
                    }
                    if ($next === ';' || $next === '{') {
                        break;
                    }
                }
                continue;
            }

            if (!in_array($token[0], [T_CLASS, T_INTERFACE, T_TRAIT], true)) {
                continue;
            }

            // "Foo::class" and "new class" are not declarations
            $previous = $tokens[$i - 1] ?? null;
            if (is_array($previous) && in_array($previous[0], [T_DOUBLE_COLON, T_NEW], true)) {
                continue;
            }
            for ($j = $i + 1; $j < $count; $j++) {
                $next = $tokens[$j];
                if (is_array($next) && $next[0] === T_WHITESPACE) {
                    continue;
                }
                if (is_array($next) && $next[0] === T_STRING) {
                    $name = ($namespace !== '' ? $namespace . '\\' : '') . $next[1];
                    if (class_exists($name, false) || interface_exists($name, false) || trait_exists($name, false)) {
                        return true;
                    }
                }
                break;
            }
        }

        return false;
    }

    /**
     * Runs the probe of a module in a separate process.
     *
     * @return array [ok, message]
     */
    public static function runProbe(string $module, int $timeout = 120): array
    {
        $php = self::phpBinary();
        if ($php === null) {
            return [null, sprintf('No PHP command line %d.%d found to check the module.', PHP_MAJOR_VERSION, PHP_MINOR_VERSION)];
        }

        $process = @proc_open(
            [$php, '-d', 'display_errors=0', '-d', 'memory_limit=512M', 'cli', 'module:quarantine', 'probe', $module],
            [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
            $pipes,
            self::root());
        if (!is_resource($process)) {
            return [null, 'The check could not be started.'];
        }

        fclose($pipes[0]);
        stream_set_blocking($pipes[1], false);
        stream_set_blocking($pipes[2], false);
        $output = '';
        $deadline = time() + $timeout;
        do {
            $output .= (string) stream_get_contents($pipes[1]) . (string) stream_get_contents($pipes[2]);
            $status = proc_get_status($process);
            if ($status['running']) {
                usleep(100000);
            }
        } while ($status['running'] && time() < $deadline);

        if ($status['running']) {
            proc_terminate($process, 9);
            proc_close($process);

            return [false, 'The check took too long.'];
        }

        $output .= (string) stream_get_contents($pipes[1]) . (string) stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $exit = $status['exitcode'] !== -1 ? $status['exitcode'] : proc_close($process);
        if ($status['exitcode'] !== -1) {
            proc_close($process);
        }

        return [$exit === 0, trim(substr($output, -500))];
    }

    /**
     * Checks one module again and releases it when it loads.
     *
     * @return array [status, message] status is released, quarantined, ok or unavailable
     */
    public static function recheck(string $module): array
    {
        $wasQuarantined = self::isQuarantined($module);

        if (!is_dir(self::root() . '/app/local/modules/' . $module)) {
            self::release($module);

            return ['released', 'The module is not installed any more.'];
        }

        $versionMessage = self::checkVersion($module);
        if ($versionMessage !== null) {
            self::add($module, ['type' => 0, 'message' => $versionMessage, 'file' => '', 'line' => 0], self::REASON_VERSION);

            return ['quarantined', $versionMessage];
        }

        [$ok, $message] = self::runProbe($module);
        if ($ok === null) {
            return ['unavailable', $message];
        }

        if ($ok) {
            self::release($module);

            return [$wasQuarantined ? 'released' : 'ok', ''];
        }

        // The probe process recorded the fatal error itself, otherwise record what it said.
        if (!self::isQuarantined($module) || self::get($module)['stale']) {
            self::add($module, ['type' => E_ERROR, 'message' => $message, 'file' => '', 'line' => 0], self::REASON_PROBE);
        }

        return ['quarantined', (string) (self::get($module)['message'] ?? $message)];
    }

    /**
     * Checks every module of app/local/modules that is not disabled by hand.
     *
     * @return array The counts
     */
    public static function scan(): array
    {
        $state = ['status' => 'running', 'started_at' => date('c'), 'environment' => self::environment()];
        self::writeJson(self::scanPath(), $state);

        $checked = 0;
        $quarantined = 0;
        $released = 0;
        foreach (self::localModules() as $module) {
            if (is_readable(self::root() . '/app/local/modules/' . $module . '/module.disabled')) {
                continue;
            }
            $checked++;
            [$status] = self::recheck($module);
            if ($status === 'quarantined') {
                $quarantined++;
            } elseif ($status === 'released') {
                $released++;
            }
        }

        $state = [
            'status' => 'done',
            'started_at' => $state['started_at'],
            'finished_at' => date('c'),
            'environment' => self::environment(),
            'checked' => $checked,
            'quarantined' => $quarantined,
            'released' => $released,
        ];
        self::writeJson(self::scanPath(), $state);

        return $state;
    }

    /**
     * Starts a scan in the background, it can take a while.
     */
    public static function startScan(): bool
    {
        $php = self::phpBinary();
        if ($php === null || !function_exists('exec')) {
            return false;
        }

        $state = self::scanState();
        if (($state['status'] ?? null) === 'running' && strtotime((string) ($state['started_at'] ?? '')) > time() - 900) {
            return true;
        }

        self::writeJson(self::scanPath(), array_merge($state, [
            'status' => 'running',
            'started_at' => date('c'),
            'environment' => self::environment(),
        ]));
        exec(sprintf(
            'cd %s && nohup %s cli module:quarantine scan > /dev/null 2>&1 &',
            escapeshellarg(self::root()),
            escapeshellarg($php)));

        return true;
    }

    /**
     * `php cli module:quarantine <list|release|recheck|probe|scan> [Module]`
     */
    public static function cli(array $argv): int
    {
        $command = $argv[2] ?? 'list';
        $module = $argv[3] ?? null;

        switch ($command) {
            case 'probe':
                return $module === null ? 2 : self::probe($module);
            case 'release':
                if ($module === null) {
                    echo 'Usage: module:quarantine release <Module>' . PHP_EOL;

                    return 2;
                }
                echo self::release($module) ? "{$module} released." : "{$module} is not quarantined.";
                echo PHP_EOL;

                return 0;
            case 'recheck':
                $modules = $module !== null ? [$module] : array_keys(self::all());
                foreach ($modules as $name) {
                    [$status, $message] = self::recheck($name);
                    echo sprintf('%s: %s %s', $name, $status, $message) . PHP_EOL;
                }

                return 0;
            case 'scan':
                $state = self::scan();
                echo sprintf(
                    'Checked %d modules, %d quarantined, %d released.',
                    $state['checked'],
                    $state['quarantined'],
                    $state['released']) . PHP_EOL;

                return 0;
            case 'list':
            default:
                $summary = self::summary();
                echo sprintf('%d/%d modules quarantined.', $summary['quarantined'], $summary['total']) . PHP_EOL;
                foreach ($summary['items'] as $item) {
                    echo sprintf(
                        '- %s (%s%s) %s',
                        $item['name'],
                        $item['reason'] ?? self::REASON_FATAL,
                        $item['stale'] ? ', changed since' : '',
                        $item['message']) . PHP_EOL;
                }

                return 0;
        }
    }
}
