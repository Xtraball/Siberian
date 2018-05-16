<?php

require __DIR__ . '/bootstrap.php';

use MomentPHP\MomentPHP;
use Tester\Assert;
use Tester\TestCase;

class MomentPHPTest extends TestCase
{
  /** @var MomentPHP/MomentPHP */
  private $moment;


  protected function setUp()
  {
    $this->moment = new MomentPHP('1980-12-07 19:21:42', null, 'Europe/Prague');
  }

  public function testValidDateTime()
  {
    $type = 'MomentPHP\MomentPHP';

    Assert::type($type, new MomentPHP(null));

    Assert::type($type, new MomentPHP(new DateTime));

    Assert::type($type, new MomentPHP(new MomentPHP));

    Assert::type($type, new MomentPHP('now'));

    Assert::type($type, new MomentPHP(1));
  }


  public function testInvalidDateTime()
  {
    $exception = 'MomentPHP\InvalidArgumentException';

    Assert::exception(function() {
      new MomentPHP(true);
    }, $exception);

    Assert::exception(function() {
      new MomentPHP(function(){});
    }, $exception);

    Assert::exception(function() {
      new MomentPHP(array());
    }, $exception);

    Assert::exception(function() {
      new MomentPHP(1.1);
    }, $exception);

    Assert::exception(function() {
      new MomentPHP(-1);
    }, $exception);

    Assert::exception(function() {
      new MomentPHP('');
    }, $exception);

    Assert::exception(function() {
      new MomentPHP(new stdClass);
    }, $exception);
  }


  public function testValidFormat()
  {
    $type = 'MomentPHP\MomentPHP';

    // format use only if dateTime is a string

    Assert::type($type, new MomentPHP('2000', array('Y')));

    Assert::type($type, new MomentPHP('2000', 'Y'));
  }


  public function testInvalidFormat()
  {
    $exception = 'MomentPHP\InvalidArgumentException';

    Assert::exception(function() {
      new MomentPHP(null, 1);
    }, $exception);

    Assert::exception(function() {
      new MomentPHP(null, 1.1);
    }, $exception);

    Assert::exception(function() {
      new MomentPHP(null, function(){});
    }, $exception);

    Assert::exception(function() {
      new MomentPHP(null, true);
    }, $exception);

    Assert::exception(function() {
      new MomentPHP(null, new stdClass());
    }, $exception);

    Assert::exception(function() {
      new MomentPHP(null, '');
    }, $exception);

    Assert::exception(function() {
      new MomentPHP(null, array());
    },$exception);
  }


  public function testValidTimeZone()
  {
    $type = 'MomentPHP\MomentPHP';

    Assert::type($type, new MomentPHP(null, null, null));

    Assert::type($type, new MomentPHP(null, null, 'Europe/London'));

    Assert::type($type, new MomentPHP(null, null, new DateTimeZone('Europe/London')));
  }


  public function testInvalidTimeZone()
  {
    $exception = 'MomentPHP\InvalidArgumentException';

    Assert::exception(function() {
      new MomentPHP(null, null, array());
    }, $exception);

    Assert::exception(function() {
      new MomentPHP(null, null, true);
    }, $exception);

    Assert::exception(function() {
      new MomentPHP(null, null, function(){});
    }, $exception);

    Assert::exception(function() {
      new MomentPHP(null, null, 1.1);
    }, $exception);

    Assert::exception(function() {
      new MomentPHP(null, null, 1);
    }, $exception);

    Assert::exception(function() {
      new MomentPHP(null, null, new stdClass());
    }, $exception);

    Assert::exception(function() {
      new MomentPHP(null, null, '');
    }, $exception);
  }


