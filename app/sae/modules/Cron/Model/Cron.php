<?php

class Cron_Model_Cron {
	
	const STATE_ON = 1;
	const STATE_OFF = 0;
	
	public function getActive($day, $hour, $wday){
		/**$select = $this->select()
			->where('state = ?', self::STATE_ON)
			->where('day IN (?)', array(-1,$day))
			->where('hour IN (?)', array(-1,$hour))
			->where('wday IN (?)', array(-1,$wday))
			;
		return $this->fetchAll($select);*/
	}
}