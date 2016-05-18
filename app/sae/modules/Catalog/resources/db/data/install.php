<?php
// CATALOG
$library = new Media_Model_Library();
$library->setName('Catalog')->save();

$icon_paths = array(
    '/catalog/catalog1.png',
    '/catalog/catalog2.png',
    '/catalog/catalog3.png',
    '/catalog/catalog4.png',
    '/catalog/catalog5.png',
    '/catalog/catalog6.png',
    '/catalog/catalog7.png',
    '/promotion/discount4.png',
    '/catalog/catalog8.png',
    '/catalog/catalog9.png',
    '/catalog/catalog10.png',
    '/catalog/catalog11.png',
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

$datas = array(
    'category_id' => $category->getId(),
    'library_id' => $library->getId(),
    'icon_id' => $icon_id,
    'code' => 'catalog',
    'name' => 'Catalog',
    'model' => 'Catalog_Model_Category',
    'desktop_uri' => 'catalog/application/',
    'mobile_uri' => 'catalog/mobile_category_list/',
    "mobile_view_uri" => "catalog/mobile_category_product_view/",
    "mobile_view_uri_parameter" => "product_id",
    'only_once' => 0,
    'is_ajax' => 1,
    'position' => 30,
    'social_sharing_is_available' => 1
);
$option = new Application_Model_Option();
$option->setData($datas)->save();
$catalog_value_id = $option->getId();


// SET MEAL
$catalog_option = new Application_Model_Option();
$catalog_option->find('catalog', 'code');
$library = new Media_Model_Library();

if(!$catalog_option->getId()) {

    $library->setName('Set Meal')->save();

    $icon_paths = array(
        '/catalog/catalog1.png',
        '/catalog/catalog2.png',
        '/catalog/catalog3.png',
        '/catalog/catalog4.png',
        '/catalog/catalog5.png',
        '/catalog/catalog6.png',
        '/catalog/catalog7.png',
        '/promotion/discount4.png',
        '/catalog/catalog8.png',
        '/catalog/catalog9.png',
        '/catalog/catalog10.png',
        '/catalog/catalog11.png',
    );

    $icon_id = 0;
    foreach($icon_paths as $key => $icon_path) {
        $datas = array('library_id' => $library->getId(), 'link' => $icon_path, 'can_be_colorized' => 1);
        $image = new Media_Model_Library_Image();
        $image->setData($datas)->save();

        if($key == 0) $icon_id = $image->getId();
    }

} else {
    $library->find($catalog_option->getLibraryId());
    $icons = $library->getIcons();
    $icons->next();
    $icon_id = $icons->current()->getId();
}

$datas = array(
    'category_id' => $category->getId(),
    'library_id' => $library->getId(),
    'icon_id' => $icon_id,
    'code' => 'set_meal',
    'name' => 'Set Meal',
    'model' => 'Catalog_Model_Product',
    'desktop_uri' => 'catalog/application_menu/',
    'mobile_uri' => 'catalog/mobile_setmeal_list/',
    "mobile_view_uri" => "catalog/mobile_setmeal_view/",
    "mobile_view_uri_parameter" => "set_meal_id",
    'only_once' => 0,
    'is_ajax' => 1,
    'position' => 35,
    'social_sharing_is_available' => 1
);

$option = new Application_Model_Option();
$option->setData($datas)->save();
$set_meal_value_id = $option->getId();


$layout_data = array(
    array("value_id" => $catalog_value_id, "image_path" => "/customization/layout/catalog"),
    array("value_id" => $set_meal_value_id, "image_path" => "/customization/layout/set-meal")
);

foreach($layout_data as $data) {

    $layouts = array();
    $option = new Application_Model_Option();
    $option->find($data["value_id"]);

    foreach(array(1, 2, 3) as $layout_code) {
        $layouts[] = array(
            "code" => $layout_code,
            "option_id" => $option->getId(),
            "name" => "Layout {$layout_code}",
            "preview" => "{$data["image_path"]}/layout-{$layout_code}.png",
            "position" => $layout_code
        );
    }

    foreach ($layouts as $data) {
        $this->_db->insert("application_option_layout", $data);
    }

}