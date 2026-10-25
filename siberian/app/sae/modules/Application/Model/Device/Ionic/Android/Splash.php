<?php

/**
 * Class Application_Model_Device_Ionic_Android_Splash
 *
 * The Android startup screen (androidx SplashScreen) shows the icon named by `windowSplashScreenAnimatedIcon` of
 * res/values/themes.xml. By default it is the 288px app icon (@mipmap/ic_launcher), which the system enlarges to
 * about 240dp and can look blurry. With the option `use_android_splash_icon` it is the Splash icon (Android) of the
 * app (@mipmap/ic_splash, generated at 512px in every build).
 */
class Application_Model_Device_Ionic_Android_Splash
{
    const DEFAULT_ICON = '@mipmap/ic_launcher';

    const SPLASH_ICON = '@mipmap/ic_splash';

    /**
     * @param mixed $enabled The value of the option, as saved
     * @return array search => replace for res/values/themes.xml, empty (nothing changes) unless the option is on
     */
    public static function themeReplacements($enabled): array
    {
        if (!filter_var($enabled, FILTER_VALIDATE_BOOLEAN)) {
            return [];
        }

        return [
            self::DEFAULT_ICON => self::SPLASH_ICON,
        ];
    }
}
