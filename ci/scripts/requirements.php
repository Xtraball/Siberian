<?php

if (version_compare(phpversion(), '5.6', '<')) {
    exit('PHP 5.6+ is required \n');
}

class Requirements {

    public static $_functions = [
        'exec',
    ];

    public static $_extensions = [
        'SimpleXML',
        'pdo_mysql',
        'gd',
        'mbstring',
        'iconv',
        'curl',
        'openssl',
    ];

    public static $_binaries = [
        'zip',
        'unzip',
    ];

    public static $_errors = [];

    public static function runTest() {

        self::testFunctions();
        self::testExtensions();
        self::testExec();

        if (!empty($_errors)) {
            echo 'Following requirements are missing: ' . PHP_EOL;
            echo implode(PHP_EOL, $_errors);
            echo '...' . PHP_EOL;
        } else {
            echo 'Everything seems ok. ' . PHP_EOL;
        }
    }

    /**
     *
     */
    public static function testFunctions() {
        foreach (self::$_functions as $function) {
            if (!function_exists($function)) {
                $_errors[] = 'Please enable/add function: ' . $function . '()';
            }
        }
    }

    /**
     *
     */
    public static function testExtensions() {
        foreach (self::$_extensions as $extension) {
            if (!extension_loaded($extension)) {
                $_errors[] = 'Please enable/add extension: ' . $extension;
            }
        }
    }

    /**
     *
     */
    public static function testExec() {
        if (function_exists('exec')) {
            $which1 = exec('which zip');
            if (empty($which1)) {
                $_errors[] = 'Please enable/add binary: zip';
            }

            $which2 = exec('which unzip');
            if (empty($which2)) {
                $_errors[] = 'Please enable/add binary: unzip';
            }

        } else {
            $_errors[] = 'Please enable/add function: exec()';
        }

        if (OPENSSL_VERSION_NUMBER < 268439647) {
            $_errors[] = 'Please update OpenSSL to 1.0.1+';
        }
    }

}

Requirements::runTest();