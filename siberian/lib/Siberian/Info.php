<?php

namespace Siberian;

/**
 * Class \Siberian\Info
 */
class Info
{
    /**
     * @return array
     */
    public static function fetch (): array
    {
        $information = [
            'Platform name' => __get('platform_name'),
            'Installation version' => __get('installation_version'),
            'Main domain' => __get('main_domain'),
            'Current version - type' => Version::VERSION . ' - ' . Version::TYPE,
            'Environment' => __get('environment'),
            'PHP Version' => PHP_VERSION,
            'PHP memory_limit' => ini_get('memory_limit'),
            'PHP upload_max_filesize' => ini_get('upload_max_filesize'),
            'PHP max_execution_time' => ini_get('max_execution_time'),
            'PHP cURL HTTP/2 support' => \Push_Model_Certificate::testHttp2()['message'],
            'Update channel' => __get('update_channel'),
            'Panel type' => __get('cpanel_type'),
            'Let\'s encrypt env' => __get('letsencrypt_env'),
            'APK build type' => __get('apk_build_type'),
            'JAVA home' => __get('java_home'),
            'JAVA options' => __get('java_options'),
            'GRADLE options' => __get('gradle_options'),
        ];

        return $information;
    }

    /**
     *
     */
    public static function printLn ()
    {
        $information = self::fetch();

        $longestLabel = 0;
        $longestValue = 0;
        foreach ($information as $label => $value) {
            if (strlen($label) > $longestLabel) {
                $longestLabel = strlen($label);
            }
            if (strlen($value) > $longestValue) {
                $longestValue = strlen($value);
            }
        }

        $longest = $longestLabel + $longestValue + 4;

        echo "\n\n";
        echo str_pad("=", $longest + 1, "=", STR_PAD_LEFT);
        echo "\n";
        foreach ($information as $label => $value) {
            $lineLength = strlen($label) + strlen($value);
            $padding = abs($longest - $lineLength) * 1;
            echo color("{$label}:", "brown");
            echo str_pad($value, $padding + strlen($value), " ", STR_PAD_LEFT);
            echo "\n";
        }
        echo str_pad("=", $longest + 1, "=", STR_PAD_LEFT);
        echo "\n\n";
    }
}
