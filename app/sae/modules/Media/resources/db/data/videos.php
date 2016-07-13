<?php
$name = "Videos";
$category = "media";

# Install icons
$icons = array(
    '/videos/video1.png',
    '/videos/video2.png',
    '/videos/video3.png',
    '/videos/video4.png',
    '/videos/video5.png',
    '/videos/video6.png'
);

$result = Siberian_Feature::installIcons($name, $icons);

# Install the Feature
$data = array(
    'library_id'                    => $result["library_id"],
    'icon_id'                       => $result["icon_id"],
    'code'                          => "video_gallery",
    'name'                          => "Videos",
    'model'                         => "Media_Model_Gallery_Video",
    'desktop_uri'                   => "media/application_gallery_video/",
    'mobile_uri'                    => "media/mobile_gallery_video_list/",
    "mobile_view_uri"               => "media/mobile_gallery_video_view/",
    "mobile_view_uri_parameter"     => "gallery_id,offset/1",
    'only_once'                     => 0,
    'is_ajax'                       => 1,
    'position'                      => 100
);

$option = Siberian_Feature::install($category, $data, array('code'));

# Icons Flat
$icons = array(
    '/videos/video1-flat.png',
    '/videos/video2-flat.png',
    '/videos/video3-flat.png',
);

Siberian_Feature::installIcons("{$name}-flat", $icons);