<?php
$library = new Media_Model_Library();
$library->setName('Contest')->save();

$icon_paths = array(
    '/contest/contest1.png',
    '/contest/contest2.png',
    '/contest/contest3.png',
    '/contest/contest4.png',
    '/contest/contest5.png'
);

$icon_id = 0;
foreach($icon_paths as $key => $icon_path) {
    $data = array('library_id' => $library->getId(), 'link' => $icon_path, 'can_be_colorized' => 1);
    $image = new Media_Model_Library_Image();
    $image->setData($data)->save();

    if($key == 0) $icon_id = $image->getId();
}

$category = new Application_Model_Option_Category();
$category->find("social", "code");

$data = array(
    'category_id' => $category->getId(),
    'library_id' => $library->getId(),
    'icon_id' => $icon_id,
    'code' => "social_gaming",
    'name' => "Contest",
    'model' => "Socialgaming_Model_Game",
    'desktop_uri' => "socialgaming/application/",
    'mobile_uri' => "socialgaming/mobile_view/",
    'only_once' => 1,
    'is_ajax' => 1,
    'position' => 60
);
$option = new Application_Model_Option();
$option->setData($data)->save();
