<?php

/**
 * @author Xtraball SAS <dev@xtraball.com>
 * @version 4.20.21
 */

use \Gettext\Translations;
use Siberian\File;

/**
 * Polyfill for php <= 7.2
 *
 */
if (!function_exists('is_countable')) {
    function is_countable($var)
    {
        return (is_array($var) || $var instanceof Countable);
    }
}

/**
 * Current Application Singleton
 *
 * @return \Application_Model_Application|null
 */
function app()
{
    return \Application_Model_Application::getSingleton();
}

/**
 * Logs all strings for extraction
 * If you want contextual translations to be automatically extracted
 * add `$_config["extract"] = true;` to your config.php file
 */

global $extractTranslations;
global $extractModules;
$extractModules = [];

/**
 * @param $original
 * @throws Zend_Exception
 */
function extract___($original)
{
    if (__getConfig("extract") === true) {
        global $extractTranslations;

        if (!is_array($extractTranslations)) {
            $extractTranslations = [];
        }

        // Special binding for modules
        $file = path("/var/tmp/orphans.po");

        if (!is_file($file)) {
            touch($file);
        }

        if (!array_key_exists($file, $extractTranslations)) {
            $extractTranslations[$file] = [];
        }

        // That's all for now!
        $extractTranslations[$file][] = [
            "flag" => null,
            "context" => null,
            "original" => $original,
            "translation" => $original,
        ];
    }
}

/**
 * @param $file
 * @param $module
 * @return false|string
 * @throws \Siberian\Exception
 */
function __dcRun($file, $module)
{
    return \Siberian\Cypher::dcRun($file, $module);
}

/**
 * @param $file
 * @param $module
 * @throws Zend_Exception
 * @throws \Siberian\Exception
 */
function __dcExec($file, $module)
{
    return \Siberian\Cypher::dcExec($file, $module);
}

/**
 * @param $context
 * @param $original
 * @param null $flag
 * @param bool $force forcing the extraction
 * @throws Zend_Exception
 */
function extract_p__($context, $original, $flag = null, $force = false)
{
    global $extractModules;
    if ((__getConfig('extract') === true) ||
        ($force === true)) {
        global $extractTranslations;
        global $forcedExtractTranslations;

        $forcedExtractTranslations = $force;

        if (!is_array($extractTranslations)) {
            $extractTranslations = [];
        }

        // Special binding for modules
        if (array_key_exists($context, $extractModules)) {
            if (array_key_exists("path", $extractModules[$context]) &&
                is_file($extractModules[$context]["path"])) {
                $file = $extractModules[$context]["path"];
            } else {
                $moduleFolder = $extractModules[$context]["module"];
                $file = path("/app/local/modules/{$moduleFolder}/resources/translations/default/{$context}.po");
            }
        } else {
            $file = path("/languages/base/c_{$context}.po");
        }

        if (!is_file($file)) {
            touch($file);
        }

        if (!array_key_exists($file, $extractTranslations)) {
            $extractTranslations[$file] = [];
        }

        $trimmed = trim($original);

        // That's all for now!
        if (!empty($trimmed)) {
            $extractTranslations[$file][] = [
                "flag" => $flag,
                "context" => $context,
                "original" => $original,
                "translation" => $original,
            ];
        }
    }
}

/**
 * Shutdown extracted translations, saving to file!
 *
 * @throws Zend_Exception
 */
