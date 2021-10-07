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
        class_alias(Error::class, 'Siberian_Error');
        class_alias(Utils::class, 'Utils');
        class_alias(Utils::class, 'Siberian_Utils');
        class_alias(Cron::class, 'Siberian_Cron');
        class_alias(Exporter::class, 'Siberian_Exporter');
        class_alias(\Core\Model\Base::class, 'Core_Model_Default');
        class_alias(Exception::class, 'Siberian_Exception');
        class_alias(Version::class, 'Siberian_Version');
        class_alias(Cache::class, 'Siberian_Cache');
        class_alias(Cache\Apps::class, 'Siberian_Cache_Apps');
        class_alias(Cache\Design::class, 'Siberian_Cache_Design');
        class_alias(Cache\Translation::class, 'Siberian_Cache_Translation');
        class_alias(Cache\CacheInterface::class, 'Siberian_Cache_Interface');
        class_alias(Api::class, 'Siberian_Api');
        class_alias(Assets::class, 'Siberian_Assets');
        class_alias(Autoupdater::class, 'Siberian_Autoupdater');
        class_alias(Json::class, 'Siberian_Json');
        class_alias(Image::class, 'Siberian_Image');
        class_alias(Media::class, 'Siberian_Media');
        class_alias(Minify::class, 'Siberian_Minify');
        class_alias(Feature::class, 'Siberian_Feature');
        class_alias(Scss::class, 'Siberian_Scss');
        class_alias(Yaml::class, 'Siberian_Yaml');
        class_alias(Color::class, 'Siberian_Color');
        class_alias(Currency::class, 'Siberian_Currency');
        class_alias(Date::class, 'Siberian_Date');
        class_alias(Debug::class, 'Siberian_Debug');
        class_alias(Service::class, 'Siberian_Service');
        class_alias(Wrapper\Sqlite::class, 'Siberian_Wrapper_Sqlite');
        class_alias(Wrapper\SqliteException::class, 'Siberian_Wrapper_Sqlite_Exception');
        class_alias(Cpanel::class, 'Siberian_Cpanel');
        class_alias(Cpanel\Api::class, 'Siberian_Cpanel_Api');
        class_alias(ZebraImage::class, 'Siberian_ZebraImage');
        class_alias(View::class, 'Siberian_View');
        class_alias(VestaCP::class, 'Siberian_VestaCP');
        class_alias(VestaCP\Api::class, 'Siberian_VestaCP_Api');
        class_alias(VestaCP\Client::class, 'Siberian_VestaCP_Client');
        class_alias(Exec::class, 'Siberian_Exec');
        class_alias(Log::class, 'Siberian_Log');
        class_alias(Request::class, 'Siberian_Request');
        class_alias(Mail::class, 'Siberian_Mail');
        class_alias(Session::class, 'Siberian_Session');
        class_alias(Resource::class, 'Siberian_Resource');
        class_alias(Privacy::class, 'Siberian_Privacy');
        class_alias(\Siberian_Layout::class, '\Siberian\Layout');
        class_alias(Layout\Email::class, 'Siberian_Layout_Email');
    }
}


