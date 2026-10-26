<?php
/**
 * Android startup screen: the system splash shows the Splash icon (Android) of the app, 512px, and the app icon
 * when no Splash icon was uploaded. It used to show the 288px app icon, enlarged by the system and blurry.
 */
$siberian = dirname(__DIR__, 3) . '/siberian';
$module = $siberian . '/app/sae/modules/Application';
$helperFile = $module . '/Model/Device/Ionic/Android/Splash.php';
$generatorFile = $module . '/Model/Device/Ionic/Android/Abstract.php';
$modelFile = $module . '/Model/Application/Abstract.php';
$themesFile = $siberian . '/var/apps/ionic/android/app/src/main/res/values/themes.xml';

if (!class_exists('Application_Model_Device_Ionic_Android_Splash', false)) {
    require_once $helperFile;
}

return [
    'splash icon: the theme points at the splash icon and nothing else changes' => function () use ($themesFile) {
        $replace = ['Application_Model_Device_Ionic_Android_Splash', 'themeReplacements'];
        t_equal(['@mipmap/ic_launcher' => '@mipmap/ic_splash'], $replace(), 'swap');

        $theme = file_get_contents($themesFile);
        t_equal(1, substr_count($theme, '@mipmap/ic_launcher'), 'the anchor is in the template once');
        t_true(strpos($theme, '<item name="windowSplashScreenAnimatedIcon">@mipmap/ic_launcher</item>') !== false, 'it is the animated icon');
        $after = strtr($theme, $replace());
        t_true(strpos($after, '<item name="windowSplashScreenAnimatedIcon">@mipmap/ic_splash</item>') !== false, 'swapped');
        t_equal(strtr($theme, ['@mipmap/ic_launcher' => '@mipmap/ic_splash']), $after, 'nothing else changed');
    },

    'splash icon: the generator applies it to every build, after the splash colour' => function () use ($generatorFile) {
        $code = file_get_contents($generatorFile);
        t_equal(1, substr_count($code, 'Application_Model_Device_Ionic_Android_Splash::themeReplacements()'), 'one call');
        t_true(strpos($code, '$splashThemeReplacements') === false, 'no leftover condition');
        t_true(strpos($code, "'#FFFFFF' => \$splash_color,") < strpos($code, 'Android_Splash::themeReplacements()'), 'after the splash colour');
        t_true(strpos($code, 'getUseAndroidSplashIcon') === false, 'no option left');
    },

    'splash icon: ic_splash is generated at 512px and falls back on the app icon' => function () use ($generatorFile, $modelFile) {
        $code = file_get_contents($generatorFile);
        t_true(strpos($code, "'/mipmap-xxxhdpi/ic_splash.png' => \$icSplashAndroid") !== false, 'written to mipmap-xxxhdpi');
        t_true(strpos($code, "'/mipmap-xxxhdpi/ic_launcher.png' => \$icNew") !== false, 'ic_launcher still written');

        $model = file_get_contents($modelFile);
        $start = strpos($model, 'public function getAndroidSplashIcon');
        $method = substr($model, $start, strpos($model, "return \$newIcon->getUrl", $start) - $start);
        t_true(strpos($method, "if (empty(\$androidSplashIcon)) {\n            \$androidSplashIcon = \$icon;") !== false, 'no splash icon: the app icon');
        t_true(strpos($method, '$size = 512;') !== false, '512px');
    },

    'splash icon: no leftover option (column, form, save, version)' => function () use ($module) {
        $schema = file_get_contents($module . '/resources/db/schema/application.php');
        t_true(strpos($schema, 'use_android_splash_icon') === false, 'no column');
        t_true(strpos(file_get_contents($module . '/Form/Android.php'), 'use_android_splash_icon') === false, 'no checkbox');
        t_true(strpos(file_get_contents($module . '/controllers/Customization/Publication/InfosController.php'), 'UseAndroidSplashIcon') === false, 'no save');
    },
];
