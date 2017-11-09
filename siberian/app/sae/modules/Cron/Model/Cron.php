<?php
/**
 * Class Cron_Model_Cron
 */
class Cron_Model_Cron extends Core_Model_Default {

	/**
	 * Cron_Model_Cron constructor.
	 * @param array $params
	 */
	public function __construct($params = array()) {
		parent::__construct($params);
		$this->_db_table = 'Cron_Model_Db_Table_Cron';
		return $this;
	}

	public static function is_active(){
		return (System_Model_Config::getValueFor("disable_cron") == "0");
	}

	/**
	 * @param $minute
	 * @param $hour
	 * @param $month_day
	 * @param $month
	 * @param $week_day
	 * @return mixed
	 */
	public function getActiveActions($minute, $hour, $month_day, $month, $week_day){
		$db = $this->getTable();
		$select = $db->select()
			->where("is_active = ?", true)
			->where("minute IN (?)", array(-1, $minute))
			->where("hour IN (?)", array(-1, $hour))
			->where("month_day IN (?)", array(-1, $month_day))
			->where("month IN (?)", array(-1, $month))
			->where("week_day IN (?)", array(-1, $week_day))
			->order(array("standalone ASC", "priority DESC"))
		;

		return $db->fetchAll($select);
	}

    /**
     * @param $command
     * @return mixed
     */
	public function getTaskByCommand($command) {
        $db = $this->getTable();
        $select = $db->select()
            ->where("command = ?", $command)
            ->limit(1)
        ;

        return $db->fetchAll($select);
    }

	/**
	 * Update the last trigger date
	 */
	public function trigger() {
		$this->setLastTrigger(date("Y-m-d H:i:s"))->save();
	}

	/**
	 * Update the last success date
	 */
	public function success() {
		$this->setLastSuccess(date("Y-m-d H:i:s"))->save();
	}

	/**
	 * Update the last failure date
	 */
	public function fail() {
		$this->setLastFail(date("Y-m-d H:i:s"))->save();
	}

	/**
	 * Enable the task
	 */
	public function enable() {
		$this->setIsActive(true)->save();
	}

	/**
	 * Disable the task
	 */
	public function disable() {
		$this->setIsActive(false)->save();
	}

	/**
	 * @param $message
	 * @return mixed
	 */
	public function saveLastError($message) {
		return $this->setLastErrorDate(date("Y-m-d H:i:s"))
				->setLastError($message)->save();
	}

	/**
	 * Test if the Cron scheduler is running
	 *
	 * @return bool
	 */
	public static function isRunning() {
		$db = Zend_Db_Table::getDefaultAdapter();
		$select = $db
			->select()
			->from("cron", array("last_success AS time"))
			->where("command = ?", "pushinstant")
			->order("last_success DESC")
			->limit(1)
		;

		$result = $db->fetchRow($select);
		if(isset($result) && isset($result["time"])) {
            $r = new Zend_Date();
            $r->set($result["time"], 'YYYY-MM-dd HH:mm:ss'); 
            $n = Zend_Date::now();
      
			$diff = $n->getTimestamp() - $r->getTimestamp();
			if(abs($diff) <= 65) {
				return true;
			}

			/** Second test for SAE Fallback */
			if(Siberian_Version::is("SAE") && isset($_config["cron_secret"]) && (abs($diff) <= 610)) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @return bool|string
	 */
	public static function getLastError() {
		$db = Zend_Db_Table::getDefaultAdapter();
		$select = $db
			->select()
                ->from("cron", array("name", "last_error", "module_id", "last_error_date"))
			->where("last_error != ''")
			->order("updated_at DESC")
			->limit(1)
		;

		$result = $db->fetchRow($select);
		if(isset($result) && isset($result["last_error"])) {
			$error = $result["last_error"];

            try {
                if($result["module_id"] > 0) {
                    $module = new Installer_Model_Installer_Module();
                    $module->find($result["module_id"]);
                    $module->fetch();
                    if($module->getId()) {
                        $module->loadTranslations();
                    }
                }
            } catch (Exception $e) {
                log_debug($e);
            }
			return array(
				"short" => cut(__($result["name"]).": ".__(str_replace("\n", " ", $error)), 50),
				"full" => __($result["name"]).": <br>".str_replace("\n", "<br>", __(htmlspecialchars($error))),
				"date" => datetime_to_format($result["last_error_date"])
			);
		}

		return false;
	}

	/**
	 * Cleaning errors
	 */
	public function clearErrors() {
		$db = Zend_Db_Table::getDefaultAdapter();
		$db->query("UPDATE cron SET last_error = '';");
	}
}