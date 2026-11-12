<?php
/**
 * Siberian\Cron\Human reads a cron expression: the back office cron page shows asNaturalLanguage(), and match(),
 * next() and previous() answer "when". A fixed field (the 20 of "20 4 * * *") never reached the sets those three
 * look at, so a job at a fixed time never matched and next()/previous() returned half-empty dates; a bad
 * expression threw a missing class instead of a message; and next() went back in time when a field wrapped
 * ("5 * * * *" asked at 10:20 gave 10:05).
 */
require_once dirname(__DIR__, 3) . '/siberian/lib/Siberian/Cron/Human.php';

use Siberian\Cron\Human;

date_default_timezone_set('UTC');

/** @return string the exception message of an invalid expression, or '' when it was accepted */
function t_cron_error($spec)
{
    try {
        Human::fromCronString($spec);
    } catch (\Exception $e) {
        return $e->getMessage();
    }

    return '';
}

$friday = strtotime('2024-03-15 10:20:30');

return [
    'the natural language of the usual schedules' => function () {
        $text = function ($spec) {
            return Human::fromCronString($spec)->asNaturalLanguage();
        };
        t_equal('Every minute on every day.', $text('* * * * *'));
        t_equal('At 04:20 on every day.', $text('20 4 * * *'));
        t_equal('At 00:00 on the 1st during every January.', $text('0 0 1 1 *'));
        t_equal('At 08:30 on Mondays, Tuesdays, Wednesdays, Thursdays and Fridays.', $text('30 8 * * 1-5'));
        t_equal('At 10:15 on Mondays.', $text('15 10 * * MON'));
        t_equal('At 00:00 on the 1st during every January and every June.', $text('0 0 1 JAN,JUN *'));
    },
    'a fixed time matches at that minute and no other' => function () {
        $cron = Human::fromCronString('20 4 * * *');
        t_true($cron->match(strtotime('2024-03-15 04:20:00')));
        t_true(!$cron->match(strtotime('2024-03-15 04:21:00')));
        t_true(!$cron->match(strtotime('2024-03-15 05:20:00')));
    },
    'match follows the day of week and the month' => function () {
        $monday = Human::fromCronString('15 10 * * MON');
        t_true($monday->match(strtotime('2024-03-11 10:15:00')));
        t_true(!$monday->match(strtotime('2024-03-12 10:15:00')));

        $january = Human::fromCronString('0 0 1 JAN *');
        t_true($january->match(strtotime('2025-01-01 00:00:00')));
        t_true(!$january->match(strtotime('2025-02-01 00:00:00')));
    },
    'steps and lists match their members only' => function () {
        $cron = Human::fromCronString('*/15 9,17 * * *');
        t_true($cron->match(strtotime('2024-03-15 09:45:00')));
        t_true($cron->match(strtotime('2024-03-15 17:00:00')));
        t_true(!$cron->match(strtotime('2024-03-15 09:50:00')));
        t_true(!$cron->match(strtotime('2024-03-15 10:15:00')));
    },
    'next is the first matching minute after the time, never before it' => function () use ($friday) {
        $next = function ($spec) use ($friday) {
            return Human::fromCronString($spec)->nextAsString($friday);
        };
        t_equal('2024-03-15 10:21:00', $next('* * * * *'));
        t_equal('2024-03-15 10:25:00', $next('*/5 * * * *'));
        t_equal('2024-03-16 04:20:00', $next('20 4 * * *'));
        t_equal('2024-03-15 11:05:00', $next('5 * * * *'), 'the minute wrapped: the hour moves on');
        t_equal('2024-03-15 17:00:00', $next('0 9,17 * * *'));
        t_equal('2024-03-18 08:30:00', $next('30 8 * * 1-5'), 'the weekend is skipped');
        t_equal('2024-03-18 10:15:00', $next('15 10 * * MON'));
        t_equal('2024-06-01 00:00:00', $next('0 0 1 JAN,JUN *'));
        t_equal('2025-01-01 00:00:00', $next('0 0 1 1 *'), 'the year wrapped');
    },
    'next finds the next leap day' => function () use ($friday) {
        t_equal('2028-02-29 00:00:00', Human::fromCronString('0 0 29 2 *')->nextAsString($friday));
    },
    'the time itself is not the next one' => function () {
        $at = strtotime('2024-03-15 04:20:00');
        t_equal('2024-03-16 04:20:00', Human::fromCronString('20 4 * * *')->nextAsString($at));
        t_equal('2024-03-14 04:20:00', Human::fromCronString('20 4 * * *')->previousAsString($at));
    },
    'previous is the last matching minute before the time' => function () use ($friday) {
        $prev = function ($spec) use ($friday) {
            return Human::fromCronString($spec)->previousAsString($friday);
        };
        t_equal('2024-03-15 10:19:00', $prev('* * * * *'));
        t_equal('2024-03-15 10:15:00', $prev('*/5 * * * *'));
        t_equal('2024-03-15 04:20:00', $prev('20 4 * * *'));
        t_equal('2024-03-15 10:05:00', $prev('5 * * * *'));
        t_equal('2024-03-15 09:00:00', $prev('0 9,17 * * *'));
        t_equal('2024-03-11 10:15:00', $prev('15 10 * * MON'));
        t_equal('2024-01-01 00:00:00', $prev('0 0 1 JAN,JUN *'));
        t_equal('2024-02-29 00:00:00', $prev('0 0 29 2 *'));
    },
    'nextAsTime is the timestamp of next' => function () use ($friday) {
        t_equal(strtotime('2024-03-15 10:25:00'), Human::fromCronString('*/5 * * * *')->nextAsTime($friday));
    },
    'the time may be a date string or a date array' => function () {
        $cron = Human::fromCronString('0 12 * * *');
        t_equal('2024-03-15 12:00:00', $cron->nextAsString('2024-03-15 10:00:00'));
        t_equal('2024-03-15 12:00:00', $cron->nextAsString([0, 10, 15, 3, 2024]));
    },
    'nothing is next past the supported years' => function () {
        $cron = Human::fromCronString('0 0 1 1 *');
        t_equal(false, $cron->next(strtotime('2037-06-01 00:00:00')));
        t_equal(false, $cron->nextAsString(strtotime('2037-06-01 00:00:00')));
    },
    'a year can be given as the sixth field' => function () {
        $cron = Human::fromCronString('0 0 1 1 * 2030');
        t_equal('2030-01-01 00:00:00', $cron->nextAsString(strtotime('2024-03-15 10:20:30')));
        t_equal(false, $cron->next(strtotime('2031-01-01 00:00:00')));
    },
    'an invalid expression is refused with a message' => function () {
        t_equal('Invalid specification.', t_cron_error(''));
        t_equal('Invalid specification.', t_cron_error('* * *'));
        t_equal('Out of bounds (minutes).', t_cron_error('61 * * * *'));
        t_equal('Out of bounds (hours).', t_cron_error('* 24 * * *'));
        t_equal('Out of bounds (daysOfMonth).', t_cron_error('* * 0 * *'));
        t_equal('Out of bounds (months).', t_cron_error('* * * 13 *'));
        t_equal('Out of bounds (daysOfWeek).', t_cron_error('* * * * 7'));
        t_equal('Out of bounds (minutes).', t_cron_error('*/0 * * * *'));
        t_equal('Invalid symbol (minutes).', t_cron_error('a * * * *'));
        t_equal('Invalid range (minutes).', t_cron_error('5-1 * * * *'));
    },
    'a valid expression is accepted' => function () {
        t_equal('', t_cron_error('*/5 * * * *'));
        t_equal('', t_cron_error("0\t0  1\n1 *"), 'any run of blanks separates the fields');
    },
];
