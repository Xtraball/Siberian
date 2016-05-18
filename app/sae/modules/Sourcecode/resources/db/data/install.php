<?php
$library = new Media_Model_Library();
$library->setName('Source code')->save();

$icon_paths = array(
    '/source_code/source_code.png',
    '/custom_page/custom1.png',
    '/newswall/newswall2.png',
    '/catalog/catalog6.png',
    '/booking/booking4.png'
);

$icon_id = 0;
foreach($icon_paths as $key => $icon_path) {
    $datas = array('library_id' => $library->getId(), 'link' => $icon_path, 'can_be_colorized' => 1);
    $image = new Media_Model_Library_Image();
    $image->setData($datas)->save();

    if($key == 0) $icon_id = $image->getId();
}


$category = new Application_Model_Option_Category();
$category->find("customization", "code");

$data = array(
    'category_id' => $category->getId(),
    'library_id' => $library->getId(),
    'icon_id' => $icon_id,
    'code' => 'source_code',
    'name' => 'Source code',
    'model' => 'Sourcecode_Model_Sourcecode',
    'desktop_uri' => 'sourcecode/application/',
    'mobile_uri' => 'sourcecode/mobile_view/',
    'only_once' => 0,
    'is_ajax' => 1,
    'position' => 75
);
$option = new Application_Model_Option();
$option->setData($data)->save();
