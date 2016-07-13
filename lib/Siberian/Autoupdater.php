<?php

/**
 * Class Siberian_Autoupdater
 *
 * @version 4.1.0
 *
 */

class Siberian_Autoupdater
{
    public static function configure($host) {
        $mobileIos = new Application_Model_Device_Ionic_Ios();
        $mobileIos->configureAutoupdater($host);

        $mobileAndroid = new Application_Model_Device_Ionic_Android();
        $mobileAndroid->configureAutoupdater($host);
    }
}
