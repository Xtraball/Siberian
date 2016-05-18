<?php
// Image Gallery
$library = new Media_Model_Library();
$library->setName('Images')->save();

$icon_paths = array(
    '/images/image1.png',
    '/images/image2.png',
    '/images/image3.png',
    '/images/image4.png',
    '/images/image5.png',
    '/images/image6.png',
    '/images/image7.png'
);

$icon_id = 0;
foreach($icon_paths as $key => $icon_path) {
    $datas = array('library_id' => $library->getId(), 'link' => $icon_path, 'can_be_colorized' => 1);
    $image = new Media_Model_Library_Image();
    $image->setData($datas)->save();

    if($key == 0) $icon_id = $image->getId();
}

$category = new Application_Model_Option_Category();
$category->find("media", "code");

$data = array(
    'category_id' => $category->getId(),
    'library_id' => $library->getId(),
    'icon_id' => $icon_id,
    'code' => "image_gallery",
    'name' => "Images",
    'model' => "Media_Model_Gallery_Image",
    'desktop_uri' => "media/application_gallery_image/",
    'mobile_uri' => "media/mobile_gallery_image_list/",
    "mobile_view_uri" => "media/mobile_gallery_image_view/",
    "mobile_view_uri_parameter" => "gallery_id,offset/0",
    'only_once' => 0,
    'is_ajax' => 1,
    'position' => 90
);
$option = new Application_Model_Option();
$option->setData($data)->save();


// Videos Gallery
$library = new Media_Model_Library();
$library->setName('Videos')->save();

$icon_paths = array(
    '/videos/video1.png',
    '/videos/video2.png',
    '/videos/video3.png',
    '/videos/video4.png',
    '/videos/video5.png',
    '/videos/video6.png'
);

$icon_id = 0;
foreach($icon_paths as $key => $icon_path) {
    $datas = array('library_id' => $library->getId(), 'link' => $icon_path, 'can_be_colorized' => 1);
    $image = new Media_Model_Library_Image();
    $image->setData($datas)->save();

    if($key == 0) $icon_id = $image->getId();
}

$data = array(
    'category_id' => $category->getId(),
    'library_id' => $library->getId(),
    'icon_id' => $icon_id,
    'code' => "video_gallery",
    'name' => "Videos",
    'model' => "Media_Model_Gallery_Video",
    'desktop_uri' => "media/application_gallery_video/",
    'mobile_uri' => "media/mobile_gallery_video_list/",
    "mobile_view_uri" => "media/mobile_gallery_video_view/",
    "mobile_view_uri_parameter" => "gallery_id,offset/1",
    'only_once' => 0,
    'is_ajax' => 1,
    'position' => 100
);
$option = new Application_Model_Option();
$option->setData($data)->save();


// Music Gallery
$library = new Media_Model_Library();
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
    $datas = array('library_id' => $library->getId(), 'link' => $icon_path, 'can_be_colorized' => 1);
    $image = new Media_Model_Library_Image();
    $image->setData($datas)->save();

    if($key == 0) $icon_id = $image->getId();
}

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
$option->setData($data)->save();