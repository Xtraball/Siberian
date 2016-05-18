<?php
$library = new Media_Model_Library();
$library->setName("Radio")->save();

$images = array(
    '/radio/radio1.png',
    '/radio/radio2.png',
    '/radio/radio3.png',
    '/radio/radio4.png',
    '/radio/radio5.png',
    '/radio/radio6.png',
    '/radio/radio7.png',
    '/radio/radio8.png'
);

$icon_id = 0;
foreach($images as $key => $image) {
    $data = array('library_id' => $library->getId(), 'link' => $image, 'can_be_colorized' => 1);
    $image = new Media_Model_Library_Image();
    $image->setData($data)->save();
    if($key == 0) $icon_id = $image->getId();
}


$category = new Application_Model_Option_Category();
$category->find("media", "code");

$data = array(
    'category_id' => $category->getId(),
    'library_id' => $library->getId(),
    'icon_id' => $icon_id,
    'code' => "radio",
    'name' => "Radio",
    'model' => "Radio_Model_Radio",
    'desktop_uri' => "radio/application/",
    'mobile_uri' => "radio/mobile_radio/",
    'only_once' => 0,
    'is_ajax' => 1,
    'position' => 105
);
$option = new Application_Model_Option();
$option->setData($data)->save();
