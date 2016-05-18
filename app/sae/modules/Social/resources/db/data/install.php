<?php
$library = new Media_Model_Library();
$library->setName('Facebook')->save();

$icon_paths = array(
    '/social_facebook/facebook1.png'
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
    'code' => "facebook",
    'name' => "Facebook",
    'model' => "Social_Model_Facebook",
    'desktop_uri' => "social/application_facebook/",
    'mobile_uri' => "social/mobile_facebook_list/",
    'only_once' => 0,
    'is_ajax' => 1,
    'position' => 210
);
$option = new Application_Model_Option();
$option->setData($data)->save();
