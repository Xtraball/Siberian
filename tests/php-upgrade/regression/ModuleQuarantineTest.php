<?php
/**
 * A module of app/local/modules written for an older PHP can stop the whole application with a fatal error (a method
 * that no longer matches the core one is a compile error since PHP 8), and the editor then shows an error page for
 * every page that touches the module. A fatal error from a module file now puts the module in quarantine: it is
 * disabled like a module switched off by hand, its classes are not autoloaded, and the dashboard offers to recheck
 * or re-enable it. A quarantine never expires by itself, the dashboard flags it when the module, the core or PHP
 * changed since. A scan loads the modules in separate processes before a page breaks.
 */
$root = dirname(__DIR__, 3) . '/siberian';
require_once $root . '/lib/Siberian/Module/Quarantine.php';

use Siberian\Module\Quarantine;

function mq_tmp(): string
{
    $dir = sys_get_temp_dir() . '/mq_' . bin2hex(random_bytes(4));
    mkdir($dir . '/lib/Siberian', 0777, true);
    mkdir($dir . '/app/local/modules', 0777, true);
    file_put_contents($dir . '/lib/Siberian/Version.php', "<?php\nclass Version\n{\n    const VERSION = '5.1.4';\n}\n");

    return $dir;
}

function mq_module(string $dir, string $name, string $version = '1.0', string $minimum = '4.12.5'): void
{
    mkdir($dir . '/app/local/modules/' . $name . '/Model', 0777, true);
    file_put_contents($dir . '/app/local/modules/' . $name . '/package.json', json_encode([
        'name' => $name,
        'version' => $version,
        'dependencies' => ['system' => ['type' => 'SAE', 'version' => $minimum]],
    ]));
}

function mq_rm(string $dir): void
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
 * Runs a PHP script in a separate process, a fatal error ends it.
 */
function mq_run(string $dir, string $body, array $args = []): int
{
    $script = $dir . '/child.php';
    file_put_contents($script, "<?php\nrequire " . var_export(dirname(__DIR__, 3) . '/siberian/lib/Siberian/Module/Quarantine.php', true) . ";\n" . $body);
    $output = [];
    $code = 0;
    exec(escapeshellarg(PHP_BINARY) . ' -d display_errors=0 -d log_errors=0 ' . escapeshellarg($script) . ' ' .
        implode(' ', array_map('escapeshellarg', $args)) . ' 2>&1', $output, $code);

    return $code;
}

$BROKEN = "<?php\nclass Broken_Base\n{\n    public function copyTo(\$option, \$parent_id = null)\n    {\n    }\n}\n\n" .
    "class Broken_Model_Thing extends Broken_Base\n{\n    public function copyTo(\$option)\n    {\n    }\n}\n";

