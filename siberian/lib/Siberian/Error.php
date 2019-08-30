<?php

namespace Siberian;

/**
 * Class Siberian_Error
 * @package Siberian
 */
class Error
{
    /**
     * PHP CONSTANTS ERRORS
     *
     * @var array
     */
    public static $errors = array(
        1 => 'E_ERROR',
        2 => 'E_WARNING',
        4 => 'E_PARSE',
        8 => 'E_NOTICE',
        16 => 'E_CORE_ERROR',
        32 => 'E_CORE_WARNING',
        64 => 'E_COMPILE_ERROR',
        128 => 'E_COMPILE_WARNING',
        256 => 'E_USER_ERROR',
        512 => 'E_USER_WARNING',
        1024 => 'E_USER_NOTICE',
        2048 => 'E_STRICT',
        4096 => 'E_RECOVERABLE_ERROR',
        8192 => 'E_DEPRECATED',
        16384 => 'E_USER_DEPRECATED',
        32767 => 'E_ALL',
    );
    /**
     * @var array
     */
    public static $sql_queries = array();
    /**
     * @var Log
     */
    public static $logger = null;

    /**
     *
     */
    public static function init()
    {
        set_error_handler(array('\Siberian\Error', 'handleError'));
        register_shutdown_function(array('\Siberian\Error', 'handleFatalError'));
    }

    /**
     * @param $errno
     * @param $errstr
     * @param $errfile
     * @param $errline
     * @throws \Zend_Exception
     */
    public static function handleError($errno, $errstr, $errfile, $errline)
    {
        if (\Zend_Registry::isRegistered("logger") && (self::$logger == null)) {
            self::$logger = \Zend_Registry::get("logger");
        }

        if (self::$logger != null) {
            $err_text = (isset(self::$errors[$errno])) ? self::$errors[$errno] : $errno;
            try {
                self::$logger->err(sprintf("[%s]: %s in %s line %s", $err_text, $errstr, $errfile, $errline));
            } catch (Exception $e) {
                # Hum.
            }

        }
    }

    /**
     * @throws \Zend_Exception
     */
    public static function handleFatalError()
    {
        $last_error = error_get_last();
        $fatal_log = path("var/log/fatal-error.log");
        File::putContents($fatal_log, print_r($last_error, true) . "\n", FILE_APPEND);
    }

    /**
     * @deprecated
     * Artifact from the past
     */
    public static function backtrace($dumpError = false)
    {
        $errors = debug_backtrace();
        $dump = '';
        foreach ($errors as $error) {
            if (!empty($error['file'])) $dump .= 'file : ' . $error['file'];
            if (!empty($error['function'])) $dump .= ':: ' . $error['function'];
            if (!empty($error['line'])) $dump .= ' (l:' . $error['line'] . ')';
            $dump .= '';
        }

        if ($dumpError) {
            return \Zend_Debug::dump($dump);
        }
        return $dump;
    }

    /**
     * @return int
     */
    public static function count()
    {
        return count(self::$errors);
    }

    /**
     *
     */
    public static function end()
    {
        # Do nothing.
    }

    /**
     * Search for old info_* files and clean
     */
    public static function clear()
    {
        # Do nothing.
    }
}
