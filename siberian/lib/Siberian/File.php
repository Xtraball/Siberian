<?php

namespace Siberian;

/**
 * Class \Siberian\File
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
        if (__getConfig("debugFiles") === true) {
            $allFiles = true;
            $watchedFiles = [
                //"/Volumes/SSD2/Developments/repos/xtraball.com/siberian/siberian/var/apps/ionic/ios/www/index-prod.html"
            ];
            if (in_array($filename, $watchedFiles) || $allFiles) {
                dbg(
                    "File::putContents",
                    $filename
                );
            }
        }

        return file_put_contents($filename, $data, $flags, $context);
    }
}