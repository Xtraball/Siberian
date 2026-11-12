<?php
/**
 * SESSION_TYPE is defined by the controller when it sets up the session of a request. A view or model asking for the
 * session before that (cron, CLI, a mail or error view, a request that failed early) raised "Undefined constant
 * SESSION_TYPE" in Core/View/Default/Abstract.php::getSession(). Every reader now falls back to the default type, the
 * one of the editor ('front', Core_Model_Session::TYPE_ADMIN), the same one in the four places.
 * These classes cannot be loaded without the application, so the rule is checked on the source.
 */
$root = dirname(__DIR__, 3) . '/siberian/app/sae/modules/Core/';

return [
    'every use of SESSION_TYPE is guarded by defined()' => function () use ($root) {
        foreach (['View/Default/Abstract.php', 'Model/Default/Abstract.php', 'Controller/Default/Abstract.php'] as $file) {
            foreach (file($root . $file) as $n => $line) {
                if (!preg_match('/\bSESSION_TYPE\b/', $line) || preg_match('/define\(/', $line)) {
                    continue;
                }
                // `defined('SESSION_TYPE')` alone on a line is the guard of the next line
                t_true(
                    (bool) preg_match('/defined\(\'SESSION_TYPE\'\)/', $line),
                    $file . ':' . ($n + 1) . ' reads SESSION_TYPE without defined(): ' . trim($line)
                );
            }
        }
    },
    'the fallback is the default type, the same in all the readers' => function () use ($root) {
        foreach (['View/Default/Abstract.php' => 1, 'Model/Default/Abstract.php' => 2, 'Controller/Default/Abstract.php' => 1] as $file => $count) {
            $source = file_get_contents($root . $file);
            t_equal(
                $count,
                substr_count($source, "defined('SESSION_TYPE') ? SESSION_TYPE : Core_Model_Session::TYPE_ADMIN"),
                $file
            );
            t_true(strpos($source, ": 'admin'") === false, $file . ' still falls back to a type that does not exist');
        }
    },
    'the default type is the editor session' => function () use ($root) {
        $source = file_get_contents($root . 'Model/Session.php');
        t_true((bool) preg_match("/const TYPE_ADMIN = 'front';/", $source));
    },
];
