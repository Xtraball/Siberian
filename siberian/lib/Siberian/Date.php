<?php

namespace Siberian;

/**
 * Class \Siberian\Date
 */
class Date extends \Zend_Date
{
    /**
     * @var integer
     */
    const HOUR_SECONDS = 3600;

    /**
     * @var integer
     */
    const DAY_SECONDS = 86400;

    /**
     * @var integer
     */
    const WEEK_SECONDS = 604800;

    /**
     * @param null $locale
     * @return Date|\Zend_Date
     * @throws \Zend_Date_Exception
     */
    public static function now($locale = null)
    {
        return new self(time(), self::TIMESTAMP, $locale);
    }

    /**
     * @return $this
     * @throws \Zend_Date_Exception
     */
    public function toGmt()
    {
        $this->addSecond($this->getGmtOffset());
        return $this;
    }

    /**
     * @return $this
     * @throws \Zend_Date_Exception
     */
    public function setBeginningOfTheDay()
    {
        $this->setHour(0);
        $this->setMinute(0);
        $this->setSecond(0);
        return $this;
    }

    /**
     * @return $this
     * @throws \Zend_Date_Exception
     */
    public function setEndOfTheDay()
    {
        $this->setHour(23);
        $this->setMinute(59);
        $this->setSecond(59);
        return $this;
    }

    /**
     * @param null $date
     * @param string $format
     * @return string
     * @throws \Zend_Date_Exception
     */
    public static function format($date = null, $format = 'y-MM-dd')
    {
        $date = new \Zend_Date($date, 'y-MM-dd HH:mm:ss');
        return $date->toString($format);
    }

}