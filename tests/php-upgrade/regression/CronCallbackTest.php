<?php
/**
 * A cron job registered by a module is "Class::method". PHP 8 no longer calls a non static method that way
 * (call_user_func throws a TypeError, which nothing caught, so every job scheduled after it was lost for that run).
 * The scheduler now checks the job before calling it, runs a non static method on an instance, records what cannot
 * run on the job, and a job that throws never stops the following ones. The same check feeds the cron page and the
 * dashboard.
 */
$root = dirname(__DIR__, 3) . '/siberian';
require_once $root . '/lib/Siberian/Cron/Callback.php';

class CronFixtureJobs
{
    public static $calls = [];

    public static function staticJob($runner, $task)
    {
        self::$calls[] = ['static', $runner, $task];

        return 'static';
    }

    public function instanceJob($runner, $task)
    {
        self::$calls[] = ['instance', $runner, $task];

        return 'instance';
    }

    private static function hidden($runner, $task)
    {
    }

    public static function greedy($a, $b, $c)
    {
    }
}

class CronFixtureWithConstructor
{
    public function __construct($required)
    {
    }

    public function run($runner, $task)
    {
    }
}

abstract class CronFixtureAbstract
{
    public function run($runner, $task)
    {
    }
}

class CronFixtureRunner
{
    public $seen = [];

    public function local($task)
    {
        $this->seen[] = $task;

        return 'local';
    }

    protected function guarded($task)
    {
    }

    public function twoArguments($a, $b)
    {
    }
}

