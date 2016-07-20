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
			->order("priority DESC")
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
}