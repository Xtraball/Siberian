<?php
/**
 * Dashboard card "Errors on app calls": the errors that modules raise on the calls of the mobile and web apps, by
 * module, for the last 24 hours, from the local history of Siberian\ErrorReporter. A module with 5 fatal errors within
 * about 10 minutes gets a warning, and "Quarantine now" is a button, never automatic.
 */
$siberian = dirname(__DIR__, 3) . '/siberian';
$root = $siberian . '/app/sae/design/desktop/backoffice/';
$template = file_get_contents($root . 'template/backoffice/index/index.phtml');
$js = file_get_contents($root . 'js/controllers/homepage.js');
$factory = file_get_contents($root . 'js/factories/advanced.js');
$controller = file_get_contents($siberian . '/app/sae/modules/Backoffice/controllers/Advanced/ModuleController.php');
$glue = file_get_contents($siberian . '/app/sae/modules/Core/Model/ErrorReports.php');
require_once $siberian . '/lib/Siberian/Module/Quarantine.php';

return [
    'the card is in the dashboard, only with something to show, and says when the reports are off' => function () use ($template) {
        t_true(strpos($template, 'id="app-errors"') !== false, 'the card is there');
        t_true(strpos($template, 'appErrors.enabled && appErrors.modules.length > 0') !== false, 'only when reporting is on and there is a module');
        t_true(strpos($template, 'appErrors.enabled === false') !== false, 'a line says when the reports are off');
        t_true(strpos($template, 'ng-repeat="item in appErrors.modules"') !== false, 'one row per module');
        foreach (['item.fatals', 'item.errors', 'item.endpoints', 'item.last', 'item.warning'] as $field) {
            t_true(strpos($template, $field) !== false, "the row shows $field");
        }
    },

    'Quarantine now is a button for a module that is not quarantined yet, with a confirmation' => function () use ($template) {
        t_true(preg_match('/ng-if="!item\.quarantined"[^>]*\s+ng-disabled="quarantineBusy"\s+ng-click="quarantineNow\(item\.name, /s', $template) === 1, 'the button');
        t_true(strpos($template, '<span ng-if="item.quarantined">') !== false, 'a quarantined module says so instead');
        // The confirmation is text inside a quoted JS string: only the escaping helper (p__js) may print it there.
        t_true(preg_match("/quarantineNow\\(item\\.name, '<\\?php echo p__js\\('backoffice', '[^']+', \"'\"\\); \\?>'\\)/", $template) === 1, 'p__js with the quote to escape');
        t_equal(0, preg_match("/quarantineNow\\(item\\.name, '<\\?php echo p__\\(/", $template), 'never the plain p__');
        t_true(strpos($template, '\\Siberian\\ErrorReporter::WARN_FATALS') !== false, 'the threshold printed is the real one');
        t_true(strpos($template, 'ng-switch-when="manual"') !== false, 'the quarantine card knows the manual reason');
    },

    'the dashboard controller loads the card and quarantines only when asked' => function () use ($js, $factory) {
        t_true(strpos($js, '$scope.appErrors = {enabled: true, modules: []};') !== false, 'state');
        t_true(strpos($js, "\$scope.loadAppErrors();\n    \$scope.loadMessages();") !== false, 'loaded with the page');
        t_true(preg_match('/Advanced\s*\.quarantineNow\(name\)/', $js) === 1, 'the button calls the factory');
        t_equal(1, preg_match_all('/\.quarantineNow\(/', $js), 'the factory is only called from the one scope function');
        t_true(strpos($js, 'item.warning && !item.quarantined') !== false, 'the red state is for a module that is not quarantined');
        t_true(strpos($factory, "backoffice/advanced_module/apperrors") !== false, 'factory: read');
        t_true(strpos($factory, "backoffice/advanced_module/quarantinenow") !== false, 'factory: quarantine');
    },

    'the controller serves the card and quarantines a known local module by hand, nothing is automatic' => function () use ($controller, $glue) {
        t_true(strpos($controller, 'public function apperrorsAction()') !== false, 'read action');
        t_true(strpos($controller, 'public function quarantinenowAction()') !== false, 'quarantine action');
        t_true(strpos($controller, "in_array(\$module, \\Siberian\\Module\\Quarantine::localModules(), true)") !== false, 'only a module of app/local/modules');
        t_true(strpos($controller, '\\Siberian\\Module\\Quarantine::REASON_MANUAL') !== false, 'the reason is manual');
        t_equal(1, substr_count($controller, 'Quarantine::REASON_MANUAL'), 'the only place that quarantines by hand');
        t_true(strpos($glue, 'public static function appErrors()') !== false, 'glue');
        t_true(strpos($glue, 'self::reporter()->forgetLocal();') !== false, 'turning the reports off forgets the history');
        // the shutdown handler of the errors never quarantines
        $reporter = file_get_contents(dirname(__DIR__, 3) . '/siberian/lib/Siberian/ErrorReporter.php');
        t_equal(0, substr_count($reporter, 'Quarantine::add'), 'the reporter never quarantines');
    },

    'the manual reason is a quarantine reason like the others' => function () {
        t_equal('manual', \Siberian\Module\Quarantine::REASON_MANUAL);
        $dir = sys_get_temp_dir() . '/aec_' . bin2hex(random_bytes(4));
        mkdir($dir . '/lib/Siberian', 0777, true);
        mkdir($dir . '/app/local/modules/Foo', 0777, true);
        file_put_contents($dir . '/lib/Siberian/Version.php', "<?php\nclass Version\n{\n    const VERSION = '5.1.9';\n}\n");
        file_put_contents($dir . '/app/local/modules/Foo/package.json', '{"name":"Foo","version":"1.0"}');
        \Siberian\Module\Quarantine::setRoot($dir);
        \Siberian\Module\Quarantine::add('Foo', ['type' => 0, 'message' => 'by hand', 'file' => '', 'line' => 0], \Siberian\Module\Quarantine::REASON_MANUAL);
        $entry = \Siberian\Module\Quarantine::get('Foo');
        t_equal('manual', $entry['reason']);
        t_true(\Siberian\Module\Quarantine::isQuarantined('Foo'), 'quarantined');
        t_true(\Siberian\Module\Quarantine::release('Foo'), 'and can be enabled again');
        \Siberian\Module\Quarantine::setRoot(null);
        system('rm -rf ' . escapeshellarg($dir));
    },

    'the new texts are in the catalog' => function () {
        $catalog = file_get_contents(dirname(__DIR__, 3) . '/siberian/languages/base/c_backoffice.po');
        foreach (['Errors on app calls', 'last 24 hours', 'Quarantine now', 'In quarantine', 'Put in quarantine by an administrator',
            'The module is in quarantine.', 'Fatal errors', 'Endpoints', 'Last seen', 'Warning: %s fatal errors or more within about 10 minutes.'] as $text) {
            t_true(strpos($catalog, "msgctxt \"backoffice\"\nmsgid \"$text\"\n") !== false, "c_backoffice.po has: $text");
        }
    },
];
