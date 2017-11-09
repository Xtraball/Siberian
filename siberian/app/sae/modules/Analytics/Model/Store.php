<?php

class Analytics_Model_Store {

	// START Singleton stuff
	static private $_instance = null;

	private $_sqliteAdapter = null;
	private $_appInstallationField = array("appId"
		, "latitude"
		, "longitude"
		, "timestampGMT"
		, "OS"
		, "OSVersion"
		, "Device"
		, "DeviceVersion"
		, "deviceUUID"
	);
	private $_appLoadedField = array("appId"
		, "latitude"
		, "longitude"
		, "startTimestampGMT"
		, "endTimestampGMT"
		, "OS"
		, "OSVersion"
		, "Device"
		, "DeviceVersion"
		, "locale"
		, "deviceUUID"
	);
	private $_appPageNavigation = array("featureId"
		, "latitude"
		, "longitude"
		, "timestampGMT"
		, "OS"
		, "OSVersion"
		, "Device"
		, "DeviceVersion"
		, "locale"
		, "deviceUUID"
	);
	private $_addAppMcommerceProductNavigation = array("productId"
		, "name"
		, "latitude"
		, "longitude"
		, "timestampGMT"
		, "OS"
		, "OSVersion"
		, "Device"
		, "DeviceVersion"
		, "locale"
		, "deviceUUID"
	);
	private $_addAppMcommerceProductSold = array("productId"
		, "categoryId"
		, "quantity"
		, "latitude"
		, "longitude"
		, "timestampGMT"
		, "OS"
		, "OSVersion"
		, "Device"
		, "DeviceVersion"
		, "locale"
		, "deviceUUID"
	);

	private function __construct() {
		$this->_sqliteAdapter = Siberian_Wrapper_Sqlite::getInstance();
		$this->_sqliteAdapter->setDbPath(Core_Model_Directory::getBasePathTo("metrics/siberiancms.db"));
	}
	public static function getInstance() {
		if(!isset(self::$_instance)) {
			self::$_instance = new Analytics_Model_Store();
		}
		return self::$_instance;
	}
	// END Singleton stuff

	public function getAdapter() {
		return $this->_sqliteAdapter;
	}

	public function getInstalledApp($where = null, $limit = 100) {
		$result = array();

		$fields = $this->_appInstallationField;
		$where_query = "";
		$where_array = array();

//		$result = $this->_sqliteAdapter->query("SELECT ".implode(",",$fields)." from app_installation LIMIT 0,$limit");
//		//fill result with keys
//		return array_map(function($row) use ($fields) {
//			return array_combine(
//				$fields,
//				$row
//			);
//		},$result);

		if($where) {
			foreach ($where as $field => $value) {
				$where_array[] = str_ireplace("?", $value, $field);
			}
			$where_query = "WHERE " . implode(" AND ", $where_array);
		}

		// Get installed app by devices
		$res_query = $this->_sqliteAdapter->query("
			SELECT device, COUNT(device)
			FROM app_installation
			$where_query
			GROUP BY device
			LIMIT 0,$limit
		");

		$fields = array("device", "nb");
		$count_by_device_type = array_map(function($row) use ($fields) {
			return array_combine(
				$fields,
				$row
			);
		}, $res_query);

		$total = 0;
		foreach($count_by_device_type as $device_type) {
			$total += $device_type['nb'];
		}

		$result["by_devices"] = $count_by_device_type;
		$result["total"] = $total;

		return $result;
	}

	public function getAppLoaded($limit = 100) {
		$fields = $this->_appLoadedField;
		$result = $this->_sqliteAdapter->query("SELECT ".implode(",",$fields)." from app_loaded LIMIT 0,$limit");

		//fill result with keys
		return array_map(function($row) use ($fields) {
			return array_combine(
				$fields,
				$row
			);
		},$result);
	}

	public function getAppPageNavigation($limit = 100) {
		$fields = $this->_appPageNavigation;
		$result = $this->_sqliteAdapter->query("SELECT ".implode(",",$fields)." from page_navigation LIMIT 0,$limit");

		//fill result with keys
		return array_map(function($row) use ($fields) {
			return array_combine(
				$fields,
				$row
			);
		},$result);
	}

	public function getAppMcommerceProductNavigation($limit = 100) {
		$fields = $this->_addAppMcommerceProductNavigation;
		$result = $this->_sqliteAdapter->query("SELECT ".implode(",",$fields)." from mcommerce_product_navigation LIMIT 0,$limit");

		//fill result with keys
		return array_map(function($row) use ($fields) {
			return array_combine(
				$fields,
				$row
			);
		},$result);
	}

	public function getAppMcommerceProductSold($limit = 100) {
		$fields = $this->_addAppMcommerceProductSold;
		$result = $this->_sqliteAdapter->query("SELECT ".implode(",",$fields)." from mcommerce_product_sold LIMIT 0,$limit");

		//fill result with keys
		return array_map(function($row) use ($fields) {
			return array_combine(
				$fields,
				$row
			);
		},$result);
	}

	public function addAppInstallationMetric($metric) {
		$fields = $this->_appInstallationField;
		$metric_values = implode(",",
			array_map(
				function($field) use ($metric) {
					return "'".addslashes($metric[$field])."'";
				}
				,$fields)
		);

		$result = $this->_sqliteAdapter->query(
			"INSERT INTO app_installation (".implode(",",$fields).") VALUES ($metric_values)"
		);

		if($result) return true;
		return false;
	}

	public function addAppLoadedMetric($metric, $id = null) {
		$fields = $this->_appLoadedField;
		if($id) {
			$metric_values = $metric;
		} else {
			$metric_values = implode(",",
				array_map(
					function ($field) use ($metric) {
						return "'" . addslashes($metric[$field]) . "'";
					}
					, $fields)
			);
		}

		$query = "INSERT INTO app_loaded (".implode(",",$fields).") VALUES ($metric_values); SELECT last_insert_rowid();";
		if($id) {
			$query = "UPDATE app_loaded SET ".implode(",",$metric_values)." WHERE id = $id;";
		}

		$result = $this->_sqliteAdapter->query($query);

		if($result) {
			if($id) return true;
			return $result[0];
		} else {
			return false;
		}
	}

	public function addAppPageNavigationMetric($metric) {
		$fields = $this->_appPageNavigation;
		$metric_values = implode(",",
			array_map(
				function($field) use ($metric) {
					return "'".addslashes($metric[$field])."'";
				}
				,$fields)
		);

		$result = $this->_sqliteAdapter->query(
			"INSERT INTO page_navigation (".implode(",",$fields).") VALUES ($metric_values)"
		);

		if($result) return true;
		return false;
	}

	public function addAppMcommerceProductNavigationMetric($metric) {
		$fields = $this->_addAppMcommerceProductNavigation;
		$metric_values = implode(",",
			array_map(
				function($field) use ($metric) {
					return "'".addslashes($metric[$field])."'";
				}
				,$fields)
		);

		$result = $this->_sqliteAdapter->query(
			"INSERT INTO mcommerce_product_navigation (".implode(",",$fields).") VALUES ($metric_values)"
		);

		if($result) return true;
		return false;
	}

	public function addAppMcommerceProductSoldMetric($metric) {
		$fields = $this->_addAppMcommerceProductSold;
		$metric_values = implode(",",
			array_map(
				function($field) use ($metric) {
					return "'".addslashes($metric[$field])."'";
				}
				,$fields)
		);

		$result = $this->_sqliteAdapter->query(
			"INSERT INTO mcommerce_product_sold (".implode(",",$fields).") VALUES ($metric_values)"
		);

		if($result) return true;
		return false;
	}

}