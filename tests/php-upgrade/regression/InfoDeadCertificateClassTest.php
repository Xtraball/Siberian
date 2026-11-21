<?php
/**
 * \Siberian\Info::fetch() (the "server information" diagnostic block, cli and System > Advanced) unconditionally
 * fatals: `'PHP cURL HTTP/2 support' => \Push_Model_Certificate::testHttp2()['message']` calls a class that was
 * deleted from `app/sae/modules/Push/Model/Certificate.php` on 2023-01-15 (commit 7d4202076), almost three years
 * before `Info.php` itself was added whole-cloth in #1056 (chore: release 5.1.15, 2026-11-09). Every call to
 * fetch() raises "Uncaught Error: Class "Push_Model_Certificate" not found". There is no replacement: nothing
 * else in the codebase tests cURL's HTTP/2 support, so the field is removed rather than rewired.
 */
$siberian = dirname(__DIR__, 3) . '/siberian';
require_once $siberian . '/lib/Siberian/Pure.php';
require_once $siberian . '/lib/Siberian/Info.php';

// __get() (Pure.php) reads System_Model_Config, which needs a database; fetch() does not need a real one.
if (!class_exists('System_Model_Config')) {
    class System_Model_Config
    {
        public static function getValueFor($code)
        {
            return 'test-' . $code;
        }
    }
}

// Version.php is generated at package-build time (docs/editions.md) and is not in this repository.
if (!class_exists('Siberian\\Version')) {
    $dir = sys_get_temp_dir() . '/info-test-' . bin2hex(random_bytes(4));
    mkdir($dir, 0777, true);
    file_put_contents($dir . '/Version.php', "<?php\nnamespace Siberian;\nclass Version\n{\n    const TYPE = 'SAE';\n    const VERSION = '5.1.15';\n}\n");
    require $dir . '/Version.php';
}

return [
    'fetch() no longer calls the class deleted in 2023, and does not fatal' => function () use ($siberian) {
        $info = \Siberian\Info::fetch();
        t_true(is_array($info), 'returns the diagnostic array');
        t_true(isset($info['PHP Version']) && isset($info['Update channel']), 'the surrounding fields are untouched');
        t_equal(false, isset($info['PHP cURL HTTP/2 support']), 'the dead field is removed, not left empty');

        $source = file_get_contents($siberian . '/lib/Siberian/Info.php');
        t_equal(0, substr_count($source, 'Push_Model_Certificate'), 'the deleted class is never referenced again');
    },
];
