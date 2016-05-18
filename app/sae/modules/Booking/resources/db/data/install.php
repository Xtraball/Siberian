<?php
$library = new Media_Model_Library();
$library->setName('Booking')->save();

$icon_paths = array(
    'booking1.png',
    'booking2.png',
    'booking3.png',
    'booking4.png',
    'booking5.png',
    'booking6.png',
    'booking7.png',
    'booking8.png',
    'booking9.png',
    'booking10.png',
    'booking11.png'
);

$icon_id = 0;
foreach($icon_paths as $key => $icon_path) {
    $datas = array('library_id' => $library->getId(), 'link' => '/booking/'.$icon_path, 'can_be_colorized' => 1);
    $image = new Media_Model_Library_Image();
    $image->setData($datas)->save();

    if($key == 0) $icon_id = $image->getId();
}

$category = new Application_Model_Option_Category();
$category->find("contact", "code");

$data = array(
    'category_id' => $category->getId(),
    'library_id' => $library->getId(),
    'icon_id' => $icon_id,
    'code' => 'booking',
    'name' => 'Booking',
    'model' => 'Booking_Model_Booking',
    'desktop_uri' => 'booking/application/',
    'mobile_uri' => 'booking/mobile_view/',
    "mobile_view_uri" => "booking/mobile_view/",
    "mobile_view_uri_parameter" => null,
    'only_once' => 0,
    'is_ajax' => 1,
    'position' => 140
);
$option = new Application_Model_Option();
$option->setData($data)->save();
