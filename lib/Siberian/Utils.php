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
function cut($string, $length, $suffix = "...") {
	$string = trim($string);
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
 * Classic hook for translations
 *
 * @param $text
 * @return mixed|string
 */
function __($string) {
	$args = func_get_args();

	return Core_Model_Translator::translate($string, $args);
}

function time_to_date($time, $format = 'y-MM-dd') {
	$date = new Zend_Date($time);
	return $date->toString($format);
}