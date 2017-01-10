<?php

class Siberian_Date extends Zend_Date {

    const HOUR_SECONDS = 3600;
    const DAY_SECONDS = 86400;
    const WEEK_SECONDS = 604800;

    public static function now($locale = null) {
        return new self(time(), self::TIMESTAMP, $locale);
    }

    public function toGmt() {
        $this->addSecond($this->getGmtOffset());
        return $this;
    }

    public function setBeginningOfTheDay() {
        $this->setHour(0);
        $this->setMinute(0);
        $this->setSecond(0);
        return $this;
    }

    public function setEndOfTheDay() {
        $this->setHour(23);
        $this->setMinute(59);
        $this->setSecond(59);
        return $this;
    }

    public static function format($date = null, $format = 'y-MM-dd') {
        $date = new Zend_Date($date, 'y-MM-dd HH:mm:ss');
        return $date->toString($format);
    }

}