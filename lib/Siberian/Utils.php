<?php
/**
 * Class Siberian_Utils
 *
 * @version 4.2.0
 *
 * @author Xtraball <dev@wtraball.com>
 */

class Siberian_Utils {
	/** utility class */

	public static function load() {
		define("SAE", 	100);
		define("MAE", 	200);
		define("PE", 	300);
		define("DEMO", 	400);
	}
}

/**
 * Class Utils
 *
 * Short named class for Siberian_Utils
 *
 */
class Utils extends Siberian_Utils {}

/**
 * Current Application Singleton
 *
 * @return Application_Model_Application|null
 */
function app() {
    return Application_Model_Application::getSingleton();
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
function design_code() {
	return System_Model_Config::getValueFor("editor_design");
}

/**
 * Cut a string to the desired maximum length
 *
 * @param $string
 * @param $length
 * @param string $suffix
 * @return string
 */
function cut($string, $length, $suffix = "...", $strip_tags = true) {
	$string = strip_tags(trim($string));
	$str_length = mb_strlen($string, 'utf8');
	$suffix_length = mb_strlen($suffix, 'utf8');

	if($str_length > $length) {
		$part1 = mb_substr($string, 0, $length-$suffix_length, 'utf8');

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
function ellipsis($string, $length, $ellipsis = "...") {
	$string = trim($string);
	$str_length = mb_strlen($string, 'utf8');

	if($str_length > $length) {
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
function formatBytes($bytes, $precision = 2) {
	$units = array('B', 'KB', 'MB', 'GB', 'TB');

	$bytes = max($bytes, 0);
	$pow = floor(($bytes ? log($bytes) : 0) / log(1024));
	$pow = min($pow, count($units) - 1);

	return round($bytes, $precision) . ' ' . $units[$pow];
}

/**
 * Classic hook for translations
 *
 * @param $text
 * @return mixed|string
 */
function __($string) {
	$args = func_get_args();

	return Core_Model_Translator::translate($string, $args);
}

/**
 * Classic hook for translations
 *
 * @param $text
 * @return mixed|string
 */
function __js($string, $escape = '"') {
	$args = func_get_args();

	# Remove default args
	unset($args[0]);
	unset($args[1]);

	return addcslashes(Core_Model_Translator::translate($string, $args), $escape);
}

/**
 * @param string $url
 * @param array $params
 * @param null $locale
 * @return array|mixed|string
 */
function __url($url = "", array $params = array(), $locale = null) {
	return Core_Model_Url::create($url, $params, $locale);
}

/**
 * @param string $url
 * @param array $params
 * @param null $locale
 * @return array|mixed|string
 */
function __path($url = "", array $params = array(), $locale = null) {
	return Core_Model_Url::createPath($url, $params, $locale);
}

/**
 * @param $replacements
 * @param $file
 * @param bool $regex
 * @throws Exception
 */
function __replace($replacements, $file, $regex = false) {

	$contents = file_get_contents($file);
	if(!$contents) {
		throw new Exception(__("An error occurred while editing file (%s).", $file));
	}

	foreach($replacements as $search => $replace) {
		if($regex) {
			$contents = preg_replace($search, $replace, $contents);
		} else {
			$contents = str_replace($search, $replace, $contents);
		}

	}

	file_put_contents($file, $contents);
}

/**
 * Strip multiple slashes into one.
 *
 * @param $string
 */
function __ss($string) {
	return preg_replace('~/+~', '/', $string);
}

function time_to_date($time, $format = 'y-MM-dd') {
	$date = new Zend_Date($time);
	return $date->toString($format);
}

/**
 * Last try when a big php array with lot of data gets corrupted
 *
 * @param $array
 * @return mixed
 */
function data_to_utf8($array) {
	array_walk_recursive($array, function(&$item, $key){
		if(is_string($item) && !mb_detect_encoding($item, 'utf-8', true)){
			$item = mb_convert_encoding($item, 'UTF-8');
		}
	});

	return $array;
}