  public function testFormat()
  {
    $moment = new MomentPHP(1000000000);
    Assert::equal('2001-09-09', $moment->format('Y-m-d'));

    $moment = new MomentPHP('20000101', 'Ymd');
    Assert::equal('2000-01-01', $moment->format('Y-m-d'));

    $moment = new MomentPHP('20002010', array('Ymd', 'Ydm'));
    Assert::equal('2001-08-10', $moment->format('Y-m-d'));

    $moment = new MomentPHP('20000101', array('d', 'Ymd'));
    Assert::equal('2000-01-01', $moment->format('Y-m-d'));

    $moment = new MomentPHP('1. Jan 2000');
    Assert::equal('2000-01-01', $moment->format('Y-m-d'));

    $moment = new MomentPHP('today is 2000 January 1', '\t\o\d\a\y \i\s Y F j');
    Assert::equal('2000-01-01', $moment->format('Y-m-d'));

    $expression = 'next month';
    $timestamp = strtotime($expression);
    $expect = date('m', $timestamp);
    $moment = new MomentPHP($expression);
    Assert::equal($expect, $moment->format('m'));

    Assert::exception(function() {
      new MomentPHP('2000', 'Ym');
    }, 'MomentPHP\ErrorException', 'DateTime not create. Probably the wrong format.');
  }


  public function testTimestamp()
  {
    Assert::same(345061302, $this->moment->timestamp());
  }


  public function testSeconds()
  {
    Assert::same('42', $this->moment->seconds());
    Assert::same('42', $this->moment->second());
  }


  public function testMinutes()
  {
    Assert::same('21', $this->moment->minutes());
    Assert::same('21', $this->moment->minute());
  }


  public function testHours()
  {
    Assert::same('19', $this->moment->hours());
    Assert::same('19', $this->moment->hour());
  }


  public function testDays()
  {
    Assert::same('07', $this->moment->days());
    Assert::same('07', $this->moment->day());
  }


  public function testWeeks()
  {
    Assert::same('49', $this->moment->weeks());
    Assert::same('49', $this->moment->week());
  }


  public function testMonths()
  {
    Assert::same('12', $this->moment->months());
    Assert::same('12', $this->moment->month());
  }


  public function testYears()
  {
    Assert::same('1980', $this->moment->years());
    Assert::same('1980', $this->moment->year());
  }


  public function testDayOfWeek()
  {
    Assert::same('7', $this->moment->dayOfWeek());
  }


  public function testDayOfYear()
  {
    Assert::same('342', $this->moment->dayOfYear());
  }


  public function testNameOfDayShort()
  {
    Assert::same('Sun', $this->moment->nameOfDayShort());
  }


  public function testNameOfDayLong()
  {
    Assert::same('Sunday', $this->moment->nameOfDayLong());
  }


  public function testDayWithSuffix()
  {
    Assert::same('7th', $this->moment->dayWithSuffix());
  }


  public function testDaysInMonth()
  {
    Assert::same('31', $this->moment->daysInMonth());
  }


  public function testNameOfMonthShort()
  {
    Assert::same('Dec', $this->moment->nameOfMonthShort());
  }


  public function testNameOfMonthLong()
  {
    Assert::same('December', $this->moment->nameOfMonthLong());
  }


  public function testHourWithSuffix()
  {
    Assert::same('7PM', $this->moment->hourWithSuffix());
  }


  public function testIsoDate()
  {
    Assert::same('1980-12-07T19:21:42+01:00', $this->moment->isoDate());
  }


  public function testNameOfTimezone()
  {
    Assert::same('Europe/Prague', $this->moment->nameOfTimezone());
  }


  public function testTimezoneOffset()
  {
    $moment = new MomentPHP(null, null, 'Greenwich');
    Assert::same(0, $moment->timezoneOffset());

    $moment = new MomentPHP(null, null, 'Europe/Prague');
    Assert::same(7200, $moment->timezoneOffset());

    $moment = new MomentPHP(null, null, 'America/New_York');
    Assert::same(-14400, $moment->timezoneOffset());
  }


  public function testAsArray()
  {
    $expected = array(
      'years' => '1980',
      'months' => '12',
      'days' => '07',
      'hours' => '19',
      'minutes' => '21',
      'seconds' => '42',
      'timezoneOffset' => 3600,
    );
    Assert::equal($expected, $this->moment->asArray());
  }


  /**
   * @dataProvider getDataFrom
   */
  public function testFromAfterTime($number, $unit, $result)
  {
    $clone = clone $this->moment;
    $clone->add($number, $unit);
    $result = 'in ' . $result;
    Assert::same($result, $this->moment->from($clone));
  }


