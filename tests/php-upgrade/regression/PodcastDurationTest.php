<?php
/**
 * Media_Model_Gallery_Music_Type_Podcast::_calcDuration() did list($h, $m, $s) = explode(":", $duration): "3600" (plain
 * seconds, very common in feeds) was read as 3600 hours, "12:30" as 12 hours 30 minutes, and a text such as "abc" or ""
 * threw a TypeError on PHP 8 ("abc" * 3600) that the try/catch around it could not catch.
 */
if (!class_exists('Core_Model_Default', false)) {
    class Core_Model_Default
    {
        public function __construct($params = [])
        {
        }
    }
}
require_once dirname(__DIR__, 3) . '/siberian/app/sae/modules/Media/Model/Gallery/Music/Type/Podcast.php';

function podcast_ms($duration)
{
    $method = new ReflectionMethod(Media_Model_Gallery_Music_Type_Podcast::class, '_calcDuration');
    $method->setAccessible(true);

    return $method->invoke(new Media_Model_Gallery_Music_Type_Podcast(), $duration);
}

return [
    'the three iTunes formats give milliseconds' => function () {
        t_equal(3723000, podcast_ms('01:02:03'), 'h:m:s');
        t_equal(750000, podcast_ms('12:30'), 'm:s');
        t_equal(3600000, podcast_ms('3600'), 'plain seconds');
        t_equal(90500, podcast_ms('90.5'), 'decimal seconds');
    },

    'a text or an empty value gives null, not a TypeError' => function () {
        foreach (['abc', '', '1h 5m', '1:2:3:4', '-5', 'a:30'] as $value) {
            t_true(podcast_ms($value) === null, 'null for ' . var_export($value, true));
        }
    },
];
