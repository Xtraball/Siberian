<?php

// NEWSWALL
$library = new Media_Model_Library();
$library->setName("Newswall")->save();

$icon_paths = array(
    "/newswall/newswall1.png",
    "/newswall/newswall2.png",
    "/newswall/newswall3.png",
    "/newswall/newswall4.png"
);

$icon_id = 0;
foreach ($icon_paths as $key => $icon_path) {
    $datas = array("library_id" => $library->getId(), "link" => $icon_path, "can_be_colorized" => 1);
    $image = new Media_Model_Library_Image();
    $image->setData($datas)->save();

    if ($key == 0) $icon_id = $image->getId();
}

$category = new Application_Model_Option_Category();
$category->find("social", "code");

$data = array(
    "category_id" => $category->getId(),
    "library_id" => $library->getId(),
    "icon_id" => $icon_id,
    "code" => "newswall",
    "name" => "Newswall",
    "model" => "Comment_Model_Comment",
    "desktop_uri" => "comment/application/",
    "mobile_uri" => "comment/mobile_list/",
    "mobile_view_uri" => "comment/mobile_view/",
    "mobile_view_uri_parameter" => "comment_id",
    "only_once" => 0,
    "is_ajax" => 1,
    "position" => 10,
    "social_sharing_is_available" => 1
);
$option = new Application_Model_Option();
$option->setData($data)->save();

$layouts = array(
    array(
        "code" => 1,
        "option_id" => $option->getId(),
        "name" => "Layout 1",
        "preview" => "/customization/layout/newswall/layout-1.png",
        "position" => 1
    ), array(
        "code" => 2,
        "option_id" => $option->getId(),
        "name" => "Layout 2",
        "preview" => "/customization/layout/newswall/layout-2.png",
        "position" => 2
    ));

foreach ($layouts as $data) {
    $this->_db->insert("application_option_layout", $data);
}


// FANWALL
$library = new Media_Model_Library();
$library->setName("Fanwall")->save();

$icon_paths = array(
    "/fanwall/fanwall1.png",
    "/fanwall/fanwall2.png",
    "/fanwall/fanwall3.png",
    "/fanwall/fanwall4.png"
);

$icon_id = 0;
foreach ($icon_paths as $key => $icon_path) {
    $datas = array("library_id" => $library->getId(), "link" => $icon_path, "can_be_colorized" => 1);
    $image = new Media_Model_Library_Image();
    $image->setData($datas)->save();

    if ($key == 0) $icon_id = $image->getId();
}


$data = array(
    "category_id" => $category->getId(),
    "library_id" => $library->getId(),
    "icon_id" => $icon_id,
    "code" => "fanwall",
    "name" => "Fan Wall",
    "model" => "Comment_Model_Comment",
    "desktop_uri" => "comment/application/",
    "mobile_uri" => "comment/mobile_list/",
    "mobile_view_uri" => "comment/mobile_view/",
    "mobile_view_uri_parameter" => "comment_id",
    "only_once" => 0,
    "is_ajax" => 1,
    "position" => 15,
    "social_sharing_is_available" => 1
);
$option = new Application_Model_Option();
$option->setData($data)->save();


$layouts = array();

foreach (array(3, 4) as $layout_code) {
    $layouts[] = array(
        "code" => $layout_code,
        "option_id" => $option->getId(),
        "name" => "Layout {$layout_code}",
        "preview" => "/customization/layout/newswall/layout-{$layout_code}.png",
        "position" => $layout_code
    );
}

foreach ($layouts as $data) {
    $this->_db->insert("application_option_layout", $data);
}

$newswall_option = new Application_Model_Option();
$fanwall_option = new Application_Model_Option();

$newswall_option->find("newswall", "code");
$fanwall_option->find("fanwall", "code");

if ($newswall_option->getId() AND $fanwall_option->getId()) {
    foreach ($fanwall_option->getLayouts() as $layout) {
        if (in_array($layout->getCode(), array(3, 4))) {
            $layout->setOptionId($newswall_option->getId())->save();
        }
    }
}

$fanwall_option = new Application_Model_Option();
$fanwall_option->find("fanwall", "code");

$this->_db->update("application_option_value", array("layout_id" => 1), array("option_id = ?" => $fanwall_option->getId()));