  /**
   * @dataProvider getDataFrom
   */
  public function testFromBeforeTime($number, $unit, $result)
  {
    $clone = clone $this->moment;
    $clone->sub($number, $unit);
    $result .= ' ago';
    Assert::same($result, $this->moment->from($clone));
  }


  /**
   * @dataProvider getDataFrom
   */
  public function testFromWithoutSuffix($number, $unit, $result)
  {
    $clone = clone $this->moment;
    $clone->add($number, $unit);
    Assert::same($result, $this->moment->from($clone, true));

  }


  /**
   * @dataProvider getDataFrom
   */
  public function testFromNowBeforeTime($number, $unit, $result)
  {
    $moment = new MomentPHP;
    $moment->sub($number, $unit);
    $result .= ' ago';
    Assert::same($result, $moment->fromNow());
  }


  /**
   * @dataProvider getDataFrom
   */
  public function testFromNowAfterTime($number, $unit, $result)
  {
    $moment = new MomentPHP;
    $moment->add($number, $unit);
    $result = 'in ' . $result;
    Assert::same($result, $moment->fromNow());
  }


  /**
   * @dataProvider getDataFrom
   */
  public function testFromNowWithoutSuffix($number, $unit, $result)
  {
    $moment = new MomentPHP;
    $moment->add($number, $unit);
    Assert::same($result, $moment->fromNow(true));
  }


  public function getDataFrom()
  {
    return array(
      array(44, 'sec', '44 seconds'),
      array(45, 'sec', 'a minute'),
      array(89, 'sec', 'a minute'),
      array(90, 'sec', '2 minutes'),

      array(44, 'min', '44 minutes'),
      array(45, 'min', 'an hour'),
      array(89, 'min', 'an hour'),
      array(90, 'min', '2 hours'),

      array(21, 'hours', '21 hours'),
      array(22, 'hours', 'a day'),
      array(35, 'hours', 'a day'),
      array(36, 'hours', '2 days'),

      array(24, 'days', '24 days'),
      array(26, 'days', 'a month'),
      array(45, 'days', 'a month'),

      array(10, 'months', '10 months'),
      array(12, 'months', 'a year'),
      array(17, 'months', 'a year'),
      array(18, 'months', '2 years'),
    );
  }


  /**
   * @dataProvider getValidIntervalUnits
   */
  public function testValidIntervalUnits($unit)
  {
    Assert::type('MomentPHP\MomentPHP', $this->moment->add(1, $unit));
  }

  public function getValidIntervalUnits()
  {
    return array(
      array('sec'),
      array('second'),
      array('seconds'),
      array('min'),
      array('minute'),
      array('minutes'),
      array('hour'),
      array('hours'),
      array('day'),
      array('days'),
      array('month'),
      array('months'),
      array('year'),
      array('years'),
    );
  }


  /**
   * @dataProvider getInvalidIntervalUnits
   */
  public function testInvalidIntervalUnits($unit)
  {
    Assert::exception(function() use ($unit) {
      $this->moment->add(1, $unit);
    }, 'MomentPHP\InvalidArgumentException');
  }

  public function getInvalidIntervalUnits()
  {
    return array(
      array('_seconds'),
      array('_minutes'),
      array('_hours'),
      array('_days'),
      array('_months'),
      array('_years'),
    );
  }


  public function testAdd()
  {
    Assert::type('MomentPHP\MomentPHP', $this->moment->add(1, 'day'));
    Assert::same('08', $this->moment->days());

    Assert::type('MomentPHP\MomentPHP', $this->moment->add(1, 'days'));
    Assert::same('09', $this->moment->days());

    $interval = DateInterval::createFromDateString('1 day');
    Assert::type('MomentPHP\MomentPHP', $this->moment->add($interval));
    Assert::same('10', $this->moment->days());

    $field = array('days' => 1, 'years' => 1);
    Assert::type('MomentPHP\MomentPHP', $this->moment->add($field));
    Assert::same('11|1981', $this->moment->format('d|Y'));
  }