function shutdown_extract_p()
{
    global $extractTranslations;
    global $forcedExtractTranslations;
    if ((__getConfig("extract") === true) ||
        ($forcedExtractTranslations === true)) {

        foreach ($extractTranslations as $file => $translations) {
            $poFile = Translations::fromPoFile($file);

            foreach ($translations as $translation) {
                /**
                 * @var $tmpTranslation \Gettext\Translation
                 */
                $tmpTranslation = $poFile->insert($translation["context"], $translation["original"]);
                $tmpTranslation->setTranslation($translation["original"]);

                // Find comments
                $comments = $tmpTranslation->getComments();
                $hasGMT = false;
                foreach ($comments as $comment) {
                    if (preg_match("/GMT/", $comment) === 1) {
                        $hasGMT = true;
                    }
                }

                if (!$hasGMT) {
                    $microTime = microtime(true);
                    usleep(50);
                    $tmpTranslation->addComment("GMT {$microTime}");
                }

                if ($translation["flag"] === "mobile") {
                    $tmpTranslation->addFlag("mobile");
                }
            }

            // We're done with this file!
            $poFile->toPoFile($file);
        }
    }
}

/**
 * @param $message
 */
function log_emerg($message)
{
    \Siberian\Utils::log_emerg($message);
}

/**
 * @param $message
 */
function log_alert($message)
{
    \Siberian\Utils::log_alert($message);
}

/**
 * @param $message
 */
function log_crit($message)
{
    \Siberian\Utils::log_crit($message);
}

/**
 * @param $message
 */
function log_err($message)
{
    \Siberian\Utils::log_err($message);
}

/**
 * @param $message
 */
function log_warn($message)
{
    \Siberian\Utils::log_warn($message);
}

/**
 * @param $message
 */
function log_notice($message)
{
    \Siberian\Utils::log_notice($message);
}

/**
 * @param $message
 */
function log_info($message)
{
    \Siberian\Utils::log_info($message);
}

/**
 * @param $message
 */
function log_debug($message)
{
    \Siberian\Utils::log_debug($message);
}

function log_exception(Exception $e)
{
    log_debug(sprintf("[Siberian_Exception] %s", $e->getMessage()));
    \Siberian\Debug::addException($e);
}

/**
 * @param $aclCode
 * @return bool
 */
function canAccess($aclCode)
{
    try {
        $aclList = \Admin_Controller_Default::_sGetAcl();
        if ($aclList) {
            return $aclList->isAllowed($aclCode);
        }
    } catch (\Exception $e) {
        // Do nothing!
    }

    return true;
}

/**
 * @param $path
 * @param bool $external
 * @return bool
 */
function is_image($path, $external = false)
{
    if ($external) {
        $tmp_image = file_get_contents($path);
        $tmp_path = \Core_Model_Directory::getBasePathTo("/var/tmp/" . uniqid());
        File::putContents($tmp_path, $tmp_image);
    } else {
        $tmp_path = $path;
    }

    $result = (@is_array(getimagesize($tmp_path)));

    if ($external) {
        unlink($tmp_path);
    }

    return $result;
}

/**
 * @param $files
 * @return array
 */
function normalizeFiles($files)
{
    $newFiles = [];

    foreach ($files as $keyName => $_files) {
        if (array_key_exists('name', $_files) &&
            is_array($_files['name'])) {
            $filesCount = count($_files['name']);
            for ($i = 0; $i < $filesCount; $i++) {
                $tmpFile = [
                    'name' => $_files['name'][$i],
                    'type' => $_files['type'][$i],
                    'tmp_name' => $_files['tmp_name'][$i],
                    'error' => $_files['error'][$i],
                    'size' => $_files['size'][$i],
                ];

                array_push($newFiles, $tmpFile);
            }
        } else if (array_key_exists('name', $_files) &&
            !is_array($_files['name'])) {

            array_push($newFiles, $_files);
        }
    }

    return $newFiles;
}

/**
 * Get the directory size
 *
 * @param $directory
 * @return integer
 */
function dirSize($directory)
{
    $size = 0;
    foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory)) as $file) {
        if (!in_array($file->getFilename(), ['.', '..'])) {
            $size += $file->getSize();
        }
    }
    return $size;
}

/**
 * Actual design setup.
 *
 * - siberian
 * - flat
 * - ...
 *
 * @return mixed
 */
function design_code()
{
    return 'flat';
}

/**
 * @return bool
 */
function isDev()
{
    return APPLICATION_ENV === 'development';
}

