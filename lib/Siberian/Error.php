<?php

/**
 * Class Siberian_Error
 *
 * @version 4.3.1
 *
 * @todo
 *  so much things
 *  handle errors,
 *  verbose logging,
 *  interfacing with Siberian_Log
 *  should be verbose (in log files, even in production !!! at least for support & information)
 *  refactoring
 *
 */

class Siberian_Error
{
    public static $errors = array();
    public static $sql_queries = array();
    public static $logger;

    public static function init() {
        # Disabling for now
        # set_error_handler(array('Siberian_Error', 'handleError'));
    }

    public static function handleError($errno, $errstr, $errfile, $errline) {
        # Do nothing.
    }

    /**
     * @deprecated
     * Artifact from the past
     */
    public static function backtrace($dumpError = false) {
        $errors = debug_backtrace();
        $dump = '';
        foreach($errors as $error) {
            if(!empty($error['file'])) $dump .= 'file : ' . $error['file'];
            if(!empty($error['function'])) $dump .= ':: ' . $error['function'];
            if(!empty($error['line'])) $dump .= ' (l:' . $error['line'] . ')';
            $dump .= '
';
        }

        if($dumpError) {
            Zend_Debug::dump($dump);
        } else {
            return $dump;
        }
    }

    public static function count() {
        return count(self::$errors);
    }

    public static function end() {
        # Do nothing.
    }

    /**
     * Search for old info_* files and clean
     */
    public static function clear() {
        # Do nothing.
    }
}
