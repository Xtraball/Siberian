<?php
/**
 * An app without an uploaded icon: the source generators get a file to copy (the grey placeholder), not inline
 * image data. Before, the Android generator wrote no icon at all, kept the template launcher icon and pointed the
 * theme at @mipmap/ic_splash, which did not exist: the generated project did not build.
 */
$siberian = dirname(__DIR__, 3) . '/siberian';
$module = $siberian . '/app/sae/modules/Application';
$helperFile = $module . '/Model/Application/NoIcon.php';
$modelFile = $module . '/Model/Application/Abstract.php';
$placeholder = $siberian . '/images/application/placeholder/no-image.png';

if (is_file($helperFile) && !class_exists('Application_Model_Application_NoIcon', false)) {
    require_once $helperFile;
}

return [
    'no icon: the helper hands out a readable PNG copy of the placeholder' => function () use ($placeholder) {
        t_true(class_exists('Application_Model_Application_NoIcon', false), 'the helper exists');
        $dir = sys_get_temp_dir() . '/noicon-' . uniqid('', true);
        $file = Application_Model_Application_NoIcon::file($placeholder, $dir);

        t_true(is_file($file) && is_readable($file), 'a readable file');
        t_equal('image/png', mime_content_type($file), 'a PNG');
        t_equal(file_get_contents($placeholder), file_get_contents($file), 'the placeholder, unchanged');
        t_equal($file, Application_Model_Application_NoIcon::file($placeholder, $dir), 'the same file on the second call');
        t_equal([$file], glob($dir . '/*'), 'no temporary file left');

        unlink($file);
        rmdir($dir);
    },

    'no icon: a missing placeholder is an error, not a silent skip' => function () {
        $threw = false;
        try {
            Application_Model_Application_NoIcon::file('/nonexistent/no-image.png', sys_get_temp_dir());
        } catch (RuntimeException $e) {
            $threw = true;
        }
        t_true($threw, 'throws');
    },

    'no icon: getIcon and getAndroidSplashIcon return a file path when the generators ask for one' => function () use ($modelFile) {
        $model = file_get_contents($modelFile);
        foreach (['getAndroidSplashIcon', 'getIcon'] as $method) {
            $start = strpos($model, 'public function ' . $method . '(');
            $body = substr($model, $start, strpos($model, "\n    }\n", $start) - $start);
            t_true(strpos($body, 'self::noIcon($base)') !== false, $method . ' uses noIcon($base)');
            t_true(strpos($body, "inline('png'") === false, $method . ' no longer inlines the placeholder itself');
        }

        $start = strpos($model, 'protected static function noIcon(');
        t_true($start !== false, 'noIcon exists');
        $body = substr($model, $start, strpos($model, "\n    }\n", $start) - $start);
        t_true(strpos($body, 'Application_Model_Application_NoIcon::file(') !== false, 'a file when $base');
        t_true(strpos($body, "inline('png', 100)") !== false, 'inline data for the pages, as before');
    },
];