return [
    'a module is found from the file of a fatal error, core files and other places are not modules' => function () {
        t_equal('Loginredirect', Quarantine::moduleFromFile('/var/www/app/local/modules/Loginredirect/Model/Loginredirect.php'));
        t_equal('Layout104', Quarantine::moduleFromFile('C:\\www\\app\\local\\modules\\Layout104\\Model\\Layout.php'));
        t_equal(null, Quarantine::moduleFromFile('/var/www/app/sae/modules/Application/Model/Application.php'), 'core module');
        t_equal(null, Quarantine::moduleFromFile('/var/www/lib/Zend/Db.php'), 'library');
        t_equal(null, Quarantine::moduleFromFile(null), 'no file');
        t_equal(null, Quarantine::moduleFromFile('/var/www/app/local/modules/'), 'no module name');
        t_true(Quarantine::isFatal(E_COMPILE_ERROR), 'compile error is fatal');
        t_true(Quarantine::isFatal(E_ERROR), 'error is fatal');
        t_true(!Quarantine::isFatal(E_WARNING), 'warning is not');
        t_true(!Quarantine::isFatal(E_DEPRECATED), 'deprecation is not');
        t_true(!Quarantine::isFatal(null), 'no error is not');
    },

    'a quarantine is recorded, never expires, and flags the module as stale once it changed' => function () {
        $dir = mq_tmp();
        mq_module($dir, 'Foo', '1.0');
        Quarantine::setRoot($dir);

        t_true(!Quarantine::isQuarantined('Foo'), 'not quarantined at first');
        t_true(Quarantine::add('Foo', ['type' => E_COMPILE_ERROR, 'message' => 'Declaration of Foo::copyTo()', 'file' => 'x.php', 'line' => 3]), 'new');
        t_true(Quarantine::isQuarantined('Foo'), 'quarantined');
        $entry = Quarantine::get('Foo');
        t_equal('fatal', $entry['reason'], 'default reason');
        t_equal(false, $entry['stale'], 'not stale yet');
        t_equal('1.0', $entry['version']);
        t_true(!Quarantine::add('Foo', ['type' => E_ERROR, 'message' => 'again', 'file' => 'x.php', 'line' => 4]), 'second time is not new');

        // The module is updated: still quarantined, but flagged so the dashboard asks to recheck it
        file_put_contents($dir . '/app/local/modules/Foo/package.json', json_encode(['version' => '2.0']));
        Quarantine::setRoot($dir);
        t_true(Quarantine::isQuarantined('Foo'), 'still quarantined after the update');
        t_equal(true, Quarantine::get('Foo')['stale'], 'stale after the update');
        t_equal('2.0', Quarantine::get('Foo')['version']);

        t_true(Quarantine::release('Foo'), 'released');
        t_true(!Quarantine::isQuarantined('Foo'), 'not quarantined any more');
        t_true(!Quarantine::release('Foo'), 'nothing to release twice');
        Quarantine::setRoot(null);
        mq_rm($dir);
    },

    'only fatal errors of a module file are recorded, and the switch turns it all off' => function () {
        global $_config;
        $dir = mq_tmp();
        mq_module($dir, 'Foo');
        Quarantine::setRoot($dir);
        $file = $dir . '/app/local/modules/Foo/Model/Foo.php';

        t_equal(null, Quarantine::handleError(null), 'no error');
        t_equal(null, Quarantine::handleError(['type' => E_WARNING, 'message' => 'w', 'file' => $file, 'line' => 1]), 'warning');
        t_equal(null, Quarantine::handleError(['type' => E_ERROR, 'message' => 'core', 'file' => '/var/www/app/sae/modules/Core/Model/X.php', 'line' => 1]), 'core file');
        t_true(!Quarantine::isQuarantined('Foo'), 'nothing recorded yet');

        $previous = $_config ?? null;
        $_config = ['module_quarantine' => false];
        t_equal(null, Quarantine::handleError(['type' => E_ERROR, 'message' => 'x', 'file' => $file, 'line' => 1]), 'switched off');
        t_true(!Quarantine::isQuarantined('Foo'));
        $_config = $previous;

        $handled = Quarantine::handleError(['type' => E_COMPILE_ERROR, 'message' => 'boom', 'file' => $file, 'line' => 7]);
        t_equal(['Foo', true], $handled, 'recorded');
        t_true(Quarantine::isQuarantined('Foo'));

        $_config = ['module_quarantine' => false];
        t_true(!Quarantine::isQuarantined('Foo'), 'a switched off quarantine never disables a module');
        t_true(!Quarantine::blocksClass('Foo_Model_Foo'), 'nor blocks a class');
        $_config = $previous;

        Quarantine::setRoot(null);
        mq_rm($dir);
    },

    'the autoloader refuses the classes of a quarantined module, never a core module of the same name' => function () {
        $dir = mq_tmp();
        mq_module($dir, 'Foo');
        mq_module($dir, 'Other');
        mkdir($dir . '/app/sae/modules/Shared', 0777, true);
        mq_module($dir, 'Shared');
        Quarantine::setRoot($dir);
        Quarantine::add('Foo', ['type' => E_ERROR, 'message' => 'x', 'file' => '', 'line' => 0]);
        Quarantine::add('Shared', ['type' => E_ERROR, 'message' => 'x', 'file' => '', 'line' => 0]);

        t_true(Quarantine::blocksClass('Foo_Model_Foo'), 'class of the quarantined module');
        t_true(!Quarantine::blocksClass('Other_Model_Other'), 'another module');
        t_true(!Quarantine::blocksClass('Shared_Model_Shared'), 'a core module has the same name');
        t_true(!Quarantine::blocksClass('Foo'), 'no underscore');
        t_true(!Quarantine::blocksClass(null), 'no class');

        Quarantine::setRoot(null);
        mq_rm($dir);
    },

    'the visitor gets the page again once, an error with an explanation otherwise' => function () {
        $get = ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/application/customization_features/list?x=1'];

        [$code, $headers, $body, $cookie] = Quarantine::plan('Foo', $get, [], true);
        t_equal(302, $code, 'replayed');
        t_equal(['Location: /application/customization_features/list?x=1'], $headers);
        t_equal(Quarantine::RETRY_COOKIE, $cookie[0], 'retry cookie');

        [$code] = Quarantine::plan('Foo', $get, [Quarantine::RETRY_COOKIE => 'Foo'], true);
        t_equal(503, $code, 'never replayed twice, no loop');

        [$code] = Quarantine::plan('Foo', $get, [], false);
        t_equal(503, $code, 'already quarantined, the error comes from elsewhere');

        [$code, $headers, $body] = Quarantine::plan('Foo', $get + ['HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest'], [], true);
        t_equal(503, $code, 'ajax');
        $json = json_decode($body, true);
        t_equal('Foo', $json['quarantined']);
        t_equal(true, $json['error']);
        t_equal(['Content-Type: application/json'], $headers);

        [$code] = Quarantine::plan('Foo', ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/save'], [], true);
        t_equal(503, $code, 'a post cannot be replayed');

        [$code] = Quarantine::plan('Foo', ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '//evil.example/path'], [], true);
        t_equal(503, $code, 'never redirects out of the site');
        [$code] = Quarantine::plan('Foo', ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => "/ok\r\nSet-Cookie: x=1"], [], true);
        t_equal(503, $code, 'no header injection');
    },

    'a module that needs a newer core is quarantined by the version check' => function () {
        $dir = mq_tmp();
        mq_module($dir, 'Old', '1.0', '4.12.5');
        mq_module($dir, 'Future', '1.0', '9.0.0');
        Quarantine::setRoot($dir);

        t_equal(null, Quarantine::checkVersion('Old'), 'core is newer than the minimum');
        t_true(strpos((string) Quarantine::checkVersion('Future'), '9.0.0') !== false, 'message names the version');
        t_equal(null, Quarantine::checkVersion('Missing'), 'no package.json');

        [$status, $message] = Quarantine::recheck('Future');
        t_equal('quarantined', $status);
        t_equal('version', Quarantine::get('Future')['reason']);

        // The module is gone: the record is cleaned
        mq_rm($dir . '/app/local/modules/Future');
        [$status] = Quarantine::recheck('Future');
        t_equal('released', $status, 'removed module');
        t_true(!Quarantine::isQuarantined('Future'));

        Quarantine::setRoot(null);
        mq_rm($dir);
    },

    'the dashboard summary counts the modules and says when the scan is outdated' => function () {
        $dir = mq_tmp();
        mq_module($dir, 'A');
        mq_module($dir, 'B');
        mq_module($dir, 'C');
        Quarantine::setRoot($dir);
        Quarantine::add('B', ['type' => E_ERROR, 'message' => 'x', 'file' => '', 'line' => 0]);

        $summary = Quarantine::summary();
        t_equal(3, $summary['total']);
        t_equal(1, $summary['quarantined']);
        t_equal('B', $summary['items'][0]['name']);
        t_equal(false, $summary['scan_outdated'], 'never scanned is not a warning');

        is_dir($dir . '/var/quarantine') || mkdir($dir . '/var/quarantine', 0777, true);
        file_put_contents(Quarantine::scanPath(), json_encode(['status' => 'done', 'environment' => 'php5.6|core1.0']));
        t_equal(true, Quarantine::summary()['scan_outdated'], 'PHP or core changed since the last scan');
        file_put_contents(Quarantine::scanPath(), json_encode(['status' => 'done', 'environment' => Quarantine::environment()]));
        t_equal(false, Quarantine::summary()['scan_outdated'], 'same environment');

        Quarantine::setRoot(null);
        mq_rm($dir);
    },

    'a real compile error of a module file puts it in quarantine, the process dies but the record is written' => function () use ($BROKEN) {
        $dir = mq_tmp();
        mq_module($dir, 'Broken');
        file_put_contents($dir . '/app/local/modules/Broken/Model/Thing.php', $BROKEN);

        $code = mq_run($dir, "Siberian\\Module\\Quarantine::setRoot(\$argv[1]);\nSiberian\\Module\\Quarantine::register();\nrequire \$argv[1] . '/app/local/modules/Broken/Model/Thing.php';\n", [$dir]);
        t_true($code !== 0, 'the child died of the fatal error');

        Quarantine::setRoot($dir);
        $entry = Quarantine::get('Broken');
        t_true($entry !== null, 'recorded by the shutdown handler');
        t_equal('fatal', $entry['reason']);
        t_true(strpos($entry['message'], 'must be compatible') !== false, 'the PHP message is kept: ' . $entry['message']);
        t_equal(3, count(array_filter([$entry['file'], $entry['line'], $entry['at']])), 'file, line and date');

        Quarantine::setRoot(null);
        mq_rm($dir);
    },

    'a probe loads the classes of a module in its own process and quarantines it when that dies' => function () use ($BROKEN) {
        $dir = mq_tmp();
        mq_module($dir, 'Broken');
        mq_module($dir, 'Fine');
        file_put_contents($dir . '/app/local/modules/Broken/Model/Thing.php', $BROKEN);
        file_put_contents($dir . '/app/local/modules/Fine/Model/Fine.php', "<?php\nclass Fine_Model_Fine\n{\n}\n");
        // An install script must never be loaded by a probe
        file_put_contents($dir . '/app/local/modules/Fine/install.php', "<?php\nexit(9);\n");

        $body = "Siberian\\Module\\Quarantine::setRoot(\$argv[1]);\nexit(Siberian\\Module\\Quarantine::probe(\$argv[2]));\n";

        t_equal(0, mq_run($dir, $body, [$dir, 'Fine']), 'a healthy module loads');
        Quarantine::setRoot($dir);
        t_true(!Quarantine::isQuarantined('Fine'), 'nothing recorded for it');
        t_equal(1, count(Quarantine::probeFiles('Fine')), 'only the class folders, not install.php');

        t_true(mq_run($dir, $body, [$dir, 'Broken']) !== 0, 'a broken module kills the probe');
        Quarantine::setRoot($dir);
        t_equal('probe', Quarantine::get('Broken')['reason'], 'recorded as a failed check');

        Quarantine::setRoot(null);
        mq_rm($dir);
    },

    'a probe ignores a second file that declares a class which is already there, a leftover copy is not a reason' => function () {
        $dir = mq_tmp();
        mq_module($dir, 'Dup');
        file_put_contents($dir . '/app/local/modules/Dup/Model/A.php', "<?php\nclass Dup_Model_Thing\n{\n    public function name()\n    {\n        return Foo::class;\n    }\n}\n");
        file_put_contents($dir . '/app/local/modules/Dup/Model/B.php', "<?php\nclass Dup_Model_Thing\n{\n}\n");
        file_put_contents($dir . '/app/local/modules/Dup/Model/C.php', "<?php\nnamespace Dup\\Model;\n\nclass Other\n{\n}\n");

        $body = "Siberian\\Module\\Quarantine::setRoot(\$argv[1]);\nexit(Siberian\\Module\\Quarantine::probe(\$argv[2]));\n";
        t_equal(0, mq_run($dir, $body, [$dir, 'Dup']), 'the duplicate does not kill the probe');
        Quarantine::setRoot($dir);
        t_true(!Quarantine::isQuarantined('Dup'), 'not quarantined');

        // The helper itself: a class that does not exist yet is not "loaded", Foo::class is not a declaration
        t_true(!Quarantine::declaresLoadedType($dir . '/app/local/modules/Dup/Model/A.php'), 'nothing declared yet');
        t_true(Quarantine::declaresLoadedType(__FILE__) === false, 'this file declares no loaded type');
        eval('class Dup_Model_Thing {}');
        t_true(Quarantine::declaresLoadedType($dir . '/app/local/modules/Dup/Model/B.php'), 'class already declared');
        t_true(!Quarantine::declaresLoadedType($dir . '/app/local/modules/Dup/Model/C.php'), 'namespaced class is another one');

        Quarantine::setRoot(null);
        mq_rm($dir);
    },

    'a probe finds the base classes of its module and of other local modules even though a quarantined module is disabled' => function () {
        $dir = mq_tmp();
        mq_module($dir, 'Own');
        mq_module($dir, 'Base');
        $modules = $dir . '/app/local/modules';
        $w = function ($file, $code) { if (!is_dir(dirname($file))) { mkdir(dirname($file), 0777, true); } file_put_contents($file, $code); };
        $w($modules . '/Own/Controller/Default.php', "<?php\nclass Own_Controller_Default {}\n");
        $w($modules . '/Own/controllers/Public/OrderController.php', "<?php\nclass Own_Public_OrderController extends Own_Controller_Default {}\n");
        $w($modules . '/Own/Model/Thing.php', "<?php\nclass Own_Model_Thing extends Base_Model_Root {}\n");
        $w($modules . '/Base/Model/Root.php', "<?php\nclass Base_Model_Root {}\n");

        $body = "Siberian\\Module\\Quarantine::setRoot(\$argv[1]);\nexit(Siberian\\Module\\Quarantine::probe(\$argv[2]));\n";
        t_equal(0, mq_run($dir, $body, [$dir, 'Own']), 'a parent class in Controller/ or in another local module is found');
        Quarantine::setRoot($dir);
        t_true(!Quarantine::isQuarantined('Own'), 'not quarantined');

        Quarantine::setRoot(null);
        mq_rm($dir);
    },

    'the quarantine is wired in the shutdown handler, the autoloader, the module switch, the cli and the dashboard' => function () {
        $root = dirname(__DIR__, 3) . '/siberian';
        $get = function ($path) use ($root) {
            return file_get_contents($root . '/' . $path);
        };

        t_true(strpos($get('lib/System/shutdown.php'), 'Quarantine::register()') !== false, 'shutdown handler');
        t_true(strpos($get('app/Bootstrap.php'), 'Quarantine::blocksClass') !== false, 'autoloader');
        t_true(strpos($get('app/Bootstrap.php'), 'setDefaultAutoloader') !== false, 'default autoloader');
        $module = $get('app/sae/modules/Installer/Model/Installer/Module.php');
        t_true(strpos($module, 'Quarantine::isQuarantined($module)') !== false, 'a quarantined module is disabled');
        t_true(strpos($module, 'Quarantine::release(') !== false, 'enabling releases');
        t_true(strpos($get('cli'), "case 'module:quarantine'") !== false, 'cli command');
        $controller = $get('app/sae/modules/Backoffice/controllers/Advanced/ModuleController.php');
        foreach (['quarantineAction', 'quarantinerecheckAction', 'quarantinereleaseAction', 'quarantinescanAction'] as $action) {
            t_true(strpos($controller, $action) !== false, $action);
        }
        $dashboard = $get('app/sae/design/desktop/backoffice/template/backoffice/index/index.phtml');
        t_true(strpos($dashboard, 'quarantine.quarantined > 0') !== false, 'dashboard alert');
        t_true(strpos($dashboard, 'quarantineRecheck') !== false && strpos($dashboard, 'quarantineRelease') !== false, 'recheck and re-enable buttons');
        t_true(strpos($get('app/sae/design/desktop/backoffice/js/controllers/homepage.js'), 'loadQuarantine') !== false, 'dashboard controller');

        // Every new text is in the catalog
        $po = $get('languages/base/c_backoffice.po');
        foreach (['Modules in quarantine', 'Recheck all', 'Re-enable', 'Scan all modules', 'Quarantine'] as $text) {
            t_true(strpos($po, 'msgid "' . $text . '"') !== false, 'catalog: ' . $text);
        }
    },
];
