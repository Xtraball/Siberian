<?php
/**
 * Tier 1: Siberian\Cron, the scheduler, against a real cron table: which jobs a minute selects, what is recorded on a
 * job that succeeds, fails or is locked, that one broken job never stops the following ones, the lock files, the log
 * clean-up and the generator queue lock.
 */
$siberian = dirname(__DIR__, 3) . '/siberian';
$cronSchema = $siberian . '/app/sae/modules/Cron/resources/db/schema/cron.php';

require_once $siberian . '/lib/Siberian/File.php';
require_once $siberian . '/lib/Siberian/Cron/Callback.php';
require_once $siberian . '/lib/Siberian/Cron.php';

/** The jobs the scheduler calls: "Class::method", each one records its call. */
class CronSchedFixture
{
    public static $calls = [];

    public static function ok($runner, $task)
    {
        self::$calls[] = 'ok:' . $task->getName();
    }

    public static function second($runner, $task)
    {
        self::$calls[] = 'second:' . $task->getName();
    }

    public static function fails($runner, $task)
    {
        self::$calls[] = 'fails:' . $task->getName();
        throw new RuntimeException('job went wrong');
    }

    public static function error($runner, $task)
    {
        self::$calls[] = 'error:' . $task->getName();
        throw new TypeError('php 8 only');
    }
}

/** The queue of source generations, as Application_Model_SourceQueue::getQueue() hands it over. */
class CronSchedSource
{
    public static $queue = [];
    public static $statuses = [];

    public function getId()
    {
        return 7;
    }

    public function getAppId()
    {
        return 1;
    }

    public function getName()
    {
        return 'App';
    }

    public function getType()
    {
        return 'ios';
    }

    public function changeStatus($status)
    {
        self::$statuses[] = $status;
    }

    public function generate($cron)
    {
        throw new Error('generator crashed');
    }
}

if (!class_exists('Application_Model_SourceQueue', false)) {
    class Application_Model_SourceQueue
    {
        public static function getQueue()
        {
            return CronSchedSource::$queue;
        }

        public function find($id)
        {
            return $this;
        }

        public function getId()
        {
            return 7;
        }

        public function changeStatus($status)
        {
            CronSchedSource::$statuses[] = $status;
        }
    }
}

/** A fresh database with the cron table and a base path of its own (locks and logs live under it). */
$fresh = static function (array $config = []) use ($cronSchema): array {
    $db = t_db_fresh();
    Zend_Registry::set('db', $db);
    t_db_sync('cron', $cronSchema);
    $db->query('SET FOREIGN_KEY_CHECKS = 0');

    $base = sys_get_temp_dir() . '/sib-cron-' . getmypid() . '-' . uniqid();
    mkdir($base . '/var/tmp', 0777, true);
    mkdir($base . '/var/log/modules', 0777, true);
    Core_Model_Directory::setBasePath($base);

    // The settings the scheduler reads: cached by the config model, so they are put straight in its cache
    $values = new ReflectionProperty(System_Model_Config::class, '_values');
    $values->setValue(null, $config + ['disable_cron' => '0', 'system_timezone' => 'UTC']);
    CronSchedFixture::$calls = [];

    return [$db, $base];
};

/** Adds a job to the cron table and returns its id. */
$job = static function (Zend_Db_Adapter_Abstract $db, string $command, array $row = []): int {
    static $n = 0;
    ++$n;
    $db->insert('cron', $row + [
        'name' => 'job' . $n,
        'command' => $command,
        'last_error' => '',
        'last_error_date' => '0000-00-00 00:00:00',
        'last_trigger' => '0000-00-00 00:00:00',
        'last_success' => '0000-00-00 00:00:00',
        'last_fail' => '0000-00-00 00:00:00',
    ]);

    return (int) $db->lastInsertId();
};

$row = static function (Zend_Db_Adapter_Abstract $db, int $id): array {
    return $db->fetchRow('SELECT * FROM cron WHERE cron_id = ?', [$id]);
};

/** Runs a closure with the scheduler's output kept out of the test log. */
$quiet = static function (callable $run) {
    ob_start();
    try {
        return $run();
    } finally {
        ob_end_clean();
    }
};

$scheduler = static function () {
    return new Siberian\Cron();
};

