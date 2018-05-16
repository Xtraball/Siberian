<?php
/**
 * MomentPHP is date library for parsing, validating, manipulating, and formatting dates.
 * It's inspired by the JavaScript library Moment.js, see http://momentjs.com/
 *
 * @author Ladislav Vondráček
 */

namespace MomentPHP;

class MomentPHP
{
  const VERSION = '1.1';

  const SECONDS = 'seconds';

  const MINUTES = 'minutes';

  const HOURS = 'hours';

  const DAYS = 'days';

  const MONTHS = 'months';

  const YEARS = 'years';


  /** @var \DateTime */
  private $dateTime;

  /** @var array */
  public $lang = array(
    'relativeTime' => array(
      'future' => 'in %s',
      'past' => '%s ago',
      's' => '%d seconds',
      'i' => 'a minute',
      'i+' => '%d minutes',
      'h' => 'an hour',
      'h+' => '%d hours',
      'd' => 'a day',
      'd+' => '%d days',
      'm' => 'a month',
      'm+' => '%d months',
      'y' => 'a year',
      'y+' => '%d years',
    ),
  );


  /**
   * @param \DateTime|MomentPHP|string|int|null $dateTime Instance of classes \DateTime or MomentPHP or string representing the time or timestamp or null for now.
   * @param array|string|null $format Field formats or simple formatting options, see http://php.net/manual/en/datetime.createfromformat.php
   * @param \DateTimeZone|string|null $timeZone Supported Timezones, see http://php.net/manual/en/timezones.php
   * @throws InvalidArgumentException
   */
  public function __construct($dateTime = null, $format = null, $timeZone = null)
  {
    $this->validateDateTime($dateTime);
    $this->validateFormat($format);
    $this->validateTimeZone($timeZone);

    $timeZone = $this->createDateTimeZone($timeZone);

    try {
      // set dateTime by type
      if (!isset($dateTime)) {
        $this->dateTime = new \DateTime('now', $timeZone);
      }
      elseif ($dateTime instanceof \DateTime) {
        $this->dateTime = $dateTime;
      }
      elseif ($dateTime instanceof MomentPHP) {
        $this->dateTime = $dateTime->dateTime;
      }
      elseif (is_string($dateTime)) {
        $this->dateTime = $this->fromFormat($dateTime, $format, $timeZone);
      }
      elseif (is_int($dateTime)) {
        $this->dateTime = $this->fromFormat($dateTime, 'U', $timeZone);
      }
    }
    catch (\Exception $e) {
      throw new InvalidArgumentException($e->getMessage());
    }

    $error = $this->dateTime->getLastErrors();
    if ($error['warning_count'] > 0) {
      $msg = 'WARNINGS: ' . join('; ', $error['warnings']);
      throw new InvalidArgumentException($msg);
    }
    if ($error['error_count'] > 0) {
      $msg = 'ERRORS: ' . join('; ', $error['errors']);
      throw new InvalidArgumentException($msg);
    }
  }


  /**
   * @return MomentPHP
   */
  public function __clone()
  {
    $this->dateTime = clone $this->dateTime;
  }


  /**
   * @return \DateTime
   */
  public function getDateTime()
  {
    return $this->dateTime;
  }


  /************************************ DISPLAY ************************************/

  /**
   * Return formatted date time.
   *
   * @param string $format
   * @return string
   * @throws InvalidArgumentException
   */
  public function format($format)
  {
    if (!is_string($format)) {
      throw new InvalidArgumentException('Type of format is invalid.');
    }

    $stringDatetime = $this->dateTime->format($format);

    return $stringDatetime;
  }


  /**
   * Seconds from the Unix Epoch (January 1 1970 00:00:00 GMT) to date time.
   *
   * @return int
   */
  public function timestamp()
  {
    $timestamp = $this->format('U');

    return (int)$timestamp;
  }


  /**
   * Seconds of date time with leading zeros.
   *
   * @return string
   */
  public function seconds()
  {
    $seconds = $this->dateTime->format('s');

    return $seconds;
  }