/**
 * @param $array
 * @param string $prefix
 * @return array
 */
function array_flat($array, $prefix = '')
{
    $result = [];

    foreach ($array as $key => $value) {
        $new_key = $prefix . (empty($prefix) ? '' : '.') . $key;

        if (is_array($value)) {
            $result = array_merge($result, array_flat($value, $new_key));
        } else {
            $result[$new_key] = $value;
        }
    }

    return $result;
}

/**
 * Cut a string to the desired maximum length
 *
 * @param $string
 * @param $length
 * @param string $suffix
 * @return string
 */
function cut($string, $length, $suffix = "...", $strip_tags = true)
{
    $string = strip_tags(trim($string));
    $str_length = mb_strlen($string, 'utf8');
    $suffix_length = mb_strlen($suffix, 'utf8');

    if ($str_length > $length) {
        $part1 = mb_substr($string, 0, $length - $suffix_length, 'utf8');

        $string = "{$part1}{$suffix}";
    }

    return $string;
}

/**
 * Make a string ellipsis with given max length
 *
 * @param $string
 * @param $length
 * @param string $ellipsis
 * @return string
 */
function ellipsis($string, $length, $ellipsis = "...")
{
    $string = trim($string);
    $str_length = mb_strlen($string, 'utf8');

    if ($str_length > $length) {
        $half = round($length / 2, PHP_ROUND_HALF_DOWN);
        $part1 = mb_substr($string, 0, $half, 'utf8');
        $part2 = mb_substr($string, $str_length - $half, $str_length, 'utf8');

        $string = "{$part1}{$ellipsis}{$part2}";
    }

    return $string;
}

/**
 * @param $bytes
 * @param int $precision
 * @return string
 */
function formatBytes($bytes, $precision = 2)
{
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];

    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);

    // Uncomment one of the following alternatives
    $bytes /= pow(1024, $pow);

    return round($bytes, $precision) . ' ' . $units[$pow];
}

/**
 * Simple alias for GDPR features
 *
 * @return bool
 */
function isGdpr()
{
    return \System_Model_Config::isGdprEnabled();
}

/**
 * @return mixed
 */
function __old()
{
    return call_user_func_array("__", func_get_args());
}

/**
 * @param $string
 * @return string
 */
function __title($string)
{
    $translation = call_user_func_array("__", func_get_args());

    return str_replace(["'", '"'], ["&acute;", "&quot;"], $translation);
}

/**
 * @param $string
 * @param string $escape
 * @return string
 */
function __js($string, $escape = '"')
{
    $args = func_get_args();

    # Remove $escape arg
    unset($args[1]);

    $translation = call_user_func_array("__", $args);

    return addcslashes($translation, $escape);
}

/**
 * Classic hook for translations
 *
 * @param $text
 * @return mixed|string
 */
function p__js($context, $string, $escape = '"')
{
    $args = func_get_args();

    # Remove $escape arg
    unset($args[2]);

    $translation = call_user_func_array("p__", $args);

    return addcslashes($translation, $escape);
}

/**
 * Alias: Force single quote escape
 *
 * @param $string
 * @return mixed|string
 */
function __jss($string)
{
    return __js($string, "'");
}

/**
 * Alias: Force double quote escape
 *
 * @param $string
 * @return mixed|string
 */
function __jsd($string)
{
    return __js($string, '"');
}

/**
 * @param string $url
 * @param array $params
 * @param null $locale
 * @return array|mixed|string
 */
function __url($url = '', array $params = [], $locale = null)
{
    return \Core_Model_Url::create($url, $params, $locale);
}

/**
 * @param string $url
 * @param array $params
 * @param null $locale
 * @return array|mixed|string
 */
function __path($url = '', array $params = [], $locale = null)
{
    return \Core_Model_Url::createPath($url, $params, $locale);
}

/**
 * @param string $relativePath
 * @return string
 */
