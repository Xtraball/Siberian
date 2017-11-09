<?php

class Siberian_Tools_Integrity
{
    public static $manifest = "manifest.json";

    /**
     * Check installation integrity
     */
    public static function checkIntegrity() {
        $base_path = Core_Model_Directory::getBasePathTo("/");
        $manifest = json_decode(file_get_contents($base_path.self::$manifest), true);


        $error_files = array(
            "hash" => array(),
            "missing" => array(),
        );

        foreach($manifest as $info) {
            $file = $info["file"];
            $hash = $info["hash"];

            if(file_exists($base_path.$file)) {
                $test_hash = md5_file($base_path.$file);

                if($test_hash != $hash) {
                    $error_files["hash"][] = $file;
                }
            } else {
                $error_files["missing"][] = $file;
            }
        }

        return $error_files;
    }
}