  /**
   * Alias for method seconds().
   *
   * @inherit
   */
  public function second()
  {
    return $this->seconds();
  }


  /**
   * Minutes of date time with leading zeros.
   *
   * @return string
   */
  public function minutes()
  {
    $minutes = $this->format('i');

    return $minutes;
  }


  /**
   * Alias for method minutes().
   *
   * @inherit
   */
  public function minute()
  {
    return $this->minutes();
  }


  /**
   * 24-hour format of an hour of date time with leading zeros.
   *
   * @return string
   */
  public function hours()
  {
    $hours = $this->format('H');

    return $hours;
  }


  /**
   * Alias for method hours().
   *
   * @inherit
   */
  public function hour()
  {
    return $this->hours();
  }


  /**
   * Days of date time with leading zeros.
   *
   * @return string
   */
  public function days()
  {
    $days = $this->format('d');

    return $days;
  }


  /**
   * Alias for method days().
   *
   * @inherit
   */
  public function day()
  {
    return $this->days();
  }


  /**
   * ISO-8601 week number of year, weeks starting on Monday.
   *
   * @return string
   */
  public function weeks()
  {
    $weeks = $this->format('W');

    return $weeks;
  }


  /**
   * Alias for method weeks().
   *
   * @inherit
   */
  public function week()
  {
    return $this->weeks();
  }


  /**
   * Numeric representation of a month of date time with leading zeros.
   *
   * @return string
   */
  public function months()
  {
    $months = $this->format('m');

    return $months;
  }


  /**
   * Alias for method months().
   *
   * @inherit
   */
  public function month()
  {
    return $this->months();
  }


  /**
   * A full numeric representation of a year of date time.
   *
   * @return string
   */
  public function years()
  {
    $years = $this->format('Y');

    return $years;
  }


  /**
   * Alias for method years().
   *
   * @inherit
   */
  public function year()
  {
    return $this->years();
  }


  /**
   * ISO-8601 numeric representation of the day of the week. 1 (for Monday) through 7 (for Sunday).
   *
   * @return string
   */
  public function dayOfWeek()
  {
    $ofWeek = $this->format('N');

    return $ofWeek;
  }


  /**
   * The day of the year (starting from 1).
   *
   * @return string
   */
  public function dayOfYear()
  {
    $ofYear = $this->format('z');

    // transform starting from 1
    $ofYear++;

    // back to string
    settype($ofYear, 'string');

    return $ofYear;
  }


  /**
   * A textual representation of the day of week, three letters.
   *
   * @return string
   */
  public function nameOfDayShort()
  {
    $name = $this->format('D');

    return $name;
  }


  /**
   * A full textual representation of the day of the week.
   *
   * @return string
   */
  public function nameOfDayLong()
  {
    $name = $this->format('l');

    return $name;
  }


  /**
   * English ordinal suffix for the day of the month, 2 characters, st, nd, rd or th.
   *
   * @return string
   */
  public function dayWithSuffix()
  {
    $suffix = $this->format('S');
    $day = $this->format('j');

    return $day . $suffix;
  }


  /**
   * Number of days in the given month, 28 through 31.
   *
   * @return string
   */
  public function daysInMonth()
  {
    $count = $this->format('t');

    return $count;
  }


  /**
   * A short textual representation of a month, three letters.
   *
   * @return string
   */
  public function nameOfMonthShort()
  {
    $name = $this->format('M');

    return $name;
  }


  /**
   * A full textual representation of a month.
   *
   * @return string
   */
  public function nameOfMonthLong()
  {
    $name = $this->format('F');

    return $name;
  }


  /**
   * 12-hour format of an hour AM or PM suffix.
   *
   * @return string
   */
  public function hourWithSuffix()
  {
    $hour = $this->format('gA');

    return $hour;
  }


  /**
   * ISO 8601 format date.
   *
   * @return string
   */
  public function isoDate()
  {
    $isoDate = $this->format('c');

    return $isoDate;
  }