function path($relativePath = '/')
{
    return \Core_Model_Directory::getBasePathTo($relativePath);
}

/**
 * @param string $relativePath
 * @return string
 */
function rpath($relativePath = "/")
{
    return \Core_Model_Directory::getPathTo($relativePath);
}

/**
 * @param bool $base
 * @return string
 */
function tmp($base = false)
{
    return $base ? \Core_Model_Directory::getBasePathTo('/var/tmp') :
        \Core_Model_Directory::getPathTo('/var/tmp');
}

/**
 * Removes all ./ and ../ from the path, because realpath is not OS safe!
 *
 * @param $path
 * @return array|string|string[]|null
 */
if (!function_exists('filter_path')) {
    function filter_path($path)
    {
        return preg_replace('/\.\//', '', $path);
    }
}


/**
 * @param $replacements
 * @param $file
 * @param bool $regex
 * @throws Exception
 */
function __replace($replacements, $file, $regex = false)
{

    $contents = file_get_contents($file);
    if (!$contents) {
        throw new Exception(__('An error occurred while editing file (%s).', $file));
    }

    foreach ($replacements as $search => $replace) {
        if ($regex) {
            $contents = preg_replace($search, $replace, $contents);
        } else {
            $contents = str_replace($search, $replace, $contents);
        }

    }

    File::putContents($file, $contents);
}

/**
 * @param $string
 * @return null|string|string[]
 */
function __ss($string)
{
    return preg_replace('~/+~', '/', $string);
}

/**
 * Short alias for Config getter
 *
 * @param $code
 * @return string
 */
function __get($code)
{
    return \System_Model_Config::getValueFor($code);
}

/**
 * @param $code
 * @param $value
 * @param null $label
 * @return $this|null
 */
function __set($code, $value, $label = null)
{
    return \System_Model_Config::setValueFor($code, $value, $label);
}

/**
 * @param $code
 * @return bool
 * @throws Zend_Exception
 */
function __getConfig($code)
{
    $_config = \Zend_Registry::get('_config');
    return $_config[$code] ?? false;
}

/**
 * @param $time
 * @param string $format
 * @return string
 * @throws Zend_Date_Exception
 */
function time_to_date($time, $format = 'y-MM-dd')
{
    $date = new \Zend_Date($time);
    return $date->toString($format);
}

function mysqlToTimestamp($mysql)
{

}

/**
 * @param $datetime
 * @param string $format
 * @param null $locale
 * @return string
 * @throws Zend_Date_Exception
 * @throws Zend_Locale_Exception
 */
function datetime_to_format($datetime, $format = \Zend_Date::DATETIME_SHORT, $locale = null)
{
    if (empty($datetime)) {
        return "-";
    }

    $language = null;
    if (\Core_Model_Language::getSession() &&
        \Core_Model_Language::getSession()->current_language) {

        $language = \Core_Model_Language::getSession()->current_language;
    }
    $locale = (is_null($locale)) ? new \Zend_Locale($language) : $locale;

    $date = (new \Zend_Date())
        ->set($datetime, "YYYY-MM-dd HH:mm:ss");

    return $date->toString($format, $locale);
}

/**
 * @param $hex
 * @param $steps
 * @return string
 */
function adjustBrightness($hex, $steps)
{
    // Steps should be between -255 and 255. Negative = darker, positive = lighter
    $steps = max(-255, min(255, $steps));

    // Normalize into a six character long hex string
    $hex = str_replace('#', '', $hex);
    if (strlen($hex) == 3) {
        $hex = str_repeat(substr($hex, 0, 1), 2) . str_repeat(substr($hex, 1, 1), 2) . str_repeat(substr($hex, 2, 1), 2);
    }

    // Split into three parts: R, G and B
    $color_parts = str_split($hex, 2);
    $return = '#';

    foreach ($color_parts as $color) {
        $color = hexdec($color); // Convert to decimal
        $color = max(0, min(255, $color + $steps)); // Adjust color
        $return .= str_pad(dechex($color), 2, '0', STR_PAD_LEFT); // Make two char hex code
    }

    return $return;
}

