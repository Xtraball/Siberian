<?php
/**
 * Siberian\Input reads what comes from outside as the type the code needs, with a default for anything unreadable.
 * What the app sends is often blank, an array or text where a number is expected, and on PHP 8 that is a TypeError or a
 * division by zero in the arithmetic that follows.
 */
require_once dirname(__DIR__, 3) . '/siberian/lib/Siberian/Input.php';

use Siberian\Input;

$junk = [null, [], [1, 2], new stdClass(), '', '   ', 'abc', '12abc', '1e', true, false, NAN, INF, -INF];

return [
    'int reads integers, numeric text and floats towards zero' => function () {
        t_equal(12, Input::int(12));
        t_equal(12, Input::int('12'));
        t_equal(12, Input::int(' 12 '));
        t_equal(-3, Input::int('-3'));
        t_equal(12, Input::int('12.9'));
        t_equal(-12, Input::int(-12.9));
        t_equal(1200, Input::int('1.2e3'));
        t_equal(0, Input::int('0'));
    },
    'int gives the default for everything that is not a number' => function () use ($junk) {
        foreach ($junk as $i => $value) {
            t_equal(0, Input::int($value), "junk $i");
            t_equal(7, Input::int($value, 7), "junk $i with a default");
        }
    },
    'an array is not 1 as a cast would say' => function () {
        t_equal(1, (int) [1, 2], 'the cast');
        t_equal(5, Input::int([1, 2], 5));
    },
    'int is limited to min and max, also the default' => function () {
        t_equal(0, Input::int(-5, 0, 0));
        t_equal(10, Input::int(50, 0, 0, 10));
        t_equal(3, Input::int(3, 0, 0, 10));
        t_equal(1, Input::int('abc', 0, 1), 'a default below the minimum is raised');
        t_equal(PHP_INT_MAX, Input::int('99999999999999999999999'));
        t_equal(PHP_INT_MIN, Input::int('-99999999999999999999999'));
    },
    'float reads numbers and gives the default otherwise' => function () use ($junk) {
        t_equal(1.5, Input::float('1.5'));
        t_equal(3.0, Input::float(3));
        t_equal(-0.25, Input::float(' -0.25 '));
        foreach ($junk as $i => $value) {
            t_equal(2.5, Input::float($value, 2.5), "junk $i");
        }
        t_equal(0.0, Input::float(null));
        t_equal(1.0, Input::float(-4, 0.0, 1.0));
        t_equal(9.5, Input::float(99, 0.0, 0.0, 9.5));
    },
    'a value that is a divisor is never zero when the minimum says so' => function () {
        foreach ([null, '', '0', 0, 'x', []] as $value) {
            $divisor = Input::float($value, 1.0, 1.0);
            t_true($divisor >= 1.0);
            t_equal(10.0, 10 / $divisor);
        }
    },
    'string is a trimmed scalar, empty for the rest' => function () {
        t_equal('abc', Input::string('  abc '));
        t_equal('12', Input::string(12));
        t_equal('1.5', Input::string(1.5));
        t_equal('', Input::string(null));
        t_equal('', Input::string([]));
        t_equal('', Input::string(new stdClass()));
        t_equal('', Input::string(true), 'true is not text');
        t_equal('none', Input::string([], 'none'));
        t_equal('héé', Input::string('hééllo', '', 3), 'characters, not bytes');
        t_equal('', Input::string('abc', '', 0));
    },
    'bool understands the words a form or an app sends' => function () {
        foreach ([true, 1, '1', 'true', 'TRUE', 'on', 'Yes', ' yes ', 2, 0.5] as $v) {
            t_equal(true, Input::bool($v), var_export($v, true));
        }
        foreach ([false, 0, '0', 'false', 'Off', 'no', '', 0.0] as $v) {
            t_equal(false, Input::bool($v), var_export($v, true));
        }
        t_equal(false, Input::bool('maybe'));
        t_equal(true, Input::bool('maybe', true));
        t_equal(true, Input::bool(null, true));
        t_equal(false, Input::bool([], false));
    },
    'enum returns the allowed value, else the default' => function () {
        t_equal('asc', Input::enum('asc', ['asc', 'desc'], 'desc'));
        t_equal('desc', Input::enum('up', ['asc', 'desc'], 'desc'));
        t_equal(2, Input::enum('2', [1, 2, 3], 1), 'the allowed one, with its own type');
        t_equal(null, Input::enum([], ['a']));
        t_equal('a', Input::enum(true, ['a', '1'], 'a'), 'true is not "1"');
    },
    'intList reads an array or a comma list, drops what is not a number, and duplicates' => function () {
        t_equal([1, 2, 3], Input::intList('1,2,3'));
        t_equal([1, 2, 3], Input::intList(['1', 2, ' 3 ']));
        t_equal([4, 5], Input::intList('4,x,,5,4'));
        t_equal([], Input::intList(null));
        t_equal([], Input::intList(7));
        t_equal([2, 3], Input::intList([-1, 0, 2, 3], 1));
    },
    'nothing here raises a notice, a warning or an error' => function () use ($junk) {
        foreach ($junk as $value) {
            Input::int($value);
            Input::float($value);
            Input::string($value);
            Input::bool($value);
            Input::enum($value, ['a']);
            Input::intList($value);
        }
        t_true(true);
    },
];
