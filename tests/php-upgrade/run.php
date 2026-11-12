<?php
/**
 * Regression runner. Usage: php tests/php-upgrade/run.php [--suite=regression|db] [--fail-fast] [filter]
 *
 * Each regression/*Test.php returns ['test name' => callable]. Warnings and
 * errors raised inside a test fail it; deprecations are counted and reported
 * but do not fail (the lint ratchet owns those).
 */
$suite = 'regression';
foreach ($argv as $arg) {
    if (strpos($arg, '--suite=') === 0) {
        $suite = substr($arg, strlen('--suite='));
    }
}
// --suite=db is tier 1: real classes against a real database server (see db/bootstrap.php)
if ($suite === 'db') {
    require __DIR__ . '/db/bootstrap.php';
    if (!t_db_available()) {
        echo "skip  tier 1 (db): SIB_TEST_DB_HOST is not set\n";
        exit(0);
    }
} else {
    require __DIR__ . '/bootstrap.php';
}

// Whatever a test prints (and the FAIL lines) stays buffered until the end: output sent early makes headers_sent() true
// and fails the session tests that run after a failing one (session_set_save_handler returns false).
ob_start();

$failFast = in_array('--fail-fast', $argv, true);
$filter = null;
foreach (array_slice($argv, 1) as $arg) {
    if ($arg[0] !== '-') {
        $filter = $arg;
    }
}

$deprecations = 0;
set_error_handler(function ($no, $msg, $file, $line) use (&$deprecations) {
    if (!(error_reporting() & $no)) {
        return false; // @-suppressed
    }
    if ($no & (E_DEPRECATED | E_USER_DEPRECATED)) {
        $deprecations++;
        return true;
    }
    throw new ErrorException($msg, 0, $no, $file, $line);
});
error_reporting(E_ALL);

$pass = 0;
$failed = [];

foreach (glob(__DIR__ . '/' . $suite . '/*Test.php') as $file) {
    foreach (require $file as $name => $test) {
        $id = basename($file, '.php') . ' :: ' . $name;
        if ($filter !== null && stripos($id, $filter) === false) {
            continue;
        }
        try {
            $test();
            $pass++;
        } catch (Throwable $e) {
            $failed[$id] = $e->getMessage() . ' (' . basename($e->getFile()) . ':' . $e->getLine() . ')';
            echo "FAIL  $id\n      {$failed[$id]}\n";
            if ($failFast) {
                break 2;
            }
        }
    }
}

printf("PHP %s: %d passed, %d failed, %d deprecations during run\n", PHP_VERSION, $pass, count($failed), $deprecations);
exit($failed ? 1 : 0);