  /**
   * Timezone identifier.
   *
   * @return string
   */
  public function nameOfTimezone()
  {
    $name = $this->format('e');

    return $name;
  }


  /**
   * Timezone offset in seconds, -43200 through 50400.
   *
   * @return int
   */
  public function timezoneOffset()
  {
    $seconds = $this->format('Z');

    return (int)$seconds;
  }


  /**
   * Return the difference in seconds.
   *
   * @param MomentPHP|\DateTime|string|int $dateTime
   * @param string $unit
   * @param bool $asFloat
   * @return int
   * @throws InvalidArgumentException
   */
  public function diff($dateTime, $unit = self::SECONDS, $asFloat = false)
  {
    if ($dateTime instanceof MomentPHP) {
      $diffMoment = $dateTime;
    }
    elseif ($dateTime instanceof \DateTime || is_string($dateTime) || is_int($dateTime)) {
      $diffMoment = new self($dateTime);
    }
    else {
      throw new InvalidArgumentException('Invalid type of datetime to difference.');
    }

    $unit = $this->normalizeUnits($unit);

    if ($unit === self::YEARS || $unit === self::MONTHS) {
      // average number of days in the months in the given dates
      $avgSecondsInMonth = ((int)$this->daysInMonth() + (int)$diffMoment->daysInMonth()) / 2 * 24 * 60 * 60;

      $differenceMonths = (((int)$this->years() - (int)$diffMoment->years()) * 12) + ((int)$this->months() - (int)$diffMoment->months());

      $cloneThis = clone $this;
      $cloneDiffMoment = clone $diffMoment;

      $differenceDays = (($this->timestamp() - $cloneThis->startOf(self::MONTHS)->timestamp()) - ($diffMoment->timestamp() - $cloneDiffMoment->startOf(self::MONTHS)->timestamp())) / $avgSecondsInMonth;
      $differenceTimezone = (($this->timezoneOffset() - $cloneThis->startOf(self::MONTHS)->timezoneOffset()) - ($diffMoment->timezoneOffset() - $cloneDiffMoment->startOf(self::MONTHS)->timezoneOffset())) / $avgSecondsInMonth;
      $difference = $differenceMonths + $differenceDays - $differenceTimezone;

      if ($unit === self::YEARS) {
        $difference /= 12;
      }
    }
    else {
      $difference = $this->timestamp() - $diffMoment->timestamp();

      switch ($unit) {
        case self::MINUTES:
          $difference /= 60;
          break;

        case self::HOURS:
          $difference /= (60 * 60);
          break;

        case self::DAYS:
          $difference /= (24 * 60 * 60);
          break;
      }

    }

    $difference = $asFloat ? round($difference, 2) : (int)floor($difference);

    return $difference;
  }


  /**
   * The DateTime object as array. Field contains years, months, days, hours, minutes, second and timezone offset.
   *
   * @return array
   */
  public function asArray()
  {
    $dateField = array(
      self::SECONDS => $this->seconds(),
      self::MINUTES => $this->minutes(),
      self::HOURS => $this->hours(),
      self::DAYS => $this->days(),
      self::MONTHS => $this->months(),
      self::YEARS => $this->years(),
      'timezoneOffset' => $this->timezoneOffset(),
    );

    return $dateField;
  }


