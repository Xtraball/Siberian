<?php
/**
 * Minify::build() calls replaceIndex() for every platform on every request, including platforms
 * an install never built (e.g. the ios/android ionic apps, when that install only ships browser).
 * replaceIndex() read $source with file_get_contents() unconditionally, then tried to write $dest
 * via File::putContents() into a directory that, on those installs, doesn't exist either — two
 * warnings, every single call, forever. _minify() (used for the css/js steps) already guards the
 * same situation with `is_readable($content)`; replaceIndex() just never got the same guard
 * (production error groups at Minify.php:343/368 and File.php:77, ~920k occurrences across 9
 * installs on 5.1.25).
 *
 * Minify's own file can't be required stand-alone: its top-level `require path(...)` call needs
 * Core_Model_Directory::$_base_path set first (normally done by app bootstrap). That's a one-line,
 * lightweight dependency (a static string, not a database or a registry), so it's set up directly
 * here rather than treating this as too heavy to test, unlike the DB-backed System_Model_Config
 * that the *rest* of replaceIndex() reaches for (__get('current_release')) once past this guard —
 * that part is exactly the kind of dependency this suite avoids, and is used here only as a probe:
 * reaching it proves the method did NOT take the early-return path.
 */
$siberian = dirname(__DIR__, 3) . '/siberian';
require_once $siberian . '/app/sae/modules/Core/Model/Directory.php';
Core_Model_Directory::setBasePath($siberian);
require_once $siberian . '/lib/Siberian/Pure.php';
require_once $siberian . '/lib/Siberian/Minify.php';

return [
    'a platform whose source index.html was never built is skipped silently' => function () {
        $minify = new \Siberian\Minify();
        $missingSource = sys_get_temp_dir() . '/minify-test-' . bin2hex(random_bytes(4)) . '/www/index.html';
        $dest = str_replace('index', 'index-prod', $missingSource);

        t_true(!is_file($missingSource), 'fixture precondition: the source really is missing');

        // Must not warn/error, and must not attempt the doomed write.
        $minify->replaceIndex('ios', $missingSource, true, false);

        t_true(!file_exists($dest), 'nothing was written when the source never existed');
    },
    'an existing source is NOT short-circuited by the new guard (still tries to proceed past it)' => function () {
        $minify = new \Siberian\Minify();
        $dir = sys_get_temp_dir() . '/minify-test-' . bin2hex(random_bytes(4));
        mkdir($dir, 0777, true);
        $source = $dir . '/index.html';
        file_put_contents($source, '<html><head></head><body></body></html>');

        // Past the is_readable() guard, replaceIndex() reaches __get('current_release'), which
        // needs System_Model_Config and a real DB connection this suite deliberately doesn't set
        // up. Reaching that far (rather than returning cleanly, as the previous case does) is the
        // proof the guard only trips on a genuinely unreadable source, not on every call.
        $reachedPastGuard = false;
        try {
            $minify->replaceIndex('ios', $source, false, false);
        } catch (\Throwable $e) {
            $reachedPastGuard = true;
        }
        t_true($reachedPastGuard, 'a readable source is not skipped by the new guard');
    },
];
