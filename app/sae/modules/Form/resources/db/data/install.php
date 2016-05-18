<?php
$library = new Media_Model_Library();
$library->setName('Form')->save();

$icon_paths = array(
    '/form/form1.png',
    '/form/form2.png',
    '/form/form3.png',
    '/calendar/calendar1.png',
    '/catalog/catalog6.png',
);

$icon_id = 0;
foreach($icon_paths as $key => $icon_path) {
    $datas = array('library_id' => $library->getId(), 'link' => $icon_path, 'can_be_colorized' => 1);
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
    'code' => "form",
    'name' => "Form",
    'model' => "Form_Model_Form",
    'desktop_uri' => "form/application/",
    'mobile_uri' => "form/mobile_view/",
    'only_once' => 0,
    'is_ajax' => 1,
    'position' => 190
);
$option = new Application_Model_Option();
$option->setData($data)->save();