  /**
   * Return relation time from other time.
   *
   * @param MomentPHP|\DateTime|string|int $datetime
   * @param bool $withoutSuffix
   * @return string
   */
  public function from($datetime, $withoutSuffix = false)
  {
    $diffMoment = new self($datetime);

    $diff = $this->diff($diffMoment);

    $negation = ($diff < 0);
    $diff = abs($diff);

    $unit = self::SECONDS;

    // display seconds from 0 to 44 seconds
    if ($diff >= 45) {
      $diff = abs($this->diff($diffMoment, self::MINUTES, true));
      $unit = self::MINUTES;
    }

    // display minutes from 45 to 89 minutes
    if ($diff >= 45 && $unit == self::MINUTES) {
      $diff = abs($this->diff($diffMoment, self::HOURS, true));
      $unit = self::HOURS;
    }

    // display hours from 22 to 36 hours
    if ($diff >= 22 && $unit == self::HOURS) {
      $diff = abs($this->diff($diffMoment, self::DAYS, true));
      $unit = self::DAYS;
    }

    // display months from 25 to 345 days (to 1.5 year)
    if ($diff >= 25 && $diff < 345 && $unit == self::DAYS) {
      $diff = abs($this->diff($diffMoment, self::MONTHS, true));
      $unit = self::MONTHS;
    }
    // display years from 345 days
    elseif ($diff >= 345 && $unit == self::DAYS) {
      $diff = abs($this->diff($diffMoment, self::YEARS, true));
      $unit = self::YEARS;
    }

    $diff = round($diff);

    $keys = array(
      self::SECONDS => 's',
      self::MINUTES => 'i',
      self::HOURS => 'h',
      self::DAYS => 'd',
      self::MONTHS => 'm',
      self::YEARS => 'y',
    );

    $key = $keys[$unit];

    if ($diff > 1 && $unit != self::SECONDS) {
      $key .= '+';
    }

    $lang = $this->lang['relativeTime'];
    $output = sprintf($lang[$key], $diff);

    if (!$withoutSuffix) {
      $output = $negation ? sprintf($lang['future'], $output) : sprintf($lang['past'], $output);
    }

    return $output;
  }


  /**
   * Return relation time from now.
   *
   * @param bool $withoutSuffix
   * @return string
   */
  public function fromNow($withoutSuffix = false)
  {
    $now = new self;

    $output = $now->from($this, $withoutSuffix);

    return $output;
  }


  /************************************ MANIPULATE ************************************/

  /**
   * Adds an amount of days, months, years, hours, minutes and seconds.
   *
   * @param \DateInterval|array|int $number
   * @param string $unit
   * @return $this
   */
  public function add($number, $unit = null)
  {
    if ($number instanceof \DateInterval) {
      $interval = $number;
    }
    elseif (is_array($number)) {
      $expression = $this->getIntervalExpressionFromArray($number);
      $interval = \DateInterval::createFromDateString($expression);
    }
    elseif (is_int($number)) {
      $expression = $this->getIntervalExpression($number, $unit);
      $interval = \DateInterval::createFromDateString($expression);
    }

    $this->dateTime->add($interval);

    return $this;
  }


  /**
   * Subtracts an amount of days, months, years, hours, minutes and seconds.
   *
   * @param \DateInterval|array|int $number
   * @param string|null $unit
   * @return $this
   */
  public function sub($number, $unit = null)
  {
    if ($number instanceof \DateInterval) {
      $interval = $number;
    }
    elseif (is_array($number)) {
      $expression = $this->getIntervalExpressionFromArray($number);
      $interval = \DateInterval::createFromDateString($expression);
    }
    elseif (is_int($number)) {
      $expression = $this->getIntervalExpression($number, $unit);
      $interval = \DateInterval::createFromDateString($expression);
    }

    $this->dateTime->sub($interval);

    return $this;
  }


  /**
   * Mutates the original moment by setting it to the start of a unit of time.
   *
   * @param string $unit
   * @return $this
   */
  public function startOf($unit)
  {
    $unit = $this->normalizeUnits($unit);

    $dateField = $this->asArray();

    switch ($unit) {
      case self::YEARS:
        $dateField[self::MONTHS] = '01';
      case self::MONTHS:
        $dateField[self::DAYS] = '01';
      case self::DAYS:
        $dateField[self::HOURS] = '00';
      case self::HOURS:
        $dateField[self::MINUTES] = '00';
      case self::MINUTES:
        $dateField[self::SECONDS] = '00';
    }

    $this->modifyDateTime($dateField);

    return $this;
  }