return [
    'a job that can run is ok, a non static one is a warning' => function () {
        $runner = CronFixtureRunner::class;
        t_equal('ok', \Siberian\Cron\Callback::inspect('CronFixtureJobs::staticJob', $runner)['status'], 'static');
        t_equal('ok', \Siberian\Cron\Callback::inspect('local', $runner)['status'], 'method of the scheduler');

        $instance = \Siberian\Cron\Callback::inspect('CronFixtureJobs::instanceJob', $runner);
        t_equal('warning', $instance['status'], 'non static');
        t_equal('non_static', $instance['code'], 'non static code');
        t_true(strpos($instance['message'], 'CronFixtureJobs::instanceJob') !== false, 'the message names the job');
    },

    'what can not run is an error with its reason' => function () {
        $runner = CronFixtureRunner::class;
        $cases = [
            ['', 'empty_command'],
            ['   ', 'empty_command'],
            ['CronFixtureMissing::job', 'missing_class'],
            ['CronFixtureJobs::missing', 'missing_method'],
            ['CronFixtureJobs::', 'missing_method'],
            ['CronFixtureJobs::hidden', 'not_public'],
            ['CronFixtureJobs::greedy', 'too_many_arguments'],
            ['CronFixtureWithConstructor::run', 'needs_constructor_args'],
            ['CronFixtureAbstract::run', 'not_instantiable'],
            ['nothingHere', 'missing_method'],
            ['guarded', 'not_public'],
            ['twoArguments', 'too_many_arguments'],
        ];
        foreach ($cases as list($command, $code)) {
            $result = \Siberian\Cron\Callback::inspect($command, $runner);
            t_equal('error', $result['status'], $command . ' status');
            t_equal($code, $result['code'], $command . ' code');
            t_true($result['message'] !== '', $command . ' has a message');
        }
    },

    'a class that can not be loaded is reported, not thrown' => function () {
        spl_autoload_register($loader = function ($class) {
            if ($class === 'CronFixtureBroken') {
                throw new ParseError('syntax error, unexpected token');
            }
        });
        try {
            $result = \Siberian\Cron\Callback::inspect('CronFixtureBroken::job', CronFixtureRunner::class);
        } finally {
            spl_autoload_unregister($loader);
        }

        t_equal('error', $result['status'], 'status');
        t_equal('class_error', $result['code'], 'code');
        t_true(strpos($result['message'], 'syntax error') !== false, 'the cause is kept');
    },

    'the failure this fixes: PHP 8 refuses the static call of a non static method' => function () {
        if (PHP_VERSION_ID < 80000) {
            return;
        }
        $thrown = null;
        try {
            call_user_func('CronFixtureJobs::instanceJob', 'runner', 'task');
        } catch (Throwable $e) {
            $thrown = $e;
        }
        t_true($thrown instanceof TypeError, 'call_user_func throws a TypeError, as in the reported cron log');
        t_true(!($thrown instanceof Exception), 'it is an Error, an "Exception" catch never sees it');
    },

    'prepare runs a static job, and a non static job on an instance, with the scheduler and the task' => function () {
        $runner = new CronFixtureRunner();
        $task = new stdClass();
        CronFixtureJobs::$calls = [];

        $static = \Siberian\Cron\Callback::prepare('CronFixtureJobs::staticJob', $runner, $task);
        t_equal('static', $static['call'](), 'static result');

        $instance = \Siberian\Cron\Callback::prepare('CronFixtureJobs::instanceJob', $runner, $task);
        t_equal('warning', $instance['status'], 'reported as a warning');
        t_equal('instance', $instance['call'](), 'non static result');

        $local = \Siberian\Cron\Callback::prepare('local', $runner, $task);
        t_equal('local', $local['call'](), 'method of the scheduler');

        t_equal(2, count(CronFixtureJobs::$calls), 'both module jobs ran');
        foreach (CronFixtureJobs::$calls as $call) {
            t_true($call[1] === $runner && $call[2] === $task, $call[0] . ' got the scheduler and the task');
        }
        t_equal([$task], $runner->seen, 'the method of the scheduler got the task');
    },

    'prepare gives nothing to call for a job that can not run' => function () {
        $result = \Siberian\Cron\Callback::prepare('CronFixtureMissing::job', new CronFixtureRunner(), null);
        t_equal('error', $result['status'], 'status');
        t_true($result['call'] === null, 'nothing to call');
        t_true($result['message'] !== '', 'the reason is there for the task error');
    },

    'the report counts the active jobs and lists what needs attention' => function () {
        $report = \Siberian\Cron\Callback::report([
            ['command' => 'local', 'name' => 'Local', 'is_active' => 1],
            ['command' => 'CronFixtureJobs::instanceJob', 'name' => 'Instance', 'is_active' => '1'],
            ['command' => 'CronFixtureMissing::job', 'name' => 'Missing', 'is_active' => 1],
            ['command' => 'CronFixtureJobs::hidden', 'name' => 'Disabled', 'is_active' => 0],
        ], '8.2.1', CronFixtureRunner::class);

        t_equal('8.2.1', $report['php_version'], 'version');
        t_equal(3, $report['checked'], 'a disabled job is not checked');
        t_equal(1, $report['ok'], 'ok');
        t_equal(1, $report['warnings'], 'warnings');
        t_equal(1, $report['errors'], 'errors');
        t_equal(2, $report['problems'], 'problems');
        t_equal(['Instance', 'Missing'], array_column($report['items'], 'name'), 'items');
        t_equal('error', $report['results']['CronFixtureJobs::hidden']['status'], 'a disabled job is still described');
    },

    'the scheduler isolates jobs and catches what a job throws' => function () use ($root) {
        $source = file_get_contents($root . '/lib/Siberian/Cron.php');

        // In namespace Siberian a bare "Exception" is Siberian\Exception: it let every other exception through
        t_equal(0, preg_match('/catch \(Exception\b/', $source), 'no catch of the namespaced Exception');
        t_equal(0, preg_match('/call_user_func\(\$command/', $source), 'no direct call of the stored command');
        t_true(strpos($source, 'Cron\\Callback::prepare($command, $this, $task)') !== false, 'jobs go through prepare');

        $execute = substr($source, strpos($source, 'public function execute('), 2400);
        t_true(strpos($execute, 'catch (\\Throwable $e)') !== false, 'execute catches Throwable');
        t_true(strpos($execute, 'saveLastError') !== false, 'the error is recorded on the job');

        $triggerAll = substr($source, strpos($source, 'public function triggerAll('), 1600);
        t_equal(1, preg_match('/try \{\s+\$this->execute\(\$task\);\s+\} catch \(\\\\Throwable/', $triggerAll),
            'each job runs in its own try, the loop goes on');
    },

    'the cron page and the dashboard show the report' => function () use ($root) {
        $design = $root . '/app/sae/design/desktop/backoffice';

        $controller = file_get_contents($root . '/app/sae/modules/Backoffice/controllers/Advanced/CronController.php');
        t_true(strpos($controller, '\\Siberian\\Cron\\Callback::report(') !== false, 'the page uses the report');
        t_true(strpos($controller, '$data["compat"] = $compat;') !== false, 'sent to the page');

        $page = file_get_contents($design . '/template/backoffice/advanced/cron.phtml');
        t_true(strpos($page, 'compat.items') !== false, 'the page lists the problems');
        t_true(strpos($page, 'system_task.compat') !== false && strpos($page, 'task.compat') !== false, 'per job');

        $js = file_get_contents($design . '/js/controllers/advanced.js');
        t_true(strpos($js, '$scope.compat = data.compat;') !== false, 'the controller keeps it');

        $service = file_get_contents($root . '/lib/Siberian/Service.php');
        t_true(strpos($service, '"cron_compat" => self::getCronCompatibility()') !== false, 'the dashboard gets the summary');
        t_true(strpos($service, '"cron_compat" => null') !== false, 'and a fallback that never breaks it');

        $dashboard = file_get_contents($design . '/template/backoffice/index/index.phtml');
        t_true(strpos($dashboard, 'services.cron_compat.problems > 0') !== false, 'the service panel shows it');
    },
];
