<?php

namespace Siberian;

/**
 * Class File
 * @package Siberian
 */
class File
{
    /**
     * @param $filename
     * @param bool $use_include_path
     * @param null $context
     * @param int $offset
     * @param null $maxlen
     * @return false|string
     */
    public static function getContents ($filename, $use_include_path = false, $context = null, $offset = 0, $maxlen = null)
    {
        // Debug files
        return file_get_contents($filename, $use_include_path, $context, $offset, $maxlen);
    }

    /**
     * The content of a file, or an empty string when the path is empty, null or not a file. An empty path is a
     * ValueError in file_get_contents() on PHP 8 (a warning on PHP 7): a certificate without a chain has no path.
     *
     * @param $filename
     * @return string
     */
    public static function readOrEmpty ($filename): string
    {
        if (!is_string($filename) || $filename === '' || !is_file($filename)) {
            return '';
        }

        $content = file_get_contents($filename);

        return $content === false ? '' : $content;
    }

    /**
     * @param $filename
     * @param $data
     * @param int $flags
     * @param null $context
     * @return bool|int
     * @throws \Zend_Exception
     */
    public static function putContents ($filename, $data, $flags = 0, $context = null)
    {
        // Debug files
        if (__getConfig('debugFiles') === true) {
            $allFiles = false;
            $watchedFiles = __getConfig('debugFilesList') ?? [];
            $willWatch = false;
            if (!$allFiles) {
                foreach ($watchedFiles as $watchedFile) {
                    if (preg_match($watchedFile, $filename)) {
                        echo $watchedFile . ' > ' . $filename . PHP_EOL;
                        $willWatch = true;
                    }
                }
            }
            if ($allFiles || (!$allFiles && $willWatch)) {
                //$key = array_search(__FUNCTION__, array_column(debug_backtrace(), 'function'));
                //$backtrace = debug_backtrace()[$key];
                dbg(
                    'File::putContents {$filename}'
                    //$backtrace,
                    //debug_backtrace()
                );
            }
        }

        return file_put_contents($filename, $data, $flags, $context);
    }
}