  /**
   * Mutates the original moment by setting it to the end of a unit of time.
   *
   * @param string $unit
   * @return $this
   */
  public function endOf($unit)
  {
    $unit = $this->normalizeUnits($unit);

    $dateField = $this->asArray();

    switch ($unit) {
      case self::YEARS:
        $dateField[self::MONTHS] = '12';
      case self::MONTHS:
        $dateField[self::DAYS] = $this->daysInMonth();
      case self::DAYS:
        $dateField[self::HOURS] = '23';
      case self::HOURS:
        $dateField[self::MINUTES] = '59';
      case self::MINUTES:
        $dateField[self::SECONDS] = '59';
    }

    $this->modifyDateTime($dateField);

    return $this;
  }


  /************************************ QUERY ************************************/

  /**
   * Whether it's a leap year.
   *
   * @return bool
   */
  public function isLeapYear()
  {
    $isLeap = $this->format('L');

    return (bool)$isLeap;
  }


  /**
   * Whether or not the date is in daylight saving time.
   *
   * @return bool
   */
  public function isDST()
  {
    $isDST = $this->format('I');

    return (bool)$isDST;
  }


  /**
   * Check if a moment is before another moment.
   *
   * @param MomentPHP|\DateTime|string|int $datetime
   * @param string $unit
   * @return bool
   */
  public function isBefore($datetime, $unit = self::SECONDS)
  {
    $compareMoment = new self($datetime);
    $originMoment = clone($this);

    $compareMoment->startOf($unit);
    $originMoment->startOf($unit);

    $difference = $originMoment->diff($compareMoment);

    return ($difference < 0);
  }


  /**
   * Check if a moment is after another moment.
   *
   * @param MomentPHP|\DateTime|string|int $datetime
   * @param string $unit
   * @return bool
   */
  public function isAfter($datetime, $unit = self::SECONDS)
  {
    $compareMoment = new self($datetime);
    $originMoment = clone($this);

    $compareMoment->startOf($unit);
    $originMoment->startOf($unit);

    $difference = $originMoment->diff($compareMoment);

    return ($difference > 0);
  }


  /**
   * Check if a moment is same another moment.
   *
   * @param MomentPHP|\DateTime|string|int $datetime
   * @param string $unit
   * @return bool
   */
  public function isSame($datetime, $unit = self::SECONDS)
  {
    $compareMoment = new self($datetime);
    $originMoment = clone($this);

    $compareMoment->startOf($unit);
    $originMoment->startOf($unit);

    $difference = $originMoment->diff($compareMoment);

    return ($difference == 0);
  }


  /**
   * Check if a variable is a MomentPHP object.
   *
   * @param mixed $moment
   * @return bool
   */
  public function isMomentPHP($moment)
  {
    return ($moment instanceof MomentPHP);
  }


  /************************************ INTERNAL ************************************/

  /**
   * @param mixed $dateTime
   * @throws InvalidArgumentException
   */
  private function validateDateTime($dateTime)
  {
    // invalid if...
    if (
      isset($dateTime) && // ...exists and...
      !($dateTime instanceof MomentPHP) && // ...not MomentPHP
      !($dateTime instanceof \DateTime) && // ...not \DateTime
      !is_string($dateTime) && // ...not string
      !is_int($dateTime) || // ...not integer
      (is_string($dateTime) && strlen($dateTime) === 0) || // ...not empty string
      (is_int($dateTime) && $dateTime < 0) // ...not negative integer
    ) {
      throw new InvalidArgumentException('Type of datetime is invalid.');
    }
  }


  /**
   * @param mixed $format
   * @throws InvalidArgumentException
   */
  private function validateFormat($format)
  {
    // invalid if...
    if (
      isset($format) && // ...exists and...
      !is_array($format) && // ...not array
      !is_string($format) || // ...not string
      (is_array($format) && count($format) === 0) || // ...not empty array
      (is_string($format) && strlen($format) === 0) // ...not empty string
    ) {
      throw new InvalidArgumentException('Type of format is invalid.');
    }
  }


  /**
   * @param mixed $timeZone
   * @throws InvalidArgumentException
   */
  private function validateTimeZone($timeZone)
  {
    // invalid if...
    if (
      isset($timeZone) && // ...exists and...
      !($timeZone instanceof \DateTimeZone) && // ...not \DateTimeZone
      !is_string($timeZone) || // ...not string
      (is_string($timeZone) && strlen($timeZone) === 0) // ...not empty string
    ) {
      throw new InvalidArgumentException('Type of timezone is invalid.');
    }
  }


