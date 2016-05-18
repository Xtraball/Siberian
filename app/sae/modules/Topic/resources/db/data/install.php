<?php
$library = new Media_Model_Library();
$library->setName('Topics')->save();

$icon_paths = array(
    '/topic/topics1.png',
    '/topic/topics2.png',
    '/topic/topics3.png',
    '/topic/topics4.png',
);

$icon_id = 0;
foreach($icon_paths as $key => $icon_path) {
    $data = array('library_id' => $library->getId(), 'link' => $icon_path, 'can_be_colorized' => 1);
    $image = new Media_Model_Library_Image();
    $image->setData($data)->save();

    if($key == 0) $icon_id = $image->getId();
}


$category = new Application_Model_Option_Category();
$category->find("contact", "code");

$data = array(
    'category_id' => $category->getId(),
    'library_id' => $library->getId(),
    'icon_id' => $icon_id,
    'code' => "topic",
    'name' => "Topics",
    'model' => "Topic_Model_Topic",
    'desktop_uri' => "topic/application/",
    'mobile_uri' => "topic/mobile_list/",
    'only_once' => 1,
    'is_ajax' => 1,
    'position' => 241
);
$option = new Application_Model_Option();
$option->setData($data)->save();