  public function testSub()
  {
    Assert::type('MomentPHP\MomentPHP', $this->moment->sub(1, 'day'));
    Assert::same('06', $this->moment->days());

    Assert::type('MomentPHP\MomentPHP', $this->moment->sub(1, 'days'));
    Assert::same('05', $this->moment->days());

    $interval = DateInterval::createFromDateString('1 day');
    Assert::type('MomentPHP\MomentPHP', $this->moment->sub($interval));
    Assert::same('04', $this->moment->days());

    $field = array('days' => 1, 'years' => 1);
    Assert::type('MomentPHP\MomentPHP', $this->moment->sub($field));
    Assert::same('03|1979', $this->moment->format('d|Y'));
  }


  public function testDiff()
  {
    $date = '1980-12-07 19:21:41';
    $zone = 'Europe/Prague';

    $moment = new MomentPHP($date, null, $zone);
    Assert::same(1, $this->moment->diff($moment));

    $datetime = new DateTime($date, new DateTimeZone($zone));
    Assert::same(1, $this->moment->diff($datetime));

    $string = $date . '+01:00';
    Assert::same(1, $this->moment->diff($string));
  }


  /**
   * @dataProvider getValidDiffUnits
   */
  public function testDiffWithUnits($unit, $result)
  {
    $moment = new MomentPHP('2000-01-01 00:00:00', 'Y-m-d H:i:s', 'Europe/Prague');
    Assert::same($result, $moment->diff($this->moment, $unit));
  }

  public function getValidDiffUnits()
  {
    return array(
      array('sec', 601619898),
      array('second', 601619898),
      array('seconds', 601619898),
      array('min', 10026998),
      array('minute', 10026998),
      array('minutes', 10026998),
      array('hour', 167116),
      array('hours', 167116),
      array('day', 6963),
      array('days', 6963),
      array('month', 228),
      array('months', 228),
      array('year', 19),
      array('years', 19),
    );
  }


  public function testStartOf()
  {
    $format = 'Y-m-d H:i:s';

    Assert::same('1980-12-07 19:21:42', $this->moment->startOf(MomentPHP::SECONDS)->format($format));

    Assert::same('1980-12-07 19:21:00', $this->moment->startOf(MomentPHP::MINUTES)->format($format));

    Assert::same('1980-12-07 19:00:00', $this->moment->startOf(MomentPHP::HOURS)->format($format));

    Assert::same('1980-12-07 00:00:00', $this->moment->startOf(MomentPHP::DAYS)->format($format));

    Assert::same('1980-12-01 00:00:00', $this->moment->startOf(MomentPHP::MONTHS)->format($format));

    Assert::same('1980-01-01 00:00:00', $this->moment->startOf(MomentPHP::YEARS)->format($format));
  }


  public function testEndOf()
  {
    $format = 'Y-m-d H:i:s';

    Assert::same('1980-12-07 19:21:42', $this->moment->endOf(MomentPHP::SECONDS)->format($format));

    Assert::same('1980-12-07 19:21:59', $this->moment->endOf(MomentPHP::MINUTES)->format($format));

    Assert::same('1980-12-07 19:59:59', $this->moment->endOf(MomentPHP::HOURS)->format($format));

    Assert::same('1980-12-07 23:59:59', $this->moment->endOf(MomentPHP::DAYS)->format($format));

    Assert::same('1980-12-31 23:59:59', $this->moment->endOf(MomentPHP::MONTHS)->format($format));

    Assert::same('1980-12-31 23:59:59', $this->moment->endOf(MomentPHP::YEARS)->format($format));
  }


  /**
   * @dataProvider getFloatValidDiffUnits
   */
  public function testFloatDiffWithUnits($unit, $result)
  {
    $moment = new MomentPHP('2000-01-01 00:00:00', 'Y-m-d H:i:s', 'Europe/Prague');
    Assert::same($result, $moment->diff($this->moment, $unit, true));
  }

  public function getFloatValidDiffUnits()
  {
    return array(
      array('min', 10026998.3),
      array('minute', 10026998.3),
      array('minutes', 10026998.3),
      array('hour', 167116.64),
      array('hours', 167116.64),
      array('day', 6963.19),
      array('days', 6963.19),
      array('month', 228.78),
      array('months', 228.78),
      array('year', 19.07),
      array('years', 19.07),
    );
  }


