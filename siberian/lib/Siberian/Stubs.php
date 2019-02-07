<?php

namespace Siberian;

/**
 * A collection of stubs, extents & aliases for backward compatibility with "un-namespaced" classes!
 */
class Stubs {
    // Empty class

    public function __construct()
    {
        // Empty one!
    }

    /**
     * Stubs loader!
     */
    public static function loadAliases ()
    {
        class_alias("\Siberian\Utils", "Utils");
        class_alias("\Siberian\Utils", "Siberian_Utils");
        class_alias("\Siberian\Cron", "Siberian_Cron");
        class_alias("\Core\Model\Base", "Core_Model_Default");
        class_alias("\Siberian\Exception", "Siberian_Exception");
        class_alias("\Siberian\Version", "Siberian_Version");
        class_alias("\Siberian\Cache", "Siberian_Cache");
        class_alias("\Siberian\Cache\Apps", "Siberian_Cache_Apps");
        class_alias("\Siberian\Cache\Design", "Siberian_Cache_Design");
        class_alias("\Siberian\Cache\Translation", "Siberian_Cache_Translation");
        class_alias("\Siberian\Cache\CacheInterface", "Siberian_Cache_Interface");
        class_alias("\Siberian\Api", "Siberian_Api");
        class_alias("\Siberian\Assets", "Siberian_Assets");
        class_alias("\Siberian\Autoupdater", "Siberian_Autoupdater");
        class_alias("\Siberian\Json", "Siberian_Json");
        class_alias("\Siberian\Minify", "Siberian_Minify");
        class_alias("\Siberian\Feature", "Siberian_Feature");
        class_alias("\Siberian\Scss", "Siberian_Scss");
        class_alias("\Siberian\Yaml", "Siberian_Yaml");
        class_alias("\Siberian\Color", "Siberian_Color");
        class_alias("\Siberian\Currency", "Siberian_Currency");
        class_alias("\Siberian\Date", "Siberian_Date");
        class_alias("\Siberian\Debug", "Siberian_Debug");
    }
}


