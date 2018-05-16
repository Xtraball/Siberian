# MomentPHP

MomentPHP is library for parsing, manipulating and formatting dates.
It's inspired by the JavaScript library [Moment.js].


## License

Is freely distributable under the terms of the [GPL-3] license.


## Install

MomentPHP is available on [Packagist], where you can get it via [Composer].

File **composer.json**:
```json
{
    "require": {
        "lawondyss/moment-php": "dev-master"
    }
}
```

Run in command-line:
```sh
php composer.phar install
```


## Parse

### Now

Get the current date and time:
```php
$moment = new MomentPHP\MomentPHP();
```

### String

Create from a string with date time. The string are acceptable same as for function `strtotime` (http://www.php.net/manual/en/function.strtotime.php).
```php
$string = '1980-12-07';
$string = '7 December 1980';
$string = '+1 week 2 days 4 hours 2 seconds';
$string = 'next Thursday';

$moment = new MomentPHP\MomentPHP($string);
```

### Integer

Create from a integer with timestamp. The integer is a number seconds from the Unix Epoch.
```php
$integer = 345061302;
$integer = time();

$moment = new MomentPHP\MomentPHP($integer);
```

### DateTime

Create from a instance of the class DateTime (http://www.php.net/manual/en/class.datetime.php):
```php
$moment = new MomentPHP\MomentPHP(new DateTime());
```

### String + Format

If the string is unrecognized or confusing, you can add its format. The format are same as for method `DateTime::createFromFormat` (http://www.php.net/manual/en/datetime.createfromformat.php).
```php
$string = '1980-07-12';
$format = 'Y-d-m';

$moment = new MomentPHP\MomentPHP($string, $format);
```

The format may be a field with more formats. Then use the first acceptable format.
```php
$string = '1980-12-07';
$format = array('U', 'Y-m-d');

$moment = new MomentPHP\MomentPHP($string, $format);
```

### Set Timezone

The Timezone is set via the third parameter to the constructor. Timezone may be string from a table with [supported Timezones], or instance of the class DateTimeZone (http://www.php.net/manual/en/class.datetimezone.php).
```php
$timezone = 'Europe/Prague';
$timezone = new DateTimeZone('Europe/Prague');

$moment = new MomentPHP\MomentPHP(null, null, $timezone);
```

## Display
For examples we have:
```php
$moment = new MomentPHP\MomentPHP('1980-12-07 19:21:42', null, 'Europe/Prague');
```

### Format()
Return formating date time. Parameter is same as for function `date` (http://www.php.net/manual/en/function.date.php).
```php
var_dump( $moment->format('d/m/Y') ); // string(10) "07/12/1980"
```

### Timestamp()
Return number seconds from the Unix Epoch.
```php
var_dump( $moment->timestamp() ); // int(345061302)
```
### Partials from date time
```php
var_dump( $moment->seconds() ); // string(2) "42"
// it`s same as
var_dump( $moment->second() );

var_dump( $moment->minutes() ); // string(2) "21"
// it's same as
var_dump( $moment->minute() );

var_dump( $moment->hours() ); // string(2) "19"
// it's same as
var_dump( $moment->hour() );

var_dump( $moment->days() ); // string(2) "07"
// it's same as
var_dump( $moment->day() );

var_dump( $moment->months() ); // string(2) "12"
// it's same as
var_dump( $moment->month() );

var_dump( $moment->years ); // string(4) "1980"
// ti's same as
var_dump( $moment->year() );

var_dump( $moment->weeks() ); //string(2) "49"
// it's same as
var_dump( $moment->week() );

// 1 (for Monday) through 7 (for Sunday)
var_dump( $moment->dayOfWeek() ); // string(1) "7"

// starting from 1
var_dump( $moment->dayOfYear() ); // string(3) "342"

var_dump( $moment->nameOfDayShort() ); // string(3) "Sun"

var_dump( $moment->nameOfDayLong() ); // string(6) "Sunday"

var_dump( $moment->dayWithSuffix() ); // string(3) "7th"

var_dump( $moment->daysInMonth() ); // string(2) "31"

var_dump( $moment->nameOfMonthShort() ); // string(3) "Dec"

var_dump( $moment->nameOfMonthLong() ); // string(8) "December"

var_dump( $moment->hourWithSuffix() ); // string(3) "7PM"

var_dump( $moment->isoDate() ); // string(25) "1980-12-07T19:21:42+01:00"

var_dump( $moment->nameOfTimezone() ); // string(13) "Europe/Prague"

var_dump( $moment->timezoneOffset() ); // int(3600)
```

### diff()
Get the difference in seconds.
```php
$momentB = new MomentPHP('2000-01-01 00:00:00', 'Y-m-d H:i:s', 'Europe/Prague');
var_dump( $momentB->diff($moment) ); // int(601619898)
```
To get the difference in another unit of measurement, pass that measurement as the second argument.
Acceptable units: sec, second, seconds, min, minute, minutes, hour, hours, day, days, month, months, year, years
```php
var_dump( $momentB->diff($moment, 'months') ); // int(227)
```
By default return number rounded down. If you want the floating point number, pass `TRUE` as the third argument.
```php
var_dump( $momentB->diff($moment, 'months', true) ); // double(227.81)
```

### from()
A common way of displaying time. This is sometimes called timeago or relative time.
```php
$momentA = new MomentPHP('1980-12-07');
$momentB = new MomentPHP('1980-12-08');

var_dump( $momentA->from($momentB) ); // string(8) "in a day"
var_dump( $momentB->from($momentA) ); // string(9) "a day ago"
```
If you can get the value without the suffix, then set second argument as `TRUE`.
```php
$momentA = new MomentPHP('1980-12-07');
$momentB = new MomentPHP('1980-12-08');

var_dump( $momentA->from($momentB, true) ); // string(5) "a day"
var_dump( $momentB->from($momentA, true) ); // string(5) "a day"
```

### fromNow()
It's equal as `from()`, but starting time is now.
```php
$moment = new MomentPHP;
$moment->add(1, 'day');

var_dump( $moment->fromNow() ); // string(8) "in a day"
```


## Manipulate

### add()
Adds an amount of days, months, years, hours, minutes and seconds. Units is same as for method `diff()`.
```php
$number = 1;
$unit = 'day';
var_dump( $moment->add($number, $unit)->days() ); // string(2) "08"
```
Adds instance of class DateInterval.
```php
$interval = DateInterval('1 day');
var_dump( $moment->add($interval)->days() ); // string(2) "08"
```
Adds array with parameters for loop add(). [unit => number]
```php
$fields = array('days' => 1, 'years' => 1);
var_dump( $moment->add($fields)->format('d|Y') ); // string(7) "08|1981"
```

### sub()
It's equal as add(), bud for subtracts.
```php
$number = 1;
$unit = 'day';
var_dump( $moment->sub($number, $unit)->days() ); // string(2) "06"
```

### startOf()
Mutates the original moment by setting it to the start of a unit of time. Units is same as for `add()`.
```php
var_dump( $moment->isoDate() ); // string(25) "1980-12-07T19:21:42+01:00"

var_dump( $moment->startOf('minutes')->isoDate() ); // string(25) "1980-12-07T19:21:00+01:00"
var_dump( $moment->startOf('hours')->isoDate() ); // string(25) "1980-12-07T19:00:00+01:00"
var_dump( $moment->startOf('days')->isoDate() ); // string(25) "1980-12-07T00:00:00+01:00"
var_dump( $moment->startOf('months')->isoDate() ); // string(25) "1980-12-01T00:00:00+01:00"
var_dump( $moment->startOf('years')->isoDate() ); // string(25) "1980-01-01T00:00:00+01:00"
```

### endOf()
Mutates the original moment by setting it to the end of a unit of time. Units is same as for `add()`.
```php
var_dump( $moment->isoDate() ); // string(25) "1980-12-07T19:21:42+00:00"

var_dump( $moment->endOf('minutes')->isoDate() ); // string(25) "1980-12-07T19:21:59+00:00"
var_dump( $moment->endOf('hours')->isoDate() ); // string(25) "1980-12-07T19:59:59+00:00"
var_dump( $moment->endOf('days')->isoDate() ); // string(25) "1980-12-07T23:59:59+00:00"
var_dump( $moment->endOf('months')->isoDate() ); // string(25) "1980-12-31T23:59:59+00:00"
var_dump( $moment->endOf('years')->isoDate() ); // string(25) "1980-12-31T23:59:59+00:00"
```

## Query

### isLeapYear()
Returns `TRUE` if that year is a leap year, and `FALSE` if it is not.
```php
$moment = new MomentPHP\MomentPHP('2012', 'Y');
var_dump( $moment->isLeapYear() ); // bool(true)

$moment = new MomentPHP\MomentPHP('2013', 'Y');
var_dump( $moment->isLeapYear() ); // bool(false)
```

### isDST()
Checks if the current moment is in daylight savings time.
```php
$moment = new MomentPHP\MomentPHP('06', 'm');
var_dump( $moment->isDST() ); // bool(true)

$moment = new MomentPHP\MomentPHP('12', 'm');
var_dump( $moment->isDST() ); // bool(false)
```

### isBefore()
Check if a moment is before another moment.
```php
var_dump( $moment->isBefore('1980-12-31') ); // bool(true)
```

If set unit, then beginning both dates set on the unit.
```php
var_dump( $moment->isBefore('1980-12-31', 'months') ); // bool(false)
```

### isAfter()
Check if a moment is after another moment. It is the exact opposite method IsBefore().

### isSame()
Check if a moment is the same as another moment.
```php
var_dump( $moment->isSame('1980-12-07') ); // bool(false) because time for compare date is "00:00:00"
var_dump( $moment->startOf('days')->isSame() ); // bool(true) because time for origin date set on "00:00:00"
```

### isMomentPHP()
Check if a variable is a MomentPHP object.
```php
var_dump( $moment->isMomentPHP(new MomentPHP\MomentPHP) ); // bool(true)
var_dump( $moment->isMomentPHP(new DateTime) ); // bool(false)
```



[Moment.js]:http://momentjs.com/
[GPL-3]:https://tldrlegal.com/license/gnu-general-public-license-v3-(gpl-3)
[Packagist]:https://packagist.org/packages/lawondyss/moment-php
[Composer]:http://getcomposer.org/
[supported Timezones]:http://www.php.net/manual/en/timezones.php