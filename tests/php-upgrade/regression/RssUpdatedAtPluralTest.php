<?php
/**
 * Rss_View_Mobile_Feed::getUpdatedAt() appended the unit to the number ("1 day") and then tested "1 day" > 1. PHP 7 read
 * the leading number (1 > 1 is false); PHP 8 compares a non-numeric string with an int as strings, so "1 day" > "1" is
 * true and the app showed "1 days", "1 hours", "1 minutes". The count is now tested before the unit is added.
 */
if (!class_exists('Core_View_Mobile_Default', false)) {
    class Core_View_Mobile_Default
    {
        public function _($text)
        {
            return $text;
        }
    }
}
require_once dirname(__DIR__, 3) . '/siberian/app/sae/modules/Rss/View/Mobile/Feed.php';

function rss_updated_at(int $secondsAgo): string
{
    $entry = new class($secondsAgo) {
        private $ago;

        public function __construct($ago)
        {
            $this->ago = $ago;
        }

        public function getTimestamp()
        {
            return time() - $this->ago;
        }
    };

    return (new Rss_View_Mobile_Feed())->getUpdatedAt($entry);
}

return [
    'one unit is singular' => function () {
        t_equal('1 day', rss_updated_at(86400 + 60), 'one day');
        t_equal('1 hour', rss_updated_at(3600 + 30), 'one hour');
        t_equal('1 minute', rss_updated_at(60 + 5), 'one minute');
    },

    'several units are plural' => function () {
        t_equal('2 days', rss_updated_at(2 * 86400 + 60), 'two days');
        t_equal('5 hours', rss_updated_at(5 * 3600 + 30), 'five hours');
        t_equal('12 minutes', rss_updated_at(12 * 60 + 5), 'twelve minutes');
    },
];
