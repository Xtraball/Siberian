<?php

/**
 * Class Application_Model_Device_Ionic_Android_Splash
 *
 * The Android startup screen (androidx SplashScreen) shows the icon named by `windowSplashScreenAnimatedIcon` of
 * res/values/themes.xml. It used to be the 288px app icon (@mipmap/ic_launcher), which the system enlarges to about
 * 240dp and made the splash blurry. It is now the Splash icon (Android) of the app (@mipmap/ic_splash, generated at
 * 512px in every build, from the app icon when no Splash icon was uploaded).
 */
class Application_Model_Device_Ionic_Android_Splash
{
    const DEFAULT_ICON = '@mipmap/ic_launcher';

    const SPLASH_ICON = '@mipmap/ic_splash';

    /**
     * @return array search => replace for res/values/themes.xml
     */
    public static function themeReplacements(): array
    {
        return [
            self::DEFAULT_ICON => self::SPLASH_ICON,
        ];
    }
}
