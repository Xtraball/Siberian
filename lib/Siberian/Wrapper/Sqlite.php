<?php

class Siberian_Wrapper_Sqlite {
	private $_binPath = null;
	private $_binWinPath = "lib/Siberian/Wrapper/Sqlite/bin/sqlite3.exe";
	private $_binOSXPath = "lib/Siberian/Wrapper/Sqlite/bin/sqlite3.osx";
	private $_bin32Path = "lib/Siberian/Wrapper/Sqlite/bin/sqlite3";
	private $_bin64Path = "lib/Siberian/Wrapper/Sqlite/bin/sqlite3_64";
	private $_dbPath = null;
	private $_schema = null;

	// START Singleton stuff
	/**
	 * @var null|Siberian_Wrapper_Sqlite
	 */
	static private $_instance = null;
	private function __construct() {
		$is_darwin = exec("uname");
		//MacOSX
		if(strpos($is_darwin, "arwin") !== false) {
			$this->_binPath = Core_Model_Directory::getBasePathTo($this->_binOSXPath);
		//Windows
		} elseif (false/** Windows */) {
			$this->_binPath = Core_Model_Directory::getBasePathTo($this->_binWinPath);
		//Linux 32bits or 64bits
		} else {
			exec(Core_Model_Directory::getBasePathTo($this->_bin32Path)." --version", $output, $return_val);
			if($return_val === 0) {
				$this->_binPath = Core_Model_Directory::getBasePathTo($this->_bin32Path);
			} else {
				$this->_binPath = Core_Model_Directory::getBasePathTo($this->_bin64Path);
			}
		}
	}

	/**
	 * @return null|Siberian_Wrapper_Sqlite
	 */
	public function getInstance() {
		if(!isset(self::$_instance)) {
			self::$_instance = new Siberian_Wrapper_Sqlite();
		}
		return self::$_instance;
	}
	// END Singleton stuff

	public function dbExists() {
		return file_exists($this->_dbPath);
	}

	/**
	 * @param $dbPath
	 * @return Siberian_Wrapper_Sqlite
	 */
	public function setDbPath($dbPath) {
		$this->_dbPath = $dbPath;
		return $this;
	}

	/**
	 * @param $schema
	 * @return Siberian_Wrapper_Sqlite
	 */
	public function setSchema($schema) {
		$this->_schema = $schema;
		return $this;
	}

	public function createDb() {
		try {
			$this->query($this->_schema);
		} catch (Siberian_Wrapper_Sqlite_Exception $e) {
			throw new Exception("Cannot create sqlite db\n".$e->getMessage());
		}
		return $this;
	}

	public function query($query) {
		if(is_null($this->_dbPath)) {
			throw new Exception("No db path specified");
		}
		if(strpos($query,'"') !== false) {
			throw new Exception("Char \" is not allowed in query (security purpose)");
		}

		$cmd = implode(" ", array(
			$this->_binPath,
			$this->_dbPath,
			'"'.$query.'"',
			"2>&1"
		));
		exec($cmd, $output, $return_val);

		if($return_val !== 0 ) {
			throw new Siberian_Wrapper_Sqlite_Exception($query, $output);
		}

		$fetched_data = array();
		foreach($output as $row) {
			array_push($fetched_data,explode("|", $row));
		}

		if($this->dbExists()) {
			return $fetched_data ? $fetched_data : ($return_val === 0);
		}
		return $fetched_data;
	}
}
