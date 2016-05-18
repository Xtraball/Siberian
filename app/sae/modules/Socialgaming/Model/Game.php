<?php

class Socialgaming_Model_Game extends Core_Model_Default {

    const PERIOD_WEEKLY = 0;
    const PERIOD_MONTHLY = 1;

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Socialgaming_Model_Db_Table_Game';
        return $this;
    }

    public function findCurrent($value_id) {
        $row = $this->getTable()->findCurrent($value_id);
        $this->_prepareDatas($row);
        return $this;
    }

    public function findNext($value_id) {
        $row = $this->getTable()->findNext($value_id);
        $this->_prepareDatas($row);

        return $this;
    }

    public function findDefault() {
        $this->setPeriodId(1);

        return $this;
    }

    public function getFromDateToDate() {

        $start = new Siberian_Date();
        $end = new Siberian_Date();

        switch($this->getPeriodId()) {
            case self::PERIOD_WEEKLY : $start->setWeekday(1); break;
            case self::PERIOD_MONTHLY : $start->setDay(1); break;
        }

        $start->setBeginningOfTheDay();
        $end->setEndOfTheDay();

        return array($start, $end);
    }

    public function save() {

        if(!$this->getPeriodId()) $this->setPeriodId(self::PERIOD_WEEKLY);

        return parent::save();
    }

    public function setEndAt() {

        $now = Zend_Date::now();
        switch($this->getPeriodId()) {
            case self::PERIOD_WEEKLY : $now->setWeekday(7); break;
            case self::PERIOD_MONTHLY : $now->setDay($now->get(Zend_Date::MONTH_DAYS)); break;
        }

        $this->setData('end_at', $now->toString('yyyy-MM-dd'));

        return $this;
    }

    public function getPeriodLabel() {
        $name = '';

        switch($this->getPeriodId()) {
            case self::PERIOD_WEEKLY : $name = $this->_('A week'); break;
            case self::PERIOD_MONTHLY : $name = $this->_('A month'); break;
        }

        return $name;
    }

    public function getGamePeriodLabel() {
        $name = '';

        switch($this->getPeriodId()) {
            case self::PERIOD_WEEKLY : $name = $this->_('Rank of the week'); break;
            case self::PERIOD_MONTHLY : $name = $this->_('Rank of the month'); break;
        }

        return $name;
    }

    public function getPeriods() {
        return array(
            self::PERIOD_WEEKLY => $this->_('A week'),
            self::PERIOD_MONTHLY => $this->_('A month')
        );
    }

    public function copyTo($option) {
        $this->setId(null)->setValueId($option->getId())->save();
        return $this;
    }
}
