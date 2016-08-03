<?php
/**
 * Siberian_Media try to optimize png/jpg images before packaging the native sources
 *
 * it needs external libraries, like jpegoptim, pngquant, optipng etc ...
 */
class Siberian_Media {

    public static $tools = array(
        "jpg" => array(
            "jpegoptim" => array(
                "bin" => "/usr/local/bin/jpegoptim",
                "cli" => "/usr/local/bin/jpegoptim -m 70 -f %s"
            ),
        ),
        "png" => array(
            "pngquant" => array(
                "bin" => "/usr/local/bin/pngquant",
                "cli" => "/usr/local/bin/pngquant --ext .png --force -- %s"
            ),
            "optipng" => array(
                "bin" => "/usr/local/bin/optipng",
                "cli" => "/usr/local/bin/optipng -o5 %s"
            ),
        ),
    );

    public static function optimize($path) {
        if(!is_writable($path)) {
            return;
        }

        $filetype = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if(array_key_exists($filetype, self::$tools)) {
            $tools = self::$tools[$filetype];

            foreach($tools as $toolbin => $options) {
                if(file_exists($options["bin"])) {
                    exec("{$options["bin"]} -h", $output);
                    if(isset($output) && isset($output[0]) && !empty($output[0])) {
                        $bin = sprintf($options["cli"], $path);
                        exec($bin, $result);
                        break;
                    }
                }
            }
        }
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
                $binary_path = $options["bin"];
                $libraries[$short_name] = (file_exists($binary_path));
            }
        }

        return $libraries;
    }
}