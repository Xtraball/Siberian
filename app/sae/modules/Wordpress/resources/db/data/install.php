<?php
$library = new Media_Model_Library();
$library->setName('Wordpress')->save();

$icon_paths = array(
    '/wordpress/wordpress1.png'
);

$icon_id = 0;
foreach($icon_paths as $key => $icon_path) {
    $datas = array('library_id' => $library->getId(), 'link' => $icon_path, 'can_be_colorized' => 1);
    $image = new Media_Model_Library_Image();
    $image->setData($datas)->save();

    if($key == 0) $icon_id = $image->getId();
}


$category = new Application_Model_Option_Category();
$category->find("integration", "code");

$data = array(
    'category_id' => $category->getId(),
    'library_id' => $library->getId(),
    'icon_id' => $icon_id,
    'code' => "wordpress",
    'name' => "Wordpress",
    'model' => "Wordpress_Model_Wordpress",
    'desktop_uri' => "wordpress/application/",
    'mobile_uri' => "wordpress/mobile_list/",
    'only_once' => 0,
    'is_ajax' => 1,
    'position' => 170,
    'social_sharing_is_available' => 1
);
$option = new Application_Model_Option();
$option->setData($data)->save();


$layouts = array();

foreach(array(1, 2, 3) as $layout_code) {
    $layouts[] = array(
        "code" => $layout_code,
        "option_id" => $option->getId(),
        "name" => "Layout {$layout_code}",
        "preview" => "/customization/layout/wordpress/layout-{$layout_code}.png",
        "position" => $layout_code
    );
}

foreach ($layouts as $data) {
    $this->_db->insert("application_option_layout", $data);
}