<?php
$name = "Audio";
$category = "media";

# Install icons
$icons = array(
    '/musics/music1.png',
    '/musics/music2.png',
    '/musics/music3.png',
    '/musics/music4.png',
    '/musics/music5.png',
    '/musics/music6.png',
    '/musics/music7.png',
    '/musics/music8.png',
    '/musics/music9.png'
);

$result = Siberian_Feature::installIcons($name, $icons);

# Install the Feature
$data = array(
    'library_id'                    => $result["library_id"],
    'icon_id'                       => $result["icon_id"],
    'code'                          => 'music_gallery',
    'name'                          => $name,
    'model'                         => 'Media_Model_Gallery_Music',
    'desktop_uri'                   => 'media/application_gallery_music/',
    'mobile_uri'                    => 'media/mobile_gallery_music_playlists/',
    "mobile_view_uri"               => "media/mobile_api_music_playlist/",
    "mobile_view_uri_parameter"     => "playlist_id",
    'only_once'                     => 0,
    'is_ajax'                       => 1,
    'position'                      => 110
);

$option = Siberian_Feature::install($category, $data, array('code'));

# Icons Flat
$icons = array(
    '/musics/music1-flat.png',
    '/musics/music2-flat.png',
    '/musics/music3-flat.png',
);

Siberian_Feature::installIcons("{$name}-flat", $icons);