/**
 * Last try when a big php array with lot of data gets corrupted
 *
 * @param $array
 * @return mixed
 */
function data_to_utf8($array)
{
    array_walk_recursive($array, function (&$item, $key) {
        if (is_string($item) && !mb_detect_encoding($item, 'utf-8', true)) {
            $item = mb_convert_encoding($item, 'UTF-8');
        }
    });

    return $array;
}

/**
 * @param $value
 * @return mixed
 */
function escape_json_string($value)
{
    return str_replace(["\n", "\r", "\t"], ['<br />', '<br />', '    '], $value);
}

/**
 * @param $array
 * @return mixed
 */
function data_to_jsonsafe($array)
{
    array_walk_recursive($array, static function (&$item, $key) {
        $item = escape_json_string($item);
    });

    return $array;
}

/**
 * Converts a row set to a form options friendly array
 *
 * @param $row_set
 * @param $key_value
 * @param $key_label
 * @return array
 */
function rowset_to_options($row_set, $key_value, $key_label)
{
    $options = [];
    foreach ($row_set as $row) {
        $row->getId();

        if (is_callable($key_value)) {
            $key = $key_value($row);
        } else {
            $key = $row->getData($key_value);
        }

        if (is_callable($key_label)) {
            $label = $key_label($row);
        } else {
            $label = $row->getData($key_label);
        }

        $options[$key] = $label;
    }

    return $options;
}

/**
 * @param $text
 * @return mixed|string
 */
function slugify($text)
{
    $text = preg_replace('~[^\pL\d]+~u', '-', $text); # Replace non letter or digits by -
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text); # Transliterate
    $text = preg_replace('~[^-\w]+~', '', $text); #remove unwanted characters
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);

    if (empty($text)) {
        return 'n-a';
    }

    return $text;
}

/**
 * HTML Purifier using standard rules
 * http://htmlpurifier.org/download
 *
 * @param $string
 * @param $config
 * @return mixed
 * @throws Zend_Exception
 */
function purify($string, $config = null)
{
    /**
     * @var $htmlPurifier HTMLPurifier
     */
    $htmlPurifier = Zend_Registry::get('htmlPurifier');

    return $htmlPurifier->purify($string, $config);
}

/**
 * @param int $length
 * @param array $options
 * @return false|string
 */
function generate_strong_password($length = 9, $options = [])
{
    $options = array_merge([
        'uppercase' => true,
        'numeric' => true,
        'special' => true,
    ], $options);

    $set1 = str_split('abcdefghjkmnpqrstuvwxyz');
    $set2 = $options['uppercase'] ? str_split('ABCDEFGHJKMNPQRSTUVWXYZ') : $set1;
    $set3 = $options['numeric'] ? str_split('23456789') : $set1;
    $set4 = $options['special'] ? str_split('!@#$%&*?') : $set1;

    shuffle($set1);
    shuffle($set2);
    shuffle($set3);
    shuffle($set4);

    $gen = array_merge([], array_slice($set1, 0, 3), array_slice($set2, 0, 3), array_slice($set3, 0, 3), array_slice($set4, 0, 3));
    shuffle($gen);
    usleep(rand(1, 500));
    shuffle($gen);

    $password = join("", $gen);
    $current_length = strlen($password);
    $loop_breaker = 10;
    $counter = 0;
    while ($current_length < $length && $counter < $loop_breaker) {
        $password .= generate_strong_password(9, $options);
        $current_length = strlen($password);
        $counter++;
    }

    return substr($password, 0, $length);
}

/**
 * @param string $password
 * @param int $min_length
 * @return bool
 */
