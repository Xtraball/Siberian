<?php

/**
 * Class Siberian_Error
 *
 * @version 4.1.0
 *
 * @todo
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
        set_error_handler(array('Siberian_Error', 'handleError'));
    }

    public static function handleError($errno, $errstr, $errfile, $errline) {
        /** Saving errors */
        self::$errors[] = sprintf("%s - %s line %s", $errstr, $errfile, $errline);
    }

    /** @unused Artifact from the past???? */
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

    public static function end()
    {
        self::clear();
        self::$logger = Zend_Registry::get("logger");

        if(APPLICATION_ENV == "development") {

            if (self::count() > 0) {
                $report = "\n";
                $count = 0;
                foreach (self::$errors as $error) {
                    $report .= "#{$count}: {$error} \n";
                    $count++;


                }
                self::$logger->info($report);
            }

        }
    }

    /**
     * Search for old info_* files and clean
     */
    public static function clear() {
        $log_path = Core_Model_Directory::getBasePathTo("var/log/");
        $files = glob("{$log_path}info_*.log");
        $files[] = "{$log_path}debug_report.log"; # Ensure the old file is removed.
        foreach($files as $file) {
            if(is_readable($file)) {
                $filemtime = filemtime($file) + 600;
                $time = time();
                if($time > $filemtime) {
                    unlink($file);
                }
            }
        }
    }
}
