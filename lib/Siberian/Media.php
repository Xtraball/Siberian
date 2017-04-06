<?php
/**
 * Siberian_Media try to optimize png/jpg images before packaging the native sources
 *
 * it needs external libraries, like jpegoptim, pngquant, optipng etc ...
 */
class Siberian_Media {

    protected static $temporary_disabled = false;

    public static $tools = array(
        "jpg" => array(
            "jpegoptim" => array(
                "bin" => "/usr/local/bin/jpegoptim",
                "cli" => "/usr/local/bin/jpegoptim -s -q -m 60 %s"
            ),
        ),
        "png" => array(
            "pngquant" => array(
                "bin" => "/usr/local/bin/pngquant",
                "cli" => "/usr/local/bin/pngquant --skip-if-larger --ext .png --force -- %s"
            ),
            "optipng" => array(
                "bin" => "/usr/local/bin/optipng",
                "cli" => "/usr/local/bin/optipng -strip all -quiet -o3 %s"
            ),
        ),
    );

    public static function optimize($image_path, $force = false) {
        /** Dev global disable. */
        $_config = Zend_Registry::get("_config");
        if(isset($_config["disable_media"])) {
            return;
        }

        /** Temporary disabled */
        if(self::$temporary_disabled) {
            return;
        }

        /** Disable if not cron && sae */
        if(!$force) {
            if(!Cron_Model_Cron::is_active()) {
                return;
            }
        }

        if(!is_writable($image_path)) {
            return;
        }

        $filetype = strtolower(pathinfo($image_path, PATHINFO_EXTENSION));
        if(array_key_exists($filetype, self::$tools)) {
            $tools = self::$tools[$filetype];

            foreach($tools as $toolbin => $options) {
                $path = self::isInstalled($options["bin"]);

                if($path !== false) {
                    exec("{$path} -h", $output);
                    if(isset($output) && isset($output[0]) && !empty($output[0])) {
                        if(strpos($path, "/local") !== false) {
                            $cli = $options["cli"];
                        } else {
                            $cli = str_replace("/local", "", $options["cli"]);
                        }
                        $bin = sprintf($cli, $image_path);

                        self::log(__("[Siberian_Media] optimizing media %s", $bin));

                        exec($bin." 2>&1", $result);
                    }
                }
            }
        }
    }

    /**
     * Disable until next reload
     */
    public static function disableTemporary() {
        if(!self::$temporary_disabled) {
            self::log("[Siberian_Media] disableTemporary");

            self::$temporary_disabled = true;
        }
    }

    /**
     * Re enable from a previous disable
     */
    public static function enable() {
        self::$temporary_disabled = false;
    }

    /**
     * @return bool
     */
    public static function isTemporaryDisabled() {
        return self::$temporary_disabled;
    }

    /**
     * @param $binary_path
     * @return bool|mixed
     */
    public static function isInstalled($binary_path) {
        if(self::exists_path($binary_path)) {
            return $binary_path;
        } elseif(self::exists_path(str_replace("/local", "", $binary_path))) {
            return str_replace("/local", "", $binary_path);
        }
        return false;
    }

    /**
     * @param $binary_path
     * @return bool
     */
    public static function exists_path($binary_path) {
        $result = file_exists($binary_path);
        if(!$result) {
            try {
                exec("if [ -f {$binary_path} ];then echo 1; else echo 0; fi 2>&1", $output);
                if(!empty($output) && isset($output[0])) {
                    $result = ($output[0] == 1);
                }
            } catch (Exception $e) {
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
    public static function getLibraries() {
        $libraries = array();
        foreach(self::$tools as $tools) {
            foreach($tools as $short_name => $options) {
                $libraries[$short_name] = (self::isInstalled($options["bin"]) !== false);
            }
        }

        return $libraries;
    }

    /**
     * @param $path
     * @return string
     */
    public static function toBase64($path) {
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);
        $base64 = sprintf("data:image/%s;base64,%s", $type, base64_encode($data));

        return $base64;
    }

    /**
     * @param $message
     */
    public static function log($message) {
        log_info($message);
        if(defined("CRON")) {
            echo sprintf("[Siberian_Media] %s \n", $message);
        }
    }


}