<?php
/**
 * #838: a scheduled push shows the date it was sent, not the date it was created.
 * #846: titles and bodies reach the phone without HTML entities.
 * Both rules live in two pure helpers, the controller and the notification builder are checked for using them.
 */
$siberian = dirname(__DIR__, 3) . '/siberian';
$push2 = $siberian . '/app/sae/modules/Push2';
require_once $push2 . '/Model/Onesignal/Schedule.php';
require_once $push2 . '/Model/Onesignal/Text.php';

use Push2\Model\Onesignal\Schedule;
use Push2\Model\Onesignal\Text;

$inUtc = function (callable $fn) {
    $previous = date_default_timezone_get();
    date_default_timezone_set('UTC');
    try {
        return $fn();
    } finally {
        date_default_timezone_set($previous);
    }
};

return [
    '838 a push that is not scheduled keeps its creation date' => function () use ($inUtc) {
        $inUtc(function () {
            t_equal('2024-04-28 10:00:00', Schedule::displayDate('2024-04-28 10:00:00', null, null), 'null');
            t_equal('2024-04-28 10:00:00', Schedule::displayDate('2024-04-28 10:00:00', '', '14:30'), 'empty');
            t_equal(null, Schedule::timestamp(null), 'no timestamp');
        });
    },

    '838 a scheduled push shows the time it was sent' => function () use ($inUtc) {
        $inUtc(function () {
            // 14:30 in the +01:00 zone of the schedule
            t_equal('2023-02-13 13:30:00', Schedule::displayDate('2023-02-01 09:00:00', 'Feb 13 2023 00:00:00 +01:00', '14:30'), 'delivery time of day');
            t_equal(gmmktime(13, 30, 0, 2, 13, 2023), Schedule::timestamp('Feb 13 2023 00:00:00 +01:00', '14:30'), 'timestamp');
            // without a time of day the time of the schedule is kept
            t_equal('2023-02-13 09:00:00', Schedule::displayDate('2023-02-01 09:00:00', 'Feb 13 2023 10:00:00 +01:00', null), 'schedule time');
        });
    },

    '838 an unreadable schedule does not break the list' => function () use ($inUtc) {
        $inUtc(function () {
            t_equal(null, Schedule::timestamp('not a date', '14:30'), 'unparseable');
            t_equal('2024-04-28 10:00:00', Schedule::displayDate('2024-04-28 10:00:00', 'not a date', '14:30'), 'falls back to creation');
        });
    },

    '838 the list skips future pushes and uses the helper for the date' => function () use ($push2) {
        $code = file_get_contents($push2 . '/controllers/Mobile/ListController.php');
        t_true(strpos($code, 'Schedule::timestamp(') !== false, 'timestamp used');
        t_true(strpos($code, '$scheduledAt !== null && $scheduledAt > $now') !== false, 'future pushes are still skipped');
        t_true(strpos($code, 'Schedule::displayDate(') !== false, 'display date used');
        t_true(strpos($code, "datetime_to_format(\$message->getCreatedAt())") === false, 'no raw creation date left');
    },

    '846 entities are decoded for the phone' => function () {
        t_equal('Café & Co', Text::plain('Caf&eacute; &amp; Co'), 'named');
        t_equal("It's", Text::plain('It&#039;s'), 'numeric');
        t_equal('"Hi"', Text::plain('&quot;Hi&quot;'), 'quotes');
    },

    '846 plain text is left alone' => function () {
        foreach (['Hello', 'a & b', '5 < 6', 'Café', '100%', ''] as $text) {
            t_equal($text, Text::plain($text), $text);
        }
        t_equal('', Text::plain(null), 'null');
    },

    '846 only the text sent to OneSignal is decoded' => function () use ($push2) {
        $code = file_get_contents($push2 . '/Model/Onesignal/Notification.php');
        t_true(strpos($code, '$title->setEn(Text::plain($message->getTitle()));') !== false, 'title');
        t_true(strpos($code, '$body->setEn(Text::plain($message->getBody()));') !== false, 'body');
        t_true(strpos($code, "'title' => \$message->getTitle(),") !== false, 'the data payload is unchanged');
    },
];
