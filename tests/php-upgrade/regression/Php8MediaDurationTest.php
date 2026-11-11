<?php
/**
 * The duration of a custom track is a varchar column and was saved as posted. Summing it ($total += "abc") or dividing
 * it (floor("" / 1000)) is a TypeError on PHP 8 (PHP 7 warned and used 0), so one blank duration broke the whole
 * album or gallery. Totals and the formatted duration now ignore what is not a number, and the save stores 0 for it.
 */
if (!class_exists('Core_Model_Default', false)) {
    class Core_Model_Default
    {
        public function __construct($params = [])
        {
        }
    }
}
if (!function_exists('__')) {
    function __($text)
    {
        return $text;
    }
}
if (!function_exists('implode_polyfill')) {
    function implode_polyfill($separator, $parts)
    {
        return implode($separator, $parts);
    }
}
$mediaModels = dirname(__DIR__, 3) . '/siberian/app/sae/modules/Media/Model/Gallery';
require_once $mediaModels . '/Music.php';
require_once $mediaModels . '/Music/Album.php';
require_once $mediaModels . '/Music/Track.php';

function media_tracks(array $durations): array
{
    return array_map(function ($duration) {
        return new class($duration) {
            private $duration;

            public function __construct($duration)
            {
                $this->duration = $duration;
            }

            public function getDuration()
            {
                return $this->duration;
            }
        };
    }, $durations);
}

function media_total(string $class, array $durations): string
{
    $tracks = media_tracks($durations);
    $model = new class($tracks) extends Media_Model_Gallery_Music {
        public $tracks;

        public function __construct($tracks)
        {
            $this->tracks = $tracks;
        }

        public function getAllTracks($without_albums)
        {
            return $this->tracks;
        }
    };
    if ($class === 'album') {
        $model = new class($tracks) extends Media_Model_Gallery_Music_Album {
            public $tracks;

            public function __construct($tracks)
            {
                $this->tracks = $tracks;
            }

            public function getAllTracks($without_albums = false)
            {
                return $this->tracks;
            }
        };
    }
    $result = $model->getTotalDuration();

    return is_array($result) ? implode(':', $result) : (string) $result;
}

return [
    'a gallery and an album add up the numeric durations' => function () {
        foreach (['gallery', 'album'] as $class) {
            $total = media_total($class, ['60000', 90000, '30000']);
            t_equal($class === 'album' ? '03:00' : '3 minutes', $total, "$class: 3 minutes");
        }
    },

    'a blank or text duration counts as zero instead of throwing' => function () {
        foreach (['gallery', 'album'] as $class) {
            $with = media_total($class, ['60000', '', 'abc', null, '60000']);
            $without = media_total($class, ['60000', '60000']);
            t_equal($without, $with, "$class: same total");
        }
    },

    'the formatted duration of a track ignores a text' => function () {
        $track = new class extends Media_Model_Gallery_Music_Track {
            public function getType()
            {
                return 'custom';
            }
        };

        t_equal('03:02', $track->getFormatedDuration(182000), 'milliseconds');
        t_equal('03:02', $track->getFormatedDuration('182000'), 'numeric string');
        foreach (['abc', null, false] as $text) {
            t_equal('', (string) $track->getFormatedDuration($text), 'nothing for ' . var_export($text, true));
        }
    },

    'a custom track is saved with a numeric duration or 0' => function () {
        $source = file_get_contents(dirname(__DIR__, 3) . '/siberian/app/sae/modules/Media/controllers/Application/Gallery/MusicController.php');

        t_true(strpos($source, "->setDuration(is_numeric(\$track['duration'] ?? null) ? \$track['duration'] : 0)") !== false, 'sanitised on save');
    },
];
