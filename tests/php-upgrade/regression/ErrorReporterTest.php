<?php
/**
 * Error reporting (Siberian\ErrorReporter): grouping, scrubbing, the buffer file, the handlers, and the hourly push
 * with its back-off. No network: a scripted transport stands in for the downloader.
 */
$siberian = dirname(__DIR__, 3) . '/siberian';
foreach (['Exception', 'Transport', 'Store'] as $class) {
    require_once $siberian . '/lib/Siberian/ModuleUpdates/' . $class . '.php';
}
require_once $siberian . '/lib/Siberian/ErrorReporter.php';

use Siberian\ErrorReporter;

class ErFakeTransport implements Siberian\ModuleUpdates\Transport
{
    public $calls = [];
    /** @var array responses consumed in order, the last one repeats */
    public $script = [];

    public function request($method, $url, array $headers = [], $body = null, $timeout = 10, $maxBytes = 5242880)
    {
        $this->calls[] = ['method' => $method, 'url' => $url, 'headers' => $headers, 'body' => $body];
        $response = count($this->script) > 1 ? array_shift($this->script) : ($this->script[0] ?? ['status' => 0]);
        if (isset($response['throw'])) {
            throw new RuntimeException('network');
        }

        return $response + ['headers' => [], 'body' => ''];
    }
}

class ErArrayStore implements Siberian\ModuleUpdates\Store
{
    public $data = [];

    public function get($key)
    {
        return isset($this->data[$key]) && $this->data[$key] !== '' ? $this->data[$key] : null;
    }

    public function set($key, $value)
    {
        $this->data[$key] = $value;
    }
}

function er_env(&$now = 1900000000, $enabled = null)
{
    $dir = sys_get_temp_dir() . '/er-' . bin2hex(random_bytes(5));
    $reporter = new ErrorReporter($dir . '/cache/error-reporter.json', '/var/www/siberian', function () use (&$now) {
        return $now;
    }, $enabled);

    return [$reporter, $dir . '/cache/error-reporter.json', new ErFakeTransport(), new ErArrayStore()];
}

function er_fill(ErrorReporter $reporter, $n = 2)
{
    for ($i = 0; $i < $n; $i++) {
        $reporter->record('warning', 'Undefined variable $x', '/var/www/siberian/app/Foo.php', 10);
    }
    $reporter->record('fatal', 'Class "Foo" not found', '/var/www/siberian/app/Bar.php', 5);
    $reporter->flush();
}

function er_install()
{
    return ['edition' => 'SAE', 'siberian' => '5.1.3', 'php' => '8.3.1'];
}

