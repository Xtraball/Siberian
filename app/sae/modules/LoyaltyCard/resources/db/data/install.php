<?php
$library = new Media_Model_Library();
$library->setName('Loyalty Card')->save();

$icon_paths = array(
    '/loyalty/loyalty1.png',
    '/loyalty/loyalty2.png',
    '/loyalty/loyalty3.png',
    '/loyalty/loyalty4.png',
    '/loyalty/loyalty5.png',
    '/loyalty/loyalty6.png'
);

$icon_id = 0;
foreach($icon_paths as $key => $icon_path) {
    $datas = array('library_id' => $library->getId(), 'link' => $icon_path, 'can_be_colorized' => 1);
    $image = new Media_Model_Library_Image();
    $image->setData($datas)->save();

    if($key == 0) $icon_id = $image->getId();
}


$category = new Application_Model_Option_Category();
$category->find("monetization", "code");

$data = array(
    'category_id' => $category->getId(),
    'library_id' => $library->getId(),
    'icon_id' => $icon_id,
    'code' => "loyalty",
    'name' => "Loyalty Card",
    'model' => "LoyaltyCard_Model_LoyaltyCard",
    'desktop_uri' => "loyaltycard/application/",
    'mobile_uri' => "loyaltycard/mobile_view/",
    'only_once' => 0,
    'is_ajax' => 1,
    'position' => 50
);
$option = new Application_Model_Option();
$option->setData($data)->save();