  /**
   * Create DateTime
   *
   * @param string $dateTime
   * @param array|string|null $format
   * @param \DateTimeZone $timeZone
   * @return \DateTime
   * @throws ErrorException
   */
  private function fromFormat($dateTime, $format, $timeZone)
  {
    // without format
    if (!isset($format)) {
      $return = new \DateTime($dateTime, $timeZone);
    }
    // simple format
    elseif (is_string($format)) {
      $return = \DateTime::createFromFormat($format, $dateTime, $timeZone);
    }
    // walk all formats
    elseif (is_array($format)) {
      foreach ($format as $item) {
        $return = \DateTime::createFromFormat($item, $dateTime, $timeZone);

        // return first acceptable format
        if ($return instanceof \DateTime) {
          break;
        }
      }
    }

    if ($return === false) {
      throw new ErrorException('DateTime not create. Probably the wrong format.');
    }

    return $return;
  }

  /**
   * @param \DateTimeZone|string|null $timeZone
   * @return \DateTimeZone
   */
  private function createDateTimeZone($timeZone)
  {
    if (!isset($timeZone)) {
      $defaultTimeZone = date_default_timezone_get();
      $return = new \DateTimeZone($defaultTimeZone);
    }
    elseif (is_string($timeZone)) {
      $return = new \DateTimeZone($timeZone);
    }
    elseif ($timeZone instanceof \DateTimeZone) {
      $return = $timeZone;
    }

    return $return;
  }


  /**
   * @param array $numbers
   * @return string
   * @throws InvalidArgumentException
   */
  private function getIntervalExpressionFromArray(array $numbers)
  {
    if (count($numbers) === 0) {
      throw new InvalidArgumentException('Count items must be nonzero.');
    }

    $expressions = array();
    foreach ($numbers as $unit => $number) {
      $expressions[] = $this->getIntervalExpression($number, $unit);
    }

    return join(' ', $expressions);
  }


  /**
   * @param int $number
   * @param string $unit
   * @return string
   * @throws InvalidArgumentException
   */
  private function getIntervalExpression($number, $unit)
  {
    if (!is_int($number)) {
      throw new InvalidArgumentException('The number must be integer.');
    }

    $unit = $this->normalizeUnits($unit);
    $expression = $number . ' ' . $unit;

    return $expression;
  }


  /**
   * @param string $unit
   * @return string mixed
   * @throws InvalidArgumentException
   */
  private function normalizeUnits($unit)
  {
    $validUnits = array(
      'sec' => self::SECONDS,
      'second' => self::SECONDS,
      'seconds' => self::SECONDS,
      'min' => self::MINUTES,
      'minute' => self::MINUTES,
      'minutes' => self::MINUTES,
      'hour' => self::HOURS,
      'hours' => self::HOURS,
      'day' => self::DAYS,
      'days' => self::DAYS,
      'month' => self::MONTHS,
      'months' => self::MONTHS,
      'year' => self::YEARS,
      'years' => self::YEARS
    );

    if (!array_key_exists($unit, $validUnits)) {
      $options = array_keys($validUnits);
      $options = join(', ', $options);
      throw new InvalidArgumentException('The unit must be from this options: ' . $options);
    }

    $unit = $validUnits[$unit];

    return $unit;
  }


  /**
   * @param array $fields Array with years, months, days, hours, minutes and seconds as keys.
   */
  public function modifyDateTime(array $fields)
  {
    $this->dateTime->setDate($fields[self::YEARS], $fields[self::MONTHS], $fields[self::DAYS]);
    $this->dateTime->setTime($fields[self::HOURS], $fields[self::MINUTES], $fields[self::SECONDS]);
  }
}


class InvalidArgumentException extends \InvalidArgumentException {};

class ErrorException extends \ErrorException {};
