<?php
/**
 * Bootstrap runs the init.php of every enabled module inside an output buffer. When an init throws, the buffer used to
 * stay open: what the module had printed was sent with the page at the end of the request, and the level of the buffers
 * drifted by one for every failing module. The catch now closes the buffers opened since the init started.
 * Bootstrap needs the whole application, so the rule is checked on the source and on the pattern itself.
 */
$code = file_get_contents(dirname(__DIR__, 3) . '/siberian/app/Bootstrap.php');
$start = strpos($code, '$pathInit = ');
$block = substr($code, $start, strpos($code, '} else if (is_readable($path))', $start) - $start);

return [
    'the buffer level is read before the init buffer is opened' => function () use ($block) {
        $level = strpos($block, '$obLevel = ob_get_level();');
        $open = strpos($block, 'ob_start();');
        t_true($level !== false && $open !== false && $level < $open, 'level read first');
    },

    'the catch of a failing init closes the buffers it opened' => function () use ($block) {
        $catch = strpos($block, 'catch (Exception $e)');
        $drain = strpos($block, 'while (isset($obLevel) && ob_get_level() > $obLevel)');
        $log = strpos($block, "trigger_error(sprintf('Module %s init: %s'");
        t_true($catch !== false && $drain > $catch, 'drained in the catch');
        t_true($log > $drain, 'the failure is still logged, after the buffer is closed');
    },

    'the success path is unchanged' => function () use ($block) {
        t_equal(2, substr_count($block, 'ob_end_clean()'), 'one on success, one in the catch');
        t_true(strpos($block, '$init($this);') !== false, 'the init still gets the bootstrap');
    },

    'closing down to the saved level leaves outer buffers alone' => function () {
        // the same loop as the catch, with an outer buffer that belongs to someone else
        $outer = ob_get_level();
        ob_start();
        $level = ob_get_level();
        ob_start();
        echo 'printed by a failing init';
        ob_start();            // an init that opened a second buffer and then threw
        while ($level !== null && ob_get_level() > $level) {
            ob_end_clean();
        }
        t_equal($level, ob_get_level(), 'back to the level saved before the init');
        ob_end_clean();
        t_equal($outer, ob_get_level(), 'the outer buffer is untouched');
    },
];