  public function testIsLeapYear()
  {
    $moment = new MomentPHP('2012', 'Y');
    Assert::true($moment->isLeapYear());

    $moment = new MomentPHP('2013', 'Y');
    Assert::false($moment->isLeapYear());
  }


  public function testIsDaylightSavingTime()
  {
    $moment = new MomentPHP('06', 'm');
    Assert::true($moment->isDST());

    $moment = new MomentPHP('12', 'm');
    Assert::false($moment->isDST());
  }


  public function testIsBefore()
  {
    $dateBefore = '1980-12-06';
    $dateAfter = '1980-12-08';

    Assert::true($this->moment->isBefore($dateAfter));
    Assert::false($this->moment->isBefore($dateBefore));

    Assert::true($this->moment->isBefore(new DateTime));
    Assert::false($this->moment->isBefore(DateTime::createFromFormat('Y-m-d', $dateBefore)));

    Assert::true($this->moment->isBefore(new MomentPHP));
    Assert::false($this->moment->isBefore(new MomentPHP($dateBefore)));

    Assert::true($this->moment->isBefore(time()));
    Assert::false($this->moment->isBefore(0));
  }


  /**
   * @dataProvider getUnits
   */
  public function testIsBeforeWithUnits($unit)
  {
    $momentSame = new MomentPHP('1980-12-07 19:21:42', null, 'Europe/Prague');
    Assert::false($this->moment->isBefore($momentSame, $unit));

    $momentAfter = new MomentPHP('1981-12-07 19:21:42', null, 'Europe/Prague');
    Assert::true($this->moment->isBefore($momentAfter, $unit));
  }


  public function testIsAfter()
  {
    $dateBefore = '1980-12-06';
    $dateAfter = '1980-12-08';

    Assert::false($this->moment->isAfter($dateAfter));
    Assert::true($this->moment->isAfter($dateBefore));

    Assert::false($this->moment->isAfter(new DateTime));
    Assert::true($this->moment->isAfter(DateTime::createFromFormat('Y-m-d', $dateBefore)));

    Assert::false($this->moment->isAfter(new MomentPHP));
    Assert::true($this->moment->isAfter(new MomentPHP($dateBefore)));

    Assert::false($this->moment->isAfter(time()));
    Assert::true($this->moment->isAfter(0));
  }


  /**
   * @dataProvider getUnits
   */
  public function testIsAfterWithUnits($unit)
  {
    $momentAfter = new MomentPHP('1980-12-07 19:21:42', null, 'Europe/Prague');
    Assert::false($this->moment->isBefore($momentAfter, $unit));
  }


  public function testIsSame()
  {
    $dateSame = '1980-12-07 19:21:42 +01:00';

    Assert::true($this->moment->isSame($dateSame));
    Assert::true($this->moment->isSame(DateTime::createFromFormat('Y-m-d H:i:s P', $dateSame)));
    Assert::true($this->moment->isSame(new MomentPHP($dateSame)));
    Assert::true($this->moment->isSame(345061302));

    $dateDifference = '1980-12-07 19:21:40 +01:00';

    Assert::false($this->moment->isSame($dateDifference));
    Assert::false($this->moment->isSame(DateTime::createFromFormat('Y-m-d H:i:s P', $dateDifference)));
    Assert::false($this->moment->isSame(new MomentPHP($dateDifference)));
    Assert::false($this->moment->isSame(0));
  }


  public function getUnits()
  {
    return array(
      array('sec'),
      array('second'),
      array('seconds'),
      array('min'),
      array('minute'),
      array('minutes'),
      array('hour'),
      array('hours'),
      array('day'),
      array('days'),
      array('month'),
      array('months'),
      array('year'),
      array('years'),
    );
  }


  public function testIsMomentPHP()
  {
    Assert::true($this->moment->isMomentPHP(new MomentPHP));

    Assert::false($this->moment->isMomentPHP(new DateTime));
  }
}

$testCase = new MomentPHPTest;
$testCase->run();