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
        class_alias("\Siberian\Error", "Siberian_Error");
        class_alias("\Siberian\Utils", "Utils");
        class_alias("\Siberian\Utils", "Siberian_Utils");
        class_alias("\Siberian\Cron", "Siberian_Cron");
        class_alias("\Siberian\Exporter", "Siberian_Exporter");
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
        class_alias("\Siberian\Image", "Siberian_Image");
        class_alias("\Siberian\Minify", "Siberian_Minify");
        class_alias("\Siberian\Feature", "Siberian_Feature");
        class_alias("\Siberian\Scss", "Siberian_Scss");
        class_alias("\Siberian\Yaml", "Siberian_Yaml");
        class_alias("\Siberian\Color", "Siberian_Color");
        class_alias("\Siberian\Currency", "Siberian_Currency");
        class_alias("\Siberian\Date", "Siberian_Date");
        class_alias("\Siberian\Debug", "Siberian_Debug");
        class_alias("\Siberian\Service", "Siberian_Service");
        class_alias("\Siberian\Wrapper\Sqlite", "Siberian_Wrapper_Sqlite");
        class_alias("\Siberian\Wrapper\SqliteException", "Siberian_Wrapper_Sqlite_Exception");
        class_alias("\Siberian\Cpanel", "Siberian_Cpanel");
        class_alias("\Siberian\Cpanel\Api", "Siberian_Cpanel_Api");
        class_alias("\Siberian\ZebraImage", "Siberian_ZebraImage");
        class_alias("\Siberian\View", "Siberian_View");
        class_alias("\Siberian\VestaCP", "Siberian_VestaCP");
        class_alias("\Siberian\VestaCP\Api", "Siberian_VestaCP_Api");
        class_alias("\Siberian\VestaCP\Client", "Siberian_VestaCP_Client");
        class_alias("\Siberian\Exec", "Siberian_Exec");
        class_alias("\Siberian\Log", "Siberian_Log");
        class_alias("\Siberian\Request", "Siberian_Request");
        class_alias("\Siberian\Mail", "Siberian_Mail");
        class_alias("\Siberian\Session", "Siberian_Session");
        class_alias("\Siberian\Resource", "Siberian_Resource");
        class_alias("\Siberian\Privacy", "Siberian_Privacy");
        class_alias("Siberian_Layout", "\Siberian\Layout");
        class_alias("\Siberian\Layout\Email", "Siberian_Layout_Email");
    }
}


