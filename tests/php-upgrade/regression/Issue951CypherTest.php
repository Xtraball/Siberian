<?php
/**
 * #951: Siberian\Cypher::dcRun() read the .ec.php counterpart of a module file without checking it exists; a module
 * that does not ship one logged "Failed to open stream" on every request. It is now "not available", as a file that
 * does not decrypt already was.
 */
$siberian = dirname(__DIR__, 3) . '/siberian';

/** Runs $fn and returns [result, what PHP complained about while it ran], without failing the test. */
$complaints = function (callable $fn) {
    $seen = [];
    set_error_handler(function ($no, $msg) use (&$seen) {
        if (!(error_reporting() & $no)) {
            return false; // @-suppressed
        }
        $seen[] = $msg;

        return true;
    });
    try {
        $result = $fn();
    } finally {
        restore_error_handler();
    }

    return [$result, $seen];
};

/** The helpers dcRun() leans on come from the application; bare stand-ins are enough for the missing-file path. */
$load = function () use ($siberian) {
    if (!class_exists('Siberian\Exception', false)) {
        eval('namespace Siberian; class Exception extends \Exception {}');
    }
    if (!function_exists('isDev')) {
        eval('function isDev() { return false; }');
    }
    require_once $siberian . '/lib/Siberian/Cypher.php';
};

return [
    '951 a module without its .ec.php is reported as not available, without a warning' => function () use ($load, $complaints) {
        $load();

        [$thrown, $seen] = $complaints(function () {
            try {
                Siberian\Cypher::dcRun('app/local/modules/NoSuchModule/Extension.php', 'NoSuchModule');
            } catch (Exception $e) {
                return $e;
            }

            return null;
        });
        t_true($thrown instanceof Siberian\Exception, 'Siberian\Exception thrown');
        t_equal([], $seen, 'nothing is reported');
    },

    '951 the file is looked up before the keys are read' => function () use ($siberian) {
        $source = file_get_contents($siberian . '/lib/Siberian/Cypher.php');
        $start = strpos($source, 'function dcRun');
        $body = substr($source, $start);
        $check = strpos($body, 'is_file(');
        $keys = strpos($body, "__get('siberiancms_key')");
        t_true($check !== false && $keys !== false && $check < $keys, 'is_file() comes first');
    },
];