return [
    'a disabled cron runs nothing' => function () use ($fresh, $job, $quiet, $scheduler) {
        [$db] = $fresh(['disable_cron' => '1']);
        $job($db, 'CronSchedFixture::ok');

        $result = $quiet(function () use ($scheduler) {
            return $scheduler()->triggerAll();
        });

        t_true($result instanceof Siberian\Cron, 'it answers with itself');
        t_equal([], CronSchedFixture::$calls);
    },
    'a minute selects the jobs whose fields match or are -1, and only the active ones' => function () use ($fresh, $job, $quiet, $scheduler) {
        [$db] = $fresh();
        $job($db, 'CronSchedFixture::ok', ['name' => 'every minute']);
        $job($db, 'CronSchedFixture::ok', ['name' => 'this minute', 'minute' => (int) date('i'), 'hour' => (int) date('G')]);
        $job($db, 'CronSchedFixture::ok', ['name' => 'another minute', 'minute' => (int) date('i') + 1 > 59 ? 0 : (int) date('i') + 1]);
        $job($db, 'CronSchedFixture::ok', ['name' => 'switched off', 'is_active' => 0]);

        $actions = $quiet(function () use ($scheduler) {
            return $scheduler()->triggerAll();
        });

        sort(CronSchedFixture::$calls);
        t_equal(['ok:every minute', 'ok:this minute'], CronSchedFixture::$calls);
        t_equal(2, count($actions));
    },
    'jobs run by standalone then priority' => function () use ($fresh, $job, $quiet, $scheduler) {
        [$db] = $fresh();
        $job($db, 'CronSchedFixture::ok', ['name' => 'low', 'priority' => 1]);
        $job($db, 'CronSchedFixture::ok', ['name' => 'high', 'priority' => 9]);
        $job($db, 'CronSchedFixture::ok', ['name' => 'alone', 'priority' => 9, 'standalone' => 1]);

        $quiet(function () use ($scheduler) {
            $scheduler()->triggerAll();
        });

        t_equal(['ok:high', 'ok:low', 'ok:alone'], CronSchedFixture::$calls);
    },
    'a job that works records its trigger and success' => function () use ($fresh, $job, $row, $quiet, $scheduler) {
        [$db] = $fresh();
        $id = $job($db, 'CronSchedFixture::ok');

        $quiet(function () use ($scheduler) {
            $scheduler()->triggerAll();
        });

        $saved = $row($db, $id);
        t_true(strpos($saved['last_trigger'], date('Y-m-d')) === 0, 'last_trigger');
        t_true(strpos($saved['last_success'], date('Y-m-d')) === 0, 'last_success');
        t_equal('0000-00-00 00:00:00', $saved['last_fail']);
        t_equal('', $saved['last_error']);
    },
    'a job that throws is recorded as failed and the next one still runs' => function () use ($fresh, $job, $row, $quiet, $scheduler) {
        [$db] = $fresh();
        $bad = $job($db, 'CronSchedFixture::fails', ['name' => 'bad', 'priority' => 9]);
        $good = $job($db, 'CronSchedFixture::second', ['name' => 'good', 'priority' => 1]);

        $quiet(function () use ($scheduler) {
            $scheduler()->triggerAll();
        });

        t_equal(['fails:bad', 'second:good'], CronSchedFixture::$calls);
        $failed = $row($db, $bad);
        t_equal('job went wrong', $failed['last_error']);
        t_true(strpos($failed['last_fail'], date('Y-m-d')) === 0, 'last_fail');
        t_equal('0000-00-00 00:00:00', $failed['last_success']);
        t_true(strpos($row($db, $good)['last_success'], date('Y-m-d')) === 0, 'the next job succeeded');
    },
    'a PHP Error in a job is recorded like an exception' => function () use ($fresh, $job, $row, $quiet, $scheduler) {
        [$db] = $fresh();
        $bad = $job($db, 'CronSchedFixture::error', ['name' => 'typed', 'priority' => 9]);
        $job($db, 'CronSchedFixture::second', ['name' => 'after', 'priority' => 1]);

        $quiet(function () use ($scheduler) {
            $scheduler()->triggerAll();
        });

        t_equal(['error:typed', 'second:after'], CronSchedFixture::$calls);
        t_equal('php 8 only', $row($db, $bad)['last_error']);
    },
    'a job that cannot be called says why on the job' => function () use ($fresh, $job, $row, $quiet, $scheduler) {
        [$db] = $fresh();
        $id = $job($db, 'NoSuchClass::run');

        $quiet(function () use ($scheduler) {
            $scheduler()->triggerAll();
        });

        $saved = $row($db, $id);
        t_true(strpos($saved['last_error'], 'NoSuchClass::run') === 0, $saved['last_error']);
        t_true(strpos($saved['last_fail'], date('Y-m-d')) === 0, 'last_fail');
    },
    'lock, isLocked and unlock work on a file under var/tmp' => function () use ($fresh, $scheduler) {
        [, $base] = $fresh();
        $cron = $scheduler();
        t_true(!$cron->isLocked('abc'));
        $cron->lock('abc');
        t_true($cron->isLocked('abc'));
        t_true(is_file($base . '/var/tmp/abc.lock'), 'the lock is a file');
        $cron->unlock('abc');
        t_true(!$cron->isLocked('abc'), 'unlock removes the file');
        $cron->unlock('abc');
    },
    'a locked job is skipped' => function () use ($fresh, $job, $quiet, $scheduler) {
        [$db, $base] = $fresh();
        $id = $job($db, 'CronSchedFixture::ok');
        file_put_contents($base . '/var/tmp/' . $id . '.lock', '1');

        $quiet(function () use ($scheduler) {
            $scheduler()->triggerAll();
        });

        t_equal([], CronSchedFixture::$calls);
    },
    'runTaskByCommand runs that command whatever the minute' => function () use ($fresh, $job, $quiet, $scheduler) {
        [$db] = $fresh();
        $job($db, 'CronSchedFixture::ok', ['name' => 'never', 'minute' => 99]);

        $quiet(function () use ($scheduler) {
            $scheduler()->runTaskByCommand('CronSchedFixture::ok');
        });

        t_equal(['ok:never'], CronSchedFixture::$calls);
    },
    'runTaskByCommand on an unknown command does not throw' => function () use ($fresh, $quiet, $scheduler) {
        $fresh();

        $quiet(function () use ($scheduler) {
            $scheduler()->runTaskByCommand('CronSchedFixture::ok');
        });

        t_equal([], CronSchedFixture::$calls);
    },
    'logrotate removes the logs and leaves the others' => function () use ($fresh, $job, $quiet, $scheduler) {
        [$db, $base] = $fresh();
        $log = $base . '/var/log';
        foreach (['info_2024.log', 'migration_1.log', 'error_2024.log', 'output.log', 'cron-output.log', 'cron.log', 'keep.txt'] as $name) {
            file_put_contents("$log/$name", 'x');
        }
        file_put_contents("$log/modules/a.log", 'x');
        file_put_contents("$log/modules/b.log", 'x');
        $id = $job($db, 'logrotate');

        $quiet(function () use ($scheduler, $db, $id) {
            $cron = $scheduler();
            $cron->execute((new Cron_Model_Cron())->find($id));
        });

        t_equal(['keep.txt', 'modules'], array_values(array_diff(scandir($log), ['.', '..'])));
        t_equal([], array_values(array_diff(scandir("$log/modules"), ['.', '..'])));
    },
    'logrotate works when the module log folder holds a folder' => function () use ($fresh, $job, $row, $quiet, $scheduler) {
        [$db, $base] = $fresh();
        mkdir($base . '/var/log/modules/sub');
        file_put_contents($base . '/var/log/modules/a.log', 'x');
        $id = $job($db, 'logrotate');

        $quiet(function () use ($scheduler, $id) {
            $scheduler()->execute((new Cron_Model_Cron())->find($id));
        });

        t_true(!is_file($base . '/var/log/modules/a.log'), 'the log is gone');
        t_equal('', $row($db, $id)['last_error'], 'no error recorded for the folder');
    },
    'a generator that crashes with an Error still releases the generator lock' => function () use ($fresh, $job, $quiet, $scheduler) {
        [$db, $base] = $fresh();
        CronSchedSource::$queue = [new CronSchedSource()];
        CronSchedSource::$statuses = [];
        $id = $job($db, 'sources');

        $quiet(function () use ($scheduler, $id) {
            $scheduler()->execute((new Cron_Model_Cron())->find($id));
        });

        t_true(!is_file($base . '/var/tmp/generator.lock'), 'the next run is not blocked');
        t_true(in_array('building', CronSchedSource::$statuses, true), 'the build had started');
    },
];
