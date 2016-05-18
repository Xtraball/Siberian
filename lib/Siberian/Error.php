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
        $path = Core_Model_Directory::getBasePathTo("/var/log/debug_report.log");

        //if ((APPLICATION_ENV == 'development') && (count(self::$errors) > 0)) {
        if (count(self::$errors) > 0) {
            $report = "";
            $count = 0;
            foreach (self::$errors as $error) {
                $report .= "#{$count} {$error} \n";
                $count++;
            }
            file_put_contents($path, $report, FILE_APPEND);
        }

        /** Limiting the debug_report log to 16MB */
        self::truncate($path, 128000000);
    }

    private static function truncate($filename, $maxfilesize){
        $size=filesize($filename);
        if ($size < $maxfilesize * 1.0) {
            return;
        }
        $fh = fopen($filename,"r+");
        $start = ftell($fh);
        fseek($fh,-$maxfilesize, SEEK_END);
        $drop = fgets($fh);
        $offset = ftell($fh);
        for ($x = 0; $x < $maxfilesize; $x++) {
            fseek($fh, $x+$offset);
            $c=fgetc($fh);
            fseek($fh, $x);
            fwrite($fh, $c);
        }
        ftruncate($fh, $maxfilesize - strlen($drop));
        fclose($fh);
    }
}
