<?php

class Siberian_Wrapper_Sqlite {
	private $_binPath = null;
	private $_binWinPath = "lib/Siberian/Wrapper/Sqlite/bin/sqlite3.exe";
	private $_binOSXPath = "lib/Siberian/Wrapper/Sqlite/bin/sqlite3.osx";
	private $_bin32Path = "lib/Siberian/Wrapper/Sqlite/bin/sqlite3";
	private $_bin64Path = "lib/Siberian/Wrapper/Sqlite/bin/sqlite3_64";
	private $_dbPath = null;
	private $_schema = null;

	private $_fallback = false;
	/**
	 * @var SQLite3
	 */
	private $_handle = null;

	// START Singleton stuff
	/**
	 * @var null|Siberian_Wrapper_Sqlite
	 */
	static private $_instance = null;
	private function __construct() {

		if (extension_loaded('sqlite3')) {
			# Use normal sqlite3 php lib

		} else {
			$this->_fallback = true;

			# Fallback with the sqlite3 binaries
			$is_darwin = exec("uname");
			# MacOSX
			if(strpos($is_darwin, "arwin") !== false) {
				$this->_binPath = Core_Model_Directory::getBasePathTo($this->_binOSXPath);
			# Windows
			} elseif (false/** Windows */) {
				$this->_binPath = Core_Model_Directory::getBasePathTo($this->_binWinPath);
			# Linux 32bits or 64bits
			} else {
				exec(Core_Model_Directory::getBasePathTo($this->_bin32Path)." --version", $output, $return_val);
				if($return_val === 0) {
					$this->_binPath = Core_Model_Directory::getBasePathTo($this->_bin32Path);
				} else {
					$this->_binPath = Core_Model_Directory::getBasePathTo($this->_bin64Path);
				}
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

		if(!$this->_fallback && $this->dbExists()) {
			$this->_handle = new SQLite3($this->_dbPath);
		}

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
			if(!$this->_fallback) {
				$this->_handle = new SQLite3($this->_dbPath);
			}
			$this->query($this->_schema, true);
		} catch (Siberian_Wrapper_Sqlite_Exception $e) {
			throw new Exception("Cannot create sqlite db\n".$e->getMessage());
		}
		return $this;
	}

	public function query($query, $create_db = false) {
		if(is_null($this->_dbPath)) {
			throw new Exception("No db path specified");
		}
		if(strpos($query,'"') !== false) {
			throw new Exception("Char \" is not allowed in query (security purpose)");
		}

		$fetched_data = array();
		if(!$this->_fallback) {
			if($create_db) {
				$output = $this->_handle->exec($query);
				//db is created
				return true;
			} else {
				$res = $this->_handle->query($query);
				if(preg_match("~^select~i",trim($query)) === 1) {
					while($row = $res->fetchArray(SQLITE3_NUM)) {
						array_push($fetched_data, $row);
					}
				}
			}

		} else {
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

			if(is_array($output)) {
				foreach($output as $row) {
					array_push($fetched_data,explode("|", $row));
				}
			}
		}

		if(!empty($fetched_data)) {
			return $fetched_data;
		} else {
			return true;
		}
	}
}
