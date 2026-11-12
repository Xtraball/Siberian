<?php
/**
 * Dashboard web space: sizes in bytes, the "other" part, and where the total stands against the informative quota.
 *
 * Siberian\Cache\DiskUsage has no dependency, the real class is loaded.
 */
$root = dirname(__DIR__, 3) . '/siberian/';
require_once $root . 'lib/Siberian/Cache/DiskUsage.php';

use Siberian\Cache\DiskUsage;

const MB = 1024 * 1024;
const GB = 1024 * 1024 * 1024;

return [
    'human() prints sizes like du -h' => function () {
        t_equal('12K', DiskUsage::human(12 * 1024), 'kilobytes');
        t_equal('4.3M', DiskUsage::human((int) (4.3 * MB)), 'one decimal under 10');
        t_equal('891M', DiskUsage::human(891 * MB), 'no decimal from 10');
        t_equal('1.5G', DiskUsage::human((int) (1.5 * GB)), 'gigabytes');
        t_equal('1G', DiskUsage::human(GB), 'no trailing .0');
        t_equal('0B', DiskUsage::human(0), 'nothing');
        t_equal('512B', DiskUsage::human(512), 'below a kilobyte');
    },

    'parse() reads the text of a measure taken before the bytes were kept' => function () {
        t_equal(12 * 1024, DiskUsage::parse('12K'), 'K');
        t_equal(891 * MB, DiskUsage::parse('891M'), 'M');
        t_equal((int) round(4.3 * MB), DiskUsage::parse('4.3M'), 'decimal');
        t_equal(GB, DiskUsage::parse('1G'), 'G');
        t_equal(null, DiskUsage::parse('-'), 'a failed measure');
        t_equal(null, DiskUsage::parse(null), 'nothing');
        t_equal(null, DiskUsage::parse('lots'), 'garbage');
    },

    'normalize() fills the bytes from an old measure and computes the rest' => function () {
        $usage = DiskUsage::normalize([
            'total' => '891M', 'log_size' => '12K', 'cache_size' => '4.3M',
            'cache_images_size' => '1.2M', 'tmp_size' => '772K',
        ]);
        t_equal(891 * MB, $usage['total_bytes'], 'total in bytes');
        t_equal(12 * 1024, $usage['log_bytes'], 'log in bytes');
        $known = $usage['log_bytes'] + $usage['cache_bytes'] + $usage['cache_images_bytes'] + $usage['tmp_bytes'];
        t_equal($usage['total_bytes'] - $known, $usage['other_bytes'], 'other is what no folder explains');

        $failed = DiskUsage::normalize(['total' => '-', 'log_size' => '-', 'cache_size' => '-',
            'cache_images_size' => '-', 'tmp_size' => '-']);
        t_equal(null, $failed['total_bytes'], 'no measure, no total');
        t_equal(null, $failed['other_bytes'], 'and no other');

        $kept = DiskUsage::normalize(['total' => '9M', 'total_bytes' => 5 * MB, 'log_size' => '1K', 'log_bytes' => 2048,
            'cache_size' => '-', 'cache_images_size' => '-', 'tmp_size' => '-']);
        t_equal(5 * MB, $kept['total_bytes'], 'a byte value that exists wins over the text');
        t_equal(2048, $kept['log_bytes'], 'also for a folder');
    },

    'other never goes below zero' => function () {
        $usage = DiskUsage::normalize(['total' => '1M', 'log_size' => '2M', 'cache_size' => '-',
            'cache_images_size' => '-', 'tmp_size' => '-']);
        t_equal(0, $usage['other_bytes'], 'folders measured after the total can add up to more');
    },

    'validQuota() accepts a size in GB, an empty value, and nothing else' => function () {
        t_equal(null, DiskUsage::validQuota(''), 'empty is no quota');
        t_equal(null, DiskUsage::validQuota(null), 'null is no quota');
        t_equal(null, DiskUsage::validQuota('   '), 'spaces are no quota');
        t_equal(5.0, DiskUsage::validQuota('5'), 'integer');
        t_equal(2.5, DiskUsage::validQuota('2.5'), 'decimal');
        t_equal(2.5, DiskUsage::validQuota('2,5'), 'decimal comma');
        t_equal(false, DiskUsage::validQuota('0'), 'zero is refused: empty means none');
        t_equal(false, DiskUsage::validQuota('-3'), 'negative');
        t_equal(false, DiskUsage::validQuota('lots'), 'text');
        t_equal(false, DiskUsage::validQuota('1e9'), 'absurd');
        t_equal(false, DiskUsage::validQuota('5 GB'), 'unit typed in the value');
    },

    'withQuota() gives the share used and the level' => function () {
        $base = DiskUsage::normalize(['total' => '1G', 'log_size' => '-', 'cache_size' => '-',
            'cache_images_size' => '-', 'tmp_size' => '-']);

        $none = DiskUsage::withQuota($base, '');
        t_equal('none', $none['quota_level'], 'no quota, no level');
        t_equal(null, $none['quota_bytes'], 'no quota in bytes');
        t_equal(null, $none['quota_percent'], 'no percent');

        $ok = DiskUsage::withQuota($base, '5');
        t_equal(5 * GB, $ok['quota_bytes'], 'quota in bytes');
        t_equal(20.0, $ok['quota_percent'], '1G of 5G');
        t_equal('ok', $ok['quota_level'], 'under 80%');

        t_equal('warn', DiskUsage::withQuota($base, '1.2')['quota_level'], '83% asks to take a look');
        t_equal('danger', DiskUsage::withQuota($base, '1.04')['quota_level'], '96% is urgent');
        t_equal('danger', DiskUsage::withQuota($base, '0.5')['quota_level'], 'over the quota');
        t_equal(200.0, DiskUsage::withQuota($base, '0.5')['quota_percent'], 'the percent is not capped');

        $unmeasured = DiskUsage::withQuota(DiskUsage::normalize(['total' => '-', 'log_size' => '-', 'cache_size' => '-',
            'cache_images_size' => '-', 'tmp_size' => '-']), '5');
        t_equal('none', $unmeasured['quota_level'], 'a quota with no measure has no level yet');
        t_equal(5.0, $unmeasured['quota_gb'], 'but it is kept');
    },

    'the dashboard reads the new data and the wiring is there' => function () use ($root) {
        $design = $root . 'app/sae/design/desktop/backoffice/';
        $template = file_get_contents($design . 'template/backoffice/index/index.phtml');
        t_true(strpos($template, 'id="webspace-manage"') !== false, 'the Manage modal is in the dashboard');
        t_true(strpos($template, 'openWebSpace()') !== false, 'the Manage button opens it');
        foreach (['log', 'cache_images', 'tmp', 'app_manifest'] as $type) {
            t_true(strpos($template, "clear('$type'") !== false, "$type can still be cleared from the modal");
        }
        t_true(strpos($template, 'sub-table') === false || strpos($template, 'Web space') === false
            || !preg_match('/Web space.{0,400}sub-table/s', $template), 'the old table of the web space is gone');

        $js = file_get_contents($design . 'js/controllers/homepage.js');
        t_true(strpos($js, 'saveQuota') !== false, 'the controller saves the quota');
        $factory = file_get_contents($design . 'js/factories/homepage.js');
        t_true(strpos($factory, 'backoffice/index/setquota') !== false, 'the factory posts to setquota');

        $controller = file_get_contents($root . 'app/sae/modules/Backoffice/Controller/Index/Abstract.php');
        t_true(strpos($controller, 'function setquotaAction') !== false, 'the action exists');

        $seed = file_get_contents($root . 'app/sae/modules/System/resources/db/data/system.php');
        t_true(strpos($seed, "'web_space_quota_gb'") !== false, 'the quota is a system config');
        t_equal(DiskUsage::QUOTA_KEY, 'web_space_quota_gb', 'with the code the class reads');
    },

    'the measure keeps the keys of the earlier dashboard' => function () use ($root) {
        $cache = file_get_contents($root . 'lib/Siberian/Cache.php');
        foreach (array_keys(DiskUsage::FOLDERS) as $key) {
            t_true(strpos($cache, "'$key'") !== false, "getDiskUsage() still reports $key");
        }
        t_true(strpos($cache, 'du -cksh') === false, 'no more du -h parsing');
    },
];
