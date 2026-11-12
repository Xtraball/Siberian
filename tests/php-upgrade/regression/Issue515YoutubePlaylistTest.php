<?php
/**
 * #515: the YouTube video gallery source only supported "channel", "search" and "user" - no way to point it at a
 * playlist. The DB schema's type enum already listed "playlist" (and "favorite") unused, so this only needed a
 * new $_flux entry (the admin form's <select> is generated from it) and a fetchYoutube() branch calling
 * playlistItems directly with the playlist id, same shape as the tail of the existing "user" case.
 */
$siberian = dirname(__DIR__, 3) . '/siberian';

return [
    '515 playlist is an available youtube gallery source type' => function () use ($siberian) {
        $model = (string) file_get_contents(
            $siberian . '/app/sae/modules/Media/Model/Gallery/Video/Youtube.php'
        );
        t_true(
            (bool) preg_match('/\$_flux\s*=\s*array\([^)]*"playlist"[^)]*\)/', $model),
            '_flux (what populates the admin form select) includes "playlist"'
        );
    },
    '515 fetchYoutube has a playlist branch that calls playlistItems with the given id' => function () use ($siberian) {
        $controller = (string) file_get_contents(
            $siberian . '/app/sae/modules/Media/controllers/Mobile/Gallery/Video/ListController.php'
        );
        $branch = strpos($controller, "case 'playlist':");
        t_true($branch !== false, 'a playlist case exists in the fetchYoutube switch');

        $slice = substr($controller, $branch, 500);
        t_true(
            strpos($slice, 'youtube/v3/playlistItems/') !== false,
            'it calls the playlistItems endpoint directly, no channel/user lookup step'
        );
        t_true(
            strpos($slice, "'playlistId' => \$keyword") !== false,
            'the playlist id is taken straight from the configured keyword'
        );
    },
    '515 the schema enum already covers "playlist", no migration needed' => function () use ($siberian) {
        $schema = (string) file_get_contents(
            $siberian . '/app/sae/modules/Media/resources/db/schema/media_gallery_video_youtube.php'
        );
        t_true(strpos($schema, 'playlist') !== false, 'type enum lists playlist');
    },
];
