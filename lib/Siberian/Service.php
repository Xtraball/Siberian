<?php
/**
 * Class Siberian_Service
 *
 * @author Xtraball SAS <dev@xtraball.com>
 *
 * @version 4.2.0
 */

class Siberian_Service {

	public static $REGISTERED_SERVICES = array();

	/**
	 * @return array
	 */
	public static function getServices() {
		try {
			$services = array(
				"cron" => Cron_Model_Cron::isRunning(),
				"cron_error" => Cron_Model_Cron::getLastError(),
				"average_build_time" => Application_Model_Queue::getBuildTime(),
				"push" => Push_Model_Message::getStatistics(),
			);
		} catch (Exception $e) {
			$services = array(
				"cron" => false,
				"cron_error" => false,
				"average_build_time" => 0,
				"push" => array(
					"total" => 0,
					"queued" => 0,
					"success" => 0,
					"failed" => 0,
				),
			);
		}

		return $services;
	}

	/**
	 * Test required extensions
	 */
	public static function getExtensions() {
		$services = array();

		# SQLite3
		try {
			$php_sqlite3 = extension_loaded("sqlite3");
			$bin_sqlite3 = false;
			if(!$php_sqlite3) {
				try {
					$sqlite = Siberian_Wrapper_Sqlite::getInstance();
					$sqlite->setDbPath(Core_Model_Directory::getBasePathTo("var/tmp/test.db"));
					$result = $sqlite->query("SELECT 1;");
					if(!empty($result)) {
						$bin_sqlite3 = true;
					}
				} catch(Exception $e) {
					$bin_sqlite3 = false;
				}
			}

			$services["php_sqlite3"] = $php_sqlite3;
			$services["bin_sqlite3"] = $bin_sqlite3;

		} catch (Exception $e) {
			$services["php_sqlite3"] = false;
			$services["bin_sqlite3"] = false;
		}

		# Java
		try {
			$java_version = false;
			exec("which java 2>&1", $java);
			if(!empty($java) && isset($java[0])) {
				exec($java[0]." -version 2>&1", $version);
			} else {
				exec("/usr/bin/java -version 2>&1", $version);
			}

			if(!empty($version) && isset($version[0])) {
				preg_match("/.*\"([0-9\._-]+).*\"/", $version[0], $matches);
				if(isset($matches[1])) {
					$java_version = $matches[1];
				}
			}

			$services["java"] = $java_version;

		} catch (Exception $e) {
			$services["java"] = false;
		}


        # Testing java version
        $services["java_ok"] = false;
        if($services["java"]) {
		    if(version_compare($services["java"], "1.8.0", ">=")) {
                $services["java_ok"] = true;
            }
        }

		# Android SDK
		$services["android_sdk"] = Application_Model_Tools::isAndroidSDKInstalled();

		return $services;
	}

	/**
	 * Register a service a command, for backoffice informations
	 *
	 * @param $name
	 * @param $options
	 */
	public static function registerService($name, $options) {
		if(!array_key_exists($name, self::$REGISTERED_SERVICES)) {
			self::$REGISTERED_SERVICES[$name] = $options;
		}
	}

	/**
	 * @return array
	 */
	public static function fetchRegisteredServices() {

		$services = array();

		foreach(self::$REGISTERED_SERVICES as $name => $options) {
			$services[$name] = array(
				"status" => false,
				"text" => __("Offline"),
			);

			try {
				$parts = explode("::", $options["command"]);
				$class = $parts[0];
				$method = $parts[1];

				# Tests.
				if(class_exists($class) && method_exists($class, $method)) {
					$text = isset($options["text"]) ? __($options["text"]) : __("Running");
					$result = call_user_func($options["command"]);

					if(!empty($result)) {
						$services[$name] = array(
							"status" => $result,
							"text" => $text,
						);
					}
				}
			} catch (Exception $e) {
				$services[$name] = array(
					"status" => false,
					"text" => __("Offline"),
				);
			}
		}

		return $services;
	}
}
