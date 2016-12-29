<?php

class Core_Model_Directory
{
    protected static $_path;
    protected static $_base_path;
    protected static $_design_path;
    protected static $_design_paths;

    public static function getPathTo($path = '') {
        if(substr($path, 0, 1) !== '/') $path = '/'.$path;
        return self::$_path.$path;
    }

    public static function getBasePathTo($path = '') {
        if(substr($path, 0, 1) !== '/') $path = '/'.$path;
        if(stripos($path, self::$_base_path) === false) {
            $path = self::$_base_path.$path;
        }
        return $path;
    }

    public static function getPathToModule($module_name, $path = "") {

        $module_names = Zend_Controller_Front::getInstance()->getDispatcher()->getModuleDirectories();

        if(!in_array($module_name, $module_names)) {
            throw new Exception("Invalid module name");
        }

        if($path == "/") $path = "";

        return self::getPathTo("app/modules/$module_name/$path");

    }
    public static function getBasePathToModule($module_name, $path = "") {
        /** @todo cleanup migration inheritance */

        return self::getBasePathTo("app/sae/modules/$module_name/$path");
    }

    public static function getDesignPath($base = false, $path = null, $application_type = null) {

        $design_path = self::$_design_path;
        $design_codes = Zend_Registry::get("design_codes");
        if($application_type AND $application_type != APPLICATION_TYPE AND !empty($design_codes[$application_type])) {
            $design_path = str_replace("/".APPLICATION_TYPE."/", "/".$application_type."/", $design_path);
            $design_path = str_replace("/".DESIGN_CODE, "/".$design_codes[$application_type], $design_path);
        }
        if($path AND substr($path, 0, 1) != "/") $path = "/$path";
        $design_path = $base ? self::getBasePathTo($design_path.$path) : self::getPathTo($design_path.$path);

        return $design_path;
    }

    public static function getDesignsFor($application_type = "desktop") {

        $designs = array();
        $excluded_designs = array();
        $base_path = APPLICATION_PATH."/sae/design/".$application_type;
        if(!is_dir($base_path)) return $designs;

        switch($application_type) {
            case "desktop": $excluded_designs = array("backoffice", "installer", "debug"); break;
            default: break;
        }

        $directories = new DirectoryIterator($base_path);
        foreach($directories as $directory) {
            if($directory->isDir() AND !$directory->isDot() AND !in_array($directories->getFilename(), $excluded_designs)) {
                $designs[$directory->getFilename()] = ucfirst($directory->getFilename());
            }
        }

        return $designs;
    }

    public static function getSessionDirectory($base = false) {
        return $base ? self::getBasePathTo('/var/session') : self::getPathTo('/var/session');
    }

    public static function getTmpDirectory($base = false) {
        return $base ? self::getBasePathTo('/var/tmp') : self::getPathTo('/var/tmp');
    }

    public static function getCacheDirectory($base = false) {
        return $base ? self::getBasePathTo('/var/cache') : self::getPathTo('/var/cache');
    }

    public static function getImageCacheDirectory($base = false) {
        return $base ? self::getBasePathTo('/var/cache/images') : self::getPathTo('/var/cache/images');
    }

    public static function setPath($path = '') {
        self::$_path = $path;
    }

    public static function setBasePath($path = '') {
        self::$_base_path = $path;
    }

    /** @deprecated from 4.1.0 */
    public static function setDesignPath($path = '') {
        self::$_design_path = $path;
    }

    public static function delete($src) {
        # TG-196, protect eventual path with spaces
        exec("rm -Rf '{$src}'", $output);
    }

    public static function move($src, $dst) {

        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($src), RecursiveIteratorIterator::SELF_FIRST);

        foreach($files as $file) {
            if($file->isDir()) {
                if(!is_dir($dst."/".$file->getFileName())) {
                    mkdir($dst."/".$file->getFileName(), 0775, true);
                }
            } else {
                $basepath = $dst.str_replace($src, '', $file->getPath());
                if(!is_dir($basepath)) {
                    mkdir($basepath, 0775, true);
                }
                copy($file->getRealpath(), $basepath.'/'.$file->getFilename());
            }

        }

        self::delete($src);

    }

    public static function duplicate($src, $dst, $permission = 0777) {

        $is_mac_os = false;
        if(function_exists("posix_uname")) {
            $system = posix_uname();
            if($system AND isset($system["sysname"]) AND $system["sysname"] == "Darwin") {
                $is_mac_os = true;
            }
        }

        if($is_mac_os) {
            exec("cp -R \"$src/\"* \"$dst\"", $output);
        } else {
            exec("mkdir \"$dst\"", $output);
            exec("cp -rH \"$src/\"* \"$dst\"", $output);
        }

    }

    /**
     * @param $source
     * @param $destination
     * @return null
     */
    public static function zip($source, $destination) {

        if(!is_dir($source)) {
            return null;
        }

        /** Clean-up */
        if(file_exists($destination)) {
            unlink($destination);
        }

        /**
         * @todo try multiple with local zip libraries, then exec ... etc
         */
        exec("cd \"$source\"; zip --symlinks -r -9 \"$destination\" ./", $output);

        // Backward compatibility for Zip < 3.0
        if(empty($output) AND !is_file($destination)) {
            exec("cd \"$source\"; zip -r -y \"$destination\" ./", $output);
        }

        return is_file($destination) ? $destination : null;

    }

    /**
     * @param $archive
     * @param null $destination
     * @return null|string
     * @throws Exception
     */
    public static function unzip($archive, $destination = null) {
        if($destination === null) {
            $destination = Core_Model_Directory::getTmpDirectory(true)."/template/".uniqid();
        }

        if(!is_writable($destination) && !mkdir($destination, 0777, true)) {
            throw new Exception("#946-01: Unable to write to the given destination '{$destination}'.");
        }

        if(!file_exists($archive)) {
            throw new Exception("#946-02: The given path '{$archive}' is not readable.");
        }

        /**
         * @todo try multiple with local zip libraries, then exec ... etc
         */
        exec("unzip '$archive' -d '$destination'");

        return is_readable($destination) ? $destination : null;
    }

}