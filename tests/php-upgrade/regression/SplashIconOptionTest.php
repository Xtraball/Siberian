<?php
/**
 * Android startup screen: option `use_android_splash_icon` (off by default) makes the system splash show the app's
 * Splash icon (Android, 512px) instead of the 288px app icon, which the system enlarges and can look blurry.
 * Nothing changes for apps that do not tick it.
 */
$siberian = dirname(__DIR__, 3) . '/siberian';
$module = $siberian . '/app/sae/modules/Application';
$helperFile = $module . '/Model/Device/Ionic/Android/Splash.php';
$generatorFile = $module . '/Model/Device/Ionic/Android/Abstract.php';
$themesFile = $siberian . '/var/apps/ionic/android/app/src/main/res/values/themes.xml';
$poFile = $siberian . '/languages/base/c_application.po';

if (!class_exists('Application_Model_Device_Ionic_Android_Splash', false)) {
    require_once $helperFile;
}

return [
    'splash icon: off by default, nothing is replaced' => function () {
        $replace = ['Application_Model_Device_Ionic_Android_Splash', 'themeReplacements'];
        foreach ([null, false, 0, '0', '', 'false', 'no'] as $value) {
            t_equal([], $replace($value), 'off for ' . var_export($value, true));
        }
    },

    'splash icon: on swaps only the animated icon of the theme' => function () use ($themesFile) {
        $replace = ['Application_Model_Device_Ionic_Android_Splash', 'themeReplacements'];
        foreach ([true, 1, '1', 'true'] as $value) {
            t_equal(['@mipmap/ic_launcher' => '@mipmap/ic_splash'], $replace($value), 'on for ' . var_export($value, true));
        }

        $theme = file_get_contents($themesFile);
        t_equal(1, substr_count($theme, '@mipmap/ic_launcher'), 'the anchor is in the template once');
        t_true(strpos($theme, '<item name="windowSplashScreenAnimatedIcon">@mipmap/ic_launcher</item>') !== false, 'it is the animated icon');
        $after = strtr($theme, $replace(true));
        t_true(strpos($after, '<item name="windowSplashScreenAnimatedIcon">@mipmap/ic_splash</item>') !== false, 'swapped');
        t_equal(strtr($theme, ['@mipmap/ic_launcher' => '@mipmap/ic_splash']), $after, 'nothing else changed');
    },

    'splash icon: the icon it points to is generated for every build' => function () use ($generatorFile) {
        $code = file_get_contents($generatorFile);
        t_true(strpos($code, "'/mipmap-xxxhdpi/ic_splash.png' => \$icSplashAndroid") !== false, 'ic_splash is written to mipmap-xxxhdpi');
        t_true(strpos($code, "'/mipmap-xxxhdpi/ic_launcher.png' => \$icNew") !== false, 'ic_launcher is still written');
    },

    'splash icon: the generator only uses it through the option' => function () use ($generatorFile) {
        $code = file_get_contents($generatorFile);
        t_equal(1, substr_count($code, 'Application_Model_Device_Ionic_Android_Splash::themeReplacements($application->getUseAndroidSplashIcon())'), 'one call, from the option');
        t_true(strpos($code, 'if (!empty($splashThemeReplacements)) {') !== false, 'guarded');
    },

    'splash icon: schema column, form and save follow the other Android options' => function () use ($module) {
        $schema = file_get_contents($module . '/resources/db/schema/application.php');
        t_true((bool) preg_match("/'use_android_splash_icon' => \\[\\s*'type' => 'tinyint\\(1\\)',\\s*'default' => '0',/", $schema), 'column, default 0');
        $form = file_get_contents($module . '/Form/Android.php');
        t_true(strpos($form, 'addSimpleCheckbox("use_android_splash_icon"') !== false, 'checkbox');
        t_true(strpos($form, '->getElement("use_android_splash_icon")') !== false, 'filled from the app');
        $controller = file_get_contents($module . '/controllers/Customization/Publication/InfosController.php');
        t_true(strpos($controller, "->setUseAndroidSplashIcon(filter_var(\$params['use_android_splash_icon'] ?? false, FILTER_VALIDATE_BOOLEAN))") !== false, 'saved, missing means off');
        $package = json_decode(file_get_contents($module . '/package.json'), true);
        t_true(version_compare($package['version'], '5.0.16', '>='), 'module version bumped so the schema is applied');
    },

    'splash icon: the new texts are in the translation catalog (English is the default)' => function () use ($module, $poFile) {
        $form = file_get_contents($module . '/Form/Android.php');
        t_true((bool) preg_match_all('/p__\\("application", "((?:[^"\\\\]|\\\\.)*(?:splash|Splash)(?:[^"\\\\]|\\\\.)*)"\\)/', $form, $m), 'texts found');
        t_equal(2, count($m[1]), 'two texts');
        $po = file_get_contents($poFile);
        foreach ($m[1] as $text) {
            $entry = 'msgctxt "application"' . "\n" . 'msgid "' . $text . '"' . "\n" . 'msgstr "' . $text . '"';
            t_true(strpos($po, $entry) !== false, 'catalog has: ' . $text);
        }
    },
];
