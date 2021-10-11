<?php

namespace Siberian;

/**
 * Class Media
 * @package Siberian
 *
 * @author Xtraball SAS <dev@xtraball.com>
 * @version 4.20.21
 */
class Media
{
    /**
     * @var bool
     */
    protected static $temporary_disabled = false;
    /**
     * @var array
     */
    public static $tools = [
        'jpg' => [
            'jpegoptim' => [
                'bin' => '/usr/local/bin/jpegoptim',
                'cli' => '/usr/local/bin/jpegoptim -s -q -m 60 %s'
            ],
        ],
        'png' => [
            'pngquant' => [
                'bin' => '/usr/local/bin/pngquant',
                'cli' => '/usr/local/bin/pngquant --skip-if-larger --ext .png --force -- %s'
            ],
            'optipng' => [
                'bin' => '/usr/local/bin/optipng',
                'cli' => '/usr/local/bin/optipng -strip all -quiet -o3 %s'
            ],
        ],
    ];

    /**
     * @param $imagePath
     * @param bool $force
     * @throws \Zend_Exception
     */
    public static function optimize($imagePath, $force = false)
    {
        /** Dev global disable. */
        $_config = \Zend_Registry::get('_config');
        if (isset($_config['disable_media'])) {
            return;
        }

        /** Temporary disabled */
        if (self::$temporary_disabled) {
            return;
        }

        /** Disable if not cron && sae */
        if (!$force) {
            if (!\Cron_Model_Cron::is_active()) {
                return;
            }
        }

        if (!is_writable($imagePath)) {
            return;
        }

        $filetype = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION));
        if (array_key_exists($filetype, self::$tools)) {
            $tools = self::$tools[$filetype];

            foreach ($tools as $toolbin => $options) {
                $path = self::isInstalled($options['bin']);

                if ($path !== false) {
                    exec("{$path} -h", $output);
                    if (isset($output) && isset($output[0]) && !empty($output[0])) {
                        if (strpos($path, '/local') !== false) {
                            $cli = $options['cli'];
                        } else {
                            $cli = str_replace('/local', '', $options['cli']);
                        }
                        $bin = sprintf($cli, $imagePath);

                        self::log("optimizing media %s", $bin);

                        exec($bin . " 2>&1", $result);
                    }
                }
            }
        }
    }

    /**
     * Disable until next reload
     */
    public static function disableTemporary()
    {
        if (!self::$temporary_disabled) {
            self::log('disableTemporary');

            self::$temporary_disabled = true;
        }
    }

    /**
     * Re enable from a previous disable
     */
    public static function enable()
    {
        self::$temporary_disabled = false;
    }

    /**
     * @return bool
     */
    public static function isTemporaryDisabled()
    {
        return self::$temporary_disabled;
    }

    /**
     * @param $binary_path
     * @return bool|mixed
     */
    public static function isInstalled($binaryPath)
    {
        if (self::exists_path($binaryPath)) {
            return $binaryPath;
        }
        if (self::exists_path(str_replace("/local", "", $binaryPath))) {
            return str_replace("/local", "", $binaryPath);
        }
        return false;
    }

    /**
     * @param $binary_path
     * @return bool
     */
    public static function exists_path($binary_path): bool
    {
        $result = file_exists($binary_path);
        if (!$result) {
            try {
                exec("if [ -f {$binary_path} ];then echo 1; else echo 0; fi 2>&1", $output);
                if (!empty($output) && isset($output[0])) {
                    $result = ($output[0] == 1);
                }
            } catch (\Exception $e) {
                $result = false;
            }
        }

        return $result;
    }

    /**
     * Check the libraries installed
     *
     * @return array
     */
    public static function getLibraries(): array
    {
        $libraries = [];
        foreach (self::$tools as $tools) {
            foreach ($tools as $short_name => $options) {
                $libraries[$short_name] = (self::isInstalled($options["bin"]) !== false);
            }
        }

        // ClamAV test
        $clamav = new ClamAV();
        $libraries['clamav'] = $clamav->ping();

        return $libraries;
    }

    /**
     * @param $path
     * @return string
     */
    public static function toBase64($path): string
    {
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);

        return sprintf("data:image/%s;base64,%s", $type, base64_encode($data));
    }

    /**
     * @param $message
     */
    public static function log($message)
    {
        $message = sprintf("[Siberian\Media] %s \n", $message);
        log_info($message);
        if (defined("CRON")) {
            echo $message;
        }
    }
}