return [
    'normalize removes what makes two occurrences differ' => function () {
        list($r) = er_env();
        t_equal('Undefined array key ?', $r->normalize('Undefined array key "user_42"'), 'quoted');
        t_equal('Call to undefined method Foo::bar()', $r->normalize('Call to undefined method Foo::bar()'), 'kept');
        t_equal('Table ? has N rows for ?', $r->normalize("Table 'orders' has 15 rows for a@b.com"), 'values');
        t_equal('Failed ? at app/x.php', $r->normalize('Failed https://x.test/p?token=abc at /var/www/siberian/app/x.php'), 'url and root');
        t_equal('Error ? in ?', $r->normalize('Error 0123456789abcdef0123 in /home/user/site/file.php'), 'hex and absolute path');
        t_equal('Uncaught Exception: boom in ?:N', $r->normalize("Uncaught Exception: boom in /srv/a/b.php:12\nStack trace:\n#0 /srv/a/c.php(3): f('secret')"), 'stack trace dropped');
        t_equal(299, strlen($r->normalize(str_repeat('word ', 200))), 'cut');
    },
    'paths are relative to the install root, nothing absolute leaves' => function () {
        list($r) = er_env();
        t_equal('app/sae/Foo.php', $r->relativePath('/var/www/siberian/app/sae/Foo.php'), 'inside');
        t_equal('plugins/Baz.php', $r->relativePath('/opt/other/plugins/Baz.php'), 'outside keeps two segments');
        t_equal('x/y.php', $r->relativePath('C:\\www\\x\\y.php'), 'windows');
        t_equal('app/z.php', $r->relativePath('app/z.php'), 'already relative');
    },
    'levels' => function () {
        t_equal('fatal', ErrorReporter::levelOf(E_ERROR), 'e_error');
        t_equal('fatal', ErrorReporter::levelOf(E_PARSE), 'parse');
        t_equal('error', ErrorReporter::levelOf(E_USER_ERROR), 'user error');
        t_equal('warning', ErrorReporter::levelOf(E_WARNING), 'warning');
        t_equal('notice', ErrorReporter::levelOf(E_NOTICE), 'notice');
        t_equal('deprecated', ErrorReporter::levelOf(E_DEPRECATED), 'deprecated');
        t_equal('deprecated', ErrorReporter::levelOf(E_USER_DEPRECATED), 'user deprecated');
    },
    'the same error at the same place is one group with a counter' => function () {
        $now = 1900000000;
        list($r, $file) = er_env($now);
        er_fill($r, 3);
        $state = json_decode(file_get_contents($file), true);
        t_equal(2, count($state['groups']), 'two groups');
        $counts = array_column($state['groups'], 'count', 'level');
        t_equal(3, $counts['warning'], 'counter');
        t_equal(1, $counts['fatal'], 'fatal');
        $g = array_values(array_filter($state['groups'], function ($g) {
            return $g['level'] === 'warning';
        }))[0];
        t_equal('app/Foo.php', $g['file'], 'relative');
        t_equal(10, $g['line'], 'line');
        er_fill($r, 2);
        $state = json_decode(file_get_contents($file), true);
        t_equal(5, array_column($state['groups'], 'count', 'level')['warning'], 'merged across flushes');
    },
    'another line or another message is another group' => function () {
        list($r, $file) = er_env();
        $r->record('warning', 'Same', 'a.php', 1);
        $r->record('warning', 'Same', 'a.php', 2);
        $r->record('warning', 'Other', 'a.php', 1);
        $r->record('notice', 'Same', 'a.php', 1);
        $r->flush();
        t_equal(4, count(json_decode(file_get_contents($file), true)['groups']), 'four');
    },
    'the buffer is capped and counts what it dropped' => function () {
        list($r, $file) = er_env();
        for ($i = 0; $i < ErrorReporter::MAX_GROUPS + 20; $i++) {
            $r->record('warning', 'Message', 'a.php', $i + 1);
        }
        $r->flush();
        $state = json_decode(file_get_contents($file), true);
        t_equal(ErrorReporter::MAX_GROUPS, count($state['groups']), 'groups');
        t_equal(20, $state['dropped'], 'dropped');
        $r->record('warning', 'Message', 'b.php', 1);
        $r->flush();
        $state = json_decode(file_get_contents($file), true);
        t_equal(ErrorReporter::MAX_GROUPS, count($state['groups']), 'still capped');
        t_equal(21, $state['dropped'], 'dropped again');
    },
    'nothing is kept while reporting is off' => function () {
        $now = 1900000000;
        list($r, $file) = er_env($now, function () {
            return false;
        });
        er_fill($r);
        t_equal(false, file_exists($file), 'no file');
    },
    'a suppressed error is not recorded, the others are' => function () {
        list($r, $file) = er_env();
        $before = error_reporting();
        error_reporting(0);
        $r->handleError(E_WARNING, 'hidden', '/x/a.php', 1);
        error_reporting(E_ALL);
        $r->handleError(E_WARNING, 'shown', '/x/a.php', 2);
        error_reporting($before);
        $r->flush();
        $messages = array_column(json_decode(file_get_contents($file), true)['groups'], 'message');
        t_equal(['shown'], array_values($messages), 'only the unsuppressed one');
    },
    'install() keeps what production shows: swallowed, display and PHP log off' => function () {
        list($r, $file) = er_env();
        $level = error_reporting();
        $display = ini_get('display_errors');
        $log = ini_get('log_errors');
        $runner = set_error_handler(null);
        error_reporting(0);
        $r->install();
        t_equal(E_ALL, error_reporting(), 'raised so @ can be told apart');
        t_equal('0', ini_get('display_errors'), 'display off');
        t_equal('0', ini_get('log_errors'), 'log off');
        t_equal(true, $r->handleError(E_WARNING, 'w', '/x/a.php', 1), 'swallowed');
        restore_error_handler();
        set_error_handler($runner);
        error_reporting($level);
        ini_set('display_errors', $display);
        ini_set('log_errors', $log);
    },
    'a handler that was there keeps its say, and outside production PHP carries on' => function () {
        list($r) = er_env();
        $seen = [];
        $previous = set_error_handler(function ($no, $str) use (&$seen) {
            $seen[] = $str;

            return true;
        });
        $level = error_reporting();
        error_reporting(E_ALL);
        $r->install();
        t_equal(true, $r->handleError(E_WARNING, 'chained', '/x/a.php', 1), 'previous result');
        t_equal(['chained'], $seen, 'previous handler called');
        restore_error_handler();
        restore_error_handler();
        error_reporting($level);
        list($plain) = er_env();
        t_equal(false, $plain->handleError(E_WARNING, 'x', '/x/a.php', 1), 'no handler before, not production: PHP carries on');
    },
    'the report has the shape the downloader expects, most frequent first' => function () {
        list($r) = er_env();
        er_fill($r, 4);
        $report = ErrorReporter::buildReport($r->take(), er_install());
        t_equal(1, $report['v'], 'schema');
        t_equal(['edition' => 'sae', 'siberian' => '5.1.3', 'php' => '8.3.1'], $report['install'], 'install');
        t_equal(4, $report['groups'][0]['count'], 'sorted');
        t_equal(['level', 'message', 'file', 'line', 'count', 'first', 'last'], array_keys($report['groups'][0]), 'keys');
        t_equal(0, $report['dropped'], 'dropped');
    },
    'a report carries at most 200 groups and counts the rest as dropped' => function () {
        $groups = [];
        for ($i = 0; $i < 230; $i++) {
            $groups['k' . $i] = ['level' => 'warning', 'message' => 'm', 'file' => 'a.php', 'line' => $i, 'count' => 1, 'first' => 1, 'last' => 1];
        }
        $report = ErrorReporter::buildReport(['groups' => $groups, 'dropped' => 5], er_install());
        t_equal(200, count($report['groups']), 'groups');
        t_equal(35, $report['dropped'], 'dropped');
    },
    'push sends the buffer once with the install identity, then has nothing to send' => function () {
        $now = 1900000000;
        list($r, $file, $transport, $store) = er_env($now);
        er_fill($r, 3);
        $transport->script = [['status' => 202, 'body' => '{"accepted":2,"rejected":0}']];
        t_equal('sent', $r->push($transport, $store, er_install(), 'https://dl.test'), 'sent');
        $call = $transport->calls[0];
        t_equal('POST', $call['method'], 'method');
        t_equal('https://dl.test/api/v1/errors/reports', $call['url'], 'url');
        $headers = implode("\n", $call['headers']);
        t_equal(1, preg_match('/^X-Install-Id: [a-f0-9]{32}$/m', $headers), 'id header');
        t_equal(1, preg_match('/^X-Install-Key: [a-f0-9]{48}$/m', $headers), 'key header');
        $body = json_decode($call['body'], true);
        t_equal(2, count($body['groups']), 'groups');
        t_equal(1, $body['v'], 'v');
        t_equal('', trim((string) file_get_contents($file)), 'file emptied');
        t_equal('empty', $r->push($transport, $store, er_install(), 'https://dl.test'), 'nothing left');
        t_equal(1, count($transport->calls), 'no second request');
        t_equal($store->get(ErrorReporter::K_INSTALL_ID), $store->get(ErrorReporter::K_INSTALL_ID), 'id kept');
    },
    'no absolute path or value leaves in the body' => function () {
        list($r, $file, $transport, $store) = er_env();
        $r->record('warning', "Undefined array key \"secret-token\" in /var/www/siberian/app/a.php for a@b.com", '/var/www/siberian/app/a.php', 3);
        $r->flush();
        $transport->script = [['status' => 202]];
        $r->push($transport, $store, er_install(), 'https://dl.test');
        $body = $transport->calls[0]['body'];
        foreach (['/var/www', 'secret-token', 'a@b.com'] as $leak) {
            t_equal(false, strpos($body, $leak) !== false, 'leaked ' . $leak);
        }
    },
    'the identity is made once and reused' => function () {
        list($r, $file, $transport, $store) = er_env();
        $transport->script = [['status' => 202]];
        er_fill($r);
        $r->push($transport, $store, er_install(), 'https://dl.test');
        $id = $store->get(ErrorReporter::K_INSTALL_ID);
        $secret = $store->get(ErrorReporter::K_SECRET);
        er_fill($r);
        $r->push($transport, $store, er_install(), 'https://dl.test');
        t_equal($id, $store->get(ErrorReporter::K_INSTALL_ID), 'id');
        t_equal($secret, $store->get(ErrorReporter::K_SECRET), 'secret');
        t_equal(true, strpos($transport->calls[1]['body'], $id) === false, 'the id is a header, not in the body');
    },
    'a busy or down downloader keeps the groups and backs off, longer each time' => function () {
        $now = 1900000000;
        list($r, $file, $transport, $store) = er_env($now);
        er_fill($r, 2);
        $transport->script = [['status' => 503, 'body' => '{"error":{"code":"busy","retry_after":7200}}']];
        t_equal('retry', $r->push($transport, $store, er_install(), 'https://dl.test'), 'first failure');
        t_equal((string) ($now + 7200), $store->get(ErrorReporter::K_BACKOFF_UNTIL), 'retry_after wins when longer');
        t_equal('backoff', $r->push($transport, $store, er_install(), 'https://dl.test'), 'waits');
        t_equal(1, count($transport->calls), 'no request while backing off');
        $now += 7201;
        $transport->script = [['status' => 0]];
        t_equal('retry', $r->push($transport, $store, er_install(), 'https://dl.test'), 'second failure');
        t_equal((string) ($now + 7200), $store->get(ErrorReporter::K_BACKOFF_UNTIL), 'doubled from the hour');
        $now += 7201;
        $transport->script = [['status' => 202]];
        t_equal('sent', $r->push($transport, $store, er_install(), 'https://dl.test'), 'finally');
        $body = json_decode(end($transport->calls)['body'], true);
        t_equal(2, count($body['groups']), 'nothing was lost');
        t_equal('0', $store->get(ErrorReporter::K_FAILURES) ?? '0', 'failures reset');
    },
    'a network exception counts as down' => function () {
        list($r, $file, $transport, $store) = er_env();
        er_fill($r);
        $transport->script = [['throw' => true]];
        t_equal('retry', $r->push($transport, $store, er_install(), 'https://dl.test'), 'retry');
    },
    'a refused report is dropped, a refused key asks for a new identity' => function () {
        $now = 1900000000;
        list($r, $file, $transport, $store) = er_env($now);
        er_fill($r);
        $transport->script = [['status' => 422]];
        t_equal('dropped', $r->push($transport, $store, er_install(), 'https://dl.test'), '422');
        t_equal(true, (int) $store->get(ErrorReporter::K_BACKOFF_UNTIL) > $now, 'waits');
        t_equal('empty', (function () use ($r, $store, $transport, &$now) {
            $now += 7 * 3600;

            return $r->push($transport, $store, er_install(), 'https://dl.test');
        })(), 'the refused groups are gone');
        er_fill($r);
        $transport->script = [['status' => 403]];
        $id = $store->get(ErrorReporter::K_INSTALL_ID);
        t_equal('dropped', $r->push($transport, $store, er_install(), 'https://dl.test'), '403');
        t_equal(null, $store->get(ErrorReporter::K_SECRET), 'secret reset');
        $now += 7 * 3600;
        er_fill($r);
        $transport->script = [['status' => 202]];
        $r->push($transport, $store, er_install(), 'https://dl.test');
        t_equal(true, $store->get(ErrorReporter::K_INSTALL_ID) !== $id, 'a new identity');
    },
    'a failed push does not lose what came in meanwhile' => function () {
        list($r, $file, $transport, $store) = er_env();
        er_fill($r, 2);
        $state = $r->take();
        er_fill($r, 1);
        $r->putBack($state);
        $groups = json_decode(file_get_contents($file), true)['groups'];
        t_equal(3, array_column($groups, 'count', 'level')['warning'], 'counts merged');
    },
    'a corrupt buffer file is replaced, not fatal' => function () {
        list($r, $file) = er_env();
        mkdir(dirname($file), 0775, true);
        file_put_contents($file, '{not json');
        er_fill($r, 1);
        t_equal(2, count(json_decode(file_get_contents($file), true)['groups']), 'rebuilt');
    },
];
