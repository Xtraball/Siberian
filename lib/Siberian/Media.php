<?php
/**
 * Siberian_Media try to optimize png/jpg images before packaging the native sources
 *
 * it needs external libraries, like jpegoptim, pngquant, optipng etc ...
 */
class Siberian_Media {

    public static $tools = array(
        "jpg" => array(
            "jpegoptim" => array("cli" => "jpegoptim")
        ),
        "png" => array(
            "pngquant" => array("cli" => "pngquant -- "),
            "optipng" => array("cli" => "optipng")
        ),
    );

    public static function optimize($path) {
        $filetype = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if(array_key_exists($filetype, self::$tools)) {
            $tools = self::$tools[$filetype];

            foreach($tools as $toolbin => $options) {
                if(exec("which {$toolbin}")) {
                    exec("{$options["cli"]} {$path}");
                    break;
                }
            }
        }
    }
}