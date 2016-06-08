<?php
// Music Gallery
$library = new Media_Model_Library();
$library->find("Musics", "name");
$library->setName('Musics')->save();

$icon_paths = array(
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

$icon_id = 0;
foreach($icon_paths as $key => $icon_path) {
    $datas = array(
        'library_id' => $library->getId(),
        'link' => $icon_path,
        'can_be_colorized' => 1,
    );
    $image = new Media_Model_Library_Image();
    $image->find($icon_path, "link");
    $image
        ->setData($datas)
        ->save();

    if($key == 0) {
        $icon_id = $image->getId();
    }
}

$category = new Application_Model_Option_Category();
$category->find("media", "code");

$data = array(
    'category_id' => $category->getId(),
    'library_id' => $library->getId(),
    'icon_id' => $icon_id,
    'code' => 'music_gallery',
    'name' => 'Audio',
    'model' => 'Media_Model_Gallery_Music',
    'desktop_uri' => 'media/application_gallery_music/',
    'mobile_uri' => 'media/mobile_gallery_music_playlists/',
    "mobile_view_uri" => "media/mobile_api_music_playlist/",
    "mobile_view_uri_parameter" => "playlist_id",
    'only_once' => 0,
    'is_ajax' => 1,
    'position' => 110
);
$option = new Application_Model_Option();
$option->find("music_gallery", "code");
$option
    ->setData($data)
    ->save();