function strong_password(string $password, int $min_length = 9): bool
{
    $uppercase = preg_match('@[A-Z]@', $password);
    $lowercase = preg_match('@[a-z]@', $password);
    $number = preg_match('@[0-9]@', $password);
    $specialChars = preg_match('@[^\w]|_@', $password);

    if (!$uppercase || !$lowercase || !$number || !$specialChars || strlen($password) < $min_length) {
        return false;
    }
    return true;
}

/**
 * @param string $password
 * @return string
 */
function encrypt_password(string $password): string
{
    return sha1($password);
}

/**
 * @param $object
 * @param string $password
 * @param int $min_length
 * @param string $password_column
 * @return Core_Model_Default
 * @throws Zend_Exception
 */
function set_password_object($object, string $password, int $min_length = 9, $password_column = "password")
{
    if (!strong_password($password, $min_length)) {
        throw new Exception(p__("backoffice",
            "Password should be at least %s characters in length and should include at least one upper case letter, one number, and one special character.", $min_length));
    }

    $object->setData($password_column, encrypt_password($password));

    return $object;
}

/**
 * @param $path
 * @return string
 */
#[\Pure(true)]
function img_to_base64($path)
{
    if (!is_null($path) &&
        is_readable($path) &&
        in_array(pathinfo($path, PATHINFO_EXTENSION), ['png', 'jpeg', 'jpg', 'bmp', 'gif'])) {
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);
        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
    } else {
        /** Default placeholder */
        $base64 = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAUAAAADwCAIAAAD+Tyo8AAAACXBIWXMAAAsTAAALEwEAmpwYAAA5pGlUWHRYTUw6Y29tLmFkb2JlLnhtcAAAAAAAPD94cGFja2V0IGJlZ2luPSLvu78iIGlkPSJXNU0wTXBDZWhpSHpyZVN6TlRjemtjOWQiPz4KPHg6eG1wbWV0YSB4bWxuczp4PSJhZG9iZTpuczptZXRhLyIgeDp4bXB0az0iQWRvYmUgWE1QIENvcmUgNS42LWMwNjcgNzkuMTU3NzQ3LCAyMDE1LzAzLzMwLTIzOjQwOjQyICAgICAgICAiPgogICA8cmRmOlJERiB4bWxuczpyZGY9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkvMDIvMjItcmRmLXN5bnRheC1ucyMiPgogICAgICA8cmRmOkRlc2NyaXB0aW9uIHJkZjphYm91dD0iIgogICAgICAgICAgICB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8iCiAgICAgICAgICAgIHhtbG5zOnhtcE1NPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvbW0vIgogICAgICAgICAgICB4bWxuczpzdFJlZj0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL3NUeXBlL1Jlc291cmNlUmVmIyIKICAgICAgICAgICAgeG1sbnM6c3RFdnQ9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZUV2ZW50IyIKICAgICAgICAgICAgeG1sbnM6ZGM9Imh0dHA6Ly9wdXJsLm9yZy9kYy9lbGVtZW50cy8xLjEvIgogICAgICAgICAgICB4bWxuczpwaG90b3Nob3A9Imh0dHA6Ly9ucy5hZG9iZS5jb20vcGhvdG9zaG9wLzEuMC8iCiAgICAgICAgICAgIHhtbG5zOnRpZmY9Imh0dHA6Ly9ucy5hZG9iZS5jb20vdGlmZi8xLjAvIgogICAgICAgICAgICB4bWxuczpleGlmPSJodHRwOi8vbnMuYWRvYmUuY29tL2V4aWYvMS4wLyI+CiAgICAgICAgIDx4bXA6Q3JlYXRvclRvb2w+QWRvYmUgUGhvdG9zaG9wIENDIDIwMTUgKE1hY2ludG9zaCk8L3htcDpDcmVhdG9yVG9vbD4KICAgICAgICAgPHhtcDpDcmVhdGVEYXRlPjIwMTYtMTAtMDRUMTY6MjI6MjUrMDI6MDA8L3htcDpDcmVhdGVEYXRlPgogICAgICAgICA8eG1wOk1vZGlmeURhdGU+MjAxNi0xMC0wNFQxNjoyMzo0NSswMjowMDwveG1wOk1vZGlmeURhdGU+CiAgICAgICAgIDx4bXA6TWV0YWRhdGFEYXRlPjIwMTYtMTAtMDRUMTY6MjM6NDUrMDI6MDA8L3htcDpNZXRhZGF0YURhdGU+CiAgICAgICAgIDx4bXBNTTpJbnN0YW5jZUlEPnhtcC5paWQ6OTU2Njk3MzMtODA0Yy00MTEwLTk1YzgtYjVjNGRlNTBkNTQ3PC94bXBNTTpJbnN0YW5jZUlEPgogICAgICAgICA8eG1wTU06RG9jdW1lbnRJRD54bXAuZGlkOkUwMUNFMDVFRTk0MDExRTM4MkEwODdBMzI4REI1M0I3PC94bXBNTTpEb2N1bWVudElEPgogICAgICAgICA8eG1wTU06RGVyaXZlZEZyb20gcmRmOnBhcnNlVHlwZT0iUmVzb3VyY2UiPgogICAgICAgICAgICA8c3RSZWY6aW5zdGFuY2VJRD54bXAuaWlkOjg3MjYyMzlBRThBQjExRTM4MkEwODdBMzI4REI1M0I3PC9zdFJlZjppbnN0YW5jZUlEPgogICAgICAgICAgICA8c3RSZWY6ZG9jdW1lbnRJRD54bXAuZGlkOjg3MjYyMzlCRThBQjExRTM4MkEwODdBMzI4REI1M0I3PC9zdFJlZjpkb2N1bWVudElEPgogICAgICAgICA8L3htcE1NOkRlcml2ZWRGcm9tPgogICAgICAgICA8eG1wTU06T3JpZ2luYWxEb2N1bWVudElEPnhtcC5kaWQ6RTAxQ0UwNUVFOTQwMTFFMzgyQTA4N0EzMjhEQjUzQjc8L3htcE1NOk9yaWdpbmFsRG9jdW1lbnRJRD4KICAgICAgICAgPHhtcE1NOkhpc3Rvcnk+CiAgICAgICAgICAgIDxyZGY6U2VxPgogICAgICAgICAgICAgICA8cmRmOmxpIHJkZjpwYXJzZVR5cGU9IlJlc291cmNlIj4KICAgICAgICAgICAgICAgICAgPHN0RXZ0OmFjdGlvbj5zYXZlZDwvc3RFdnQ6YWN0aW9uPgogICAgICAgICAgICAgICAgICA8c3RFdnQ6aW5zdGFuY2VJRD54bXAuaWlkOjk1NjY5NzMzLTgwNGMtNDExMC05NWM4LWI1YzRkZTUwZDU0Nzwvc3RFdnQ6aW5zdGFuY2VJRD4KICAgICAgICAgICAgICAgICAgPHN0RXZ0OndoZW4+MjAxNi0xMC0wNFQxNjoyMzo0NSswMjowMDwvc3RFdnQ6d2hlbj4KICAgICAgICAgICAgICAgICAgPHN0RXZ0OnNvZnR3YXJlQWdlbnQ+QWRvYmUgUGhvdG9zaG9wIENDIDIwMTUgKE1hY2ludG9zaCk8L3N0RXZ0OnNvZnR3YXJlQWdlbnQ+CiAgICAgICAgICAgICAgICAgIDxzdEV2dDpjaGFuZ2VkPi88L3N0RXZ0OmNoYW5nZWQ+CiAgICAgICAgICAgICAgIDwvcmRmOmxpPgogICAgICAgICAgICA8L3JkZjpTZXE+CiAgICAgICAgIDwveG1wTU06SGlzdG9yeT4KICAgICAgICAgPGRjOmZvcm1hdD5pbWFnZS9wbmc8L2RjOmZvcm1hdD4KICAgICAgICAgPHBob3Rvc2hvcDpDb2xvck1vZGU+MzwvcGhvdG9zaG9wOkNvbG9yTW9kZT4KICAgICAgICAgPHRpZmY6T3JpZW50YXRpb24+MTwvdGlmZjpPcmllbnRhdGlvbj4KICAgICAgICAgPHRpZmY6WFJlc29sdXRpb24+NzIwMDAwLzEwMDAwPC90aWZmOlhSZXNvbHV0aW9uPgogICAgICAgICA8dGlmZjpZUmVzb2x1dGlvbj43MjAwMDAvMTAwMDA8L3RpZmY6WVJlc29sdXRpb24+CiAgICAgICAgIDx0aWZmOlJlc29sdXRpb25Vbml0PjI8L3RpZmY6UmVzb2x1dGlvblVuaXQ+CiAgICAgICAgIDxleGlmOkNvbG9yU3BhY2U+NjU1MzU8L2V4aWY6Q29sb3JTcGFjZT4KICAgICAgICAgPGV4aWY6UGl4ZWxYRGltZW5zaW9uPjMyMDwvZXhpZjpQaXhlbFhEaW1lbnNpb24+CiAgICAgICAgIDxleGlmOlBpeGVsWURpbWVuc2lvbj4yNDA8L2V4aWY6UGl4ZWxZRGltZW5zaW9uPgogICAgICA8L3JkZjpEZXNjcmlwdGlvbj4KICAgPC9yZGY6UkRGPgo8L3g6eG1wbWV0YT4KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAKPD94cGFja2V0IGVuZD0idyI/Pucy8fwAAAAgY0hSTQAAeiUAAICDAAD5/wAAgOkAAHUwAADqYAAAOpgAABdvkl/FRgAACJ5JREFUeNrs3W1709YdwGEdyZLlJ9rEZoSUlhbotu//ebZdF4GuFCghDcHWw5H2wmvatcDcsYTYvu+3vMCW8rvOX9ZTODt9nQDbKbUJQMCAgAEBg4ABAQMCBgQMAgYEDAgYBAwIGBAwIGAQMCBgQMCAgEHAgIABAYOAAQEDAgYEDAIGBAwIGBAwCBgQMCBgEDAgYEDAgIBBwICAAQGDgAEBAwIGBAwCBgQMCBgQMAgYEDAgYBAwIGBAwICAQcCAgAEBAwIGAQMCBgQMAgYEDAgYEDAIGBAwIGBAwCBgQMCAgEHAgIABAQMCBgEDAgYEDAIGBAwIGNjUwCa4BiGEPfzWfd/b9QLeBVmWhRD25w96/WXbtrXrBbz16dZ1/eTpkxhjmmZ78q27LmZZdnz3uCiKGKM/AwFvqzRNu647ff26aZrBYF+2dtu2eZ4f3TlK01TAAt7u48AQQlEUaZpm2b6swFmWDQaDvTpq+GQrhE0AVmD+gKZpuq7bpZ+m+75P0zTPcztXwLs/UX9xfJznedd1uzMzp1lV1y9evlh/QTtawLsc8GK+mIwnTdvszPcq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';
    }

    return $base64;
}

/**
 * @param $base64
 * @param $path
 * @return mixed
 * @throws Exception
 */
function base64imageToFile($base64, $path)
{
    try {
        $parts = explode(',', $base64);
        $specs = $parts[0];
        $image = $parts[1];
        $specParts = explode(';', $specs);
        switch ($specParts[0]) {
            case 'data:image/png':
                $type = 'png';
                break;
            case 'data:image/jpeg':
            case 'data:image/jpg':
                $type = 'jpg';
                break;
            default:
                throw new \Exception('Unsupported file format, only png or jpg are allowed!');
        }

        // Save image to file
        $imagePath = sprintf('%s.%s', $path, $type);
        File::putContents($imagePath, base64_decode($image));

    } catch (Exception $e) {
        throw $e;
    }

    return $imagePath;
}