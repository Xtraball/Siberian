<?php
/**
 * A listener of a hook that fails must never break the action that triggered the hook. Exceptions were already caught;
 * PHP 8 incompatibilities in a module raise Errors (TypeError, undefined function, call to a non-static method), which
 * took down the mobile login with a 500 on every call.
 */
require_once dirname(__DIR__, 3) . '/siberian/lib/Siberian/Logger.php';
require_once dirname(__DIR__, 3) . '/siberian/lib/Siberian/Hook.php';

use Siberian\Hook;

// Hook::trigger logs the failure through the registered logger.
class HookThrowableLogger
{
    public static $lines = [];

    public function info($message)
    {
        self::$lines[] = $message;
    }
}
\Zend_Registry::set('logger', new HookThrowableLogger());

class HookThrowableProbe
{
    public function notStatic()
    {
        return 1;
    }
}

$run = function (callable $failing) {
    Hook::$hooks = [];
    Hook::listen('test.throwable', 'before', function ($p) { $p['before'] = true; return $p; }, 1);
    Hook::listen('test.throwable', 'failing', $failing, 2);
    Hook::listen('test.throwable', 'after', function ($p) { $p['after'] = true; return $p; }, 3);
    return Hook::trigger('test.throwable', ['start' => true]);
};

return [
    'a listener that throws an Exception is skipped' => function () use ($run) {
        $p = $run(function ($p) { throw new \Exception('boom'); });
        t_true(!empty($p['before']) && !empty($p['after']), 'the other listeners ran');
    },

    'a listener that throws an Error is skipped' => function () use ($run) {
        $p = $run(function ($p) { throw new \Error('boom'); });
        t_true(!empty($p['before']) && !empty($p['after']), 'the other listeners ran');
        t_true(strpos(end(HookThrowableLogger::$lines), 'Error: boom') !== false, 'the class of the failure is logged');
    },

    'a TypeError, an undefined function and a non-static call are skipped' => function () use ($run) {
        foreach ([
            function ($p) { return strlen([]); },
            function ($p) { return hook_throwable_undefined_function(); },
            function ($p) { return HookThrowableProbe::notStatic(); },
        ] as $failing) {
            $p = $run($failing);
            t_true(!empty($p['start']) && !empty($p['before']) && !empty($p['after']), 'the payload went through');
            t_true(!array_key_exists('failing', $p), 'the failing listener changed nothing');
        }
    },
];
