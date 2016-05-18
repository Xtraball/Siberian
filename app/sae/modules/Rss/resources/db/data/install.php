<?php
$library = new Media_Model_Library();
$library->setName("RSS Feed")->save();

$icon_paths = array(
    "/rss_feed/rss1.png",
    "/rss_feed/rss2.png",
    "/rss_feed/rss3.png",
    "/newswall/newswall1.png",
    "/newswall/newswall2.png",
    "/newswall/newswall3.png",
    "/newswall/newswall4.png"
);

$icon_id = 0;
foreach($icon_paths as $key => $icon_path) {
    $datas = array("library_id" => $library->getId(), "link" => $icon_path, "can_be_colorized" => 1);
    $image = new Media_Model_Library_Image();
    $image->setData($datas)->save();

    if($key == 0) $icon_id = $image->getId();
}

$category = new Application_Model_Option_Category();
$category->find("media", "code");

$data = array(
    "category_id" => $category->getId(),
    "library_id" => $library->getId(),
    "icon_id" => $icon_id,
    "code" => "rss_feed",
    "name" => "RSS Feed",
    "model" => "Rss_Model_Feed",
    "desktop_uri" => "rss/application_feed/",
    "mobile_uri" => "rss/mobile_feed_list/",
    "mobile_view_uri" => "rss/mobile_feed_view/",
    "mobile_view_uri_parameter" => "feed_id",
    "only_once" => 0,
    "is_ajax" => 1,
    "position" => 80,
    "social_sharing_is_available" => 1
);
$option = new Application_Model_Option();
$option->setData($data)->save();


$layouts = array();

foreach(array(1, 2, 3) as $layout_code) {
    $layouts[] = array(
        "code" => $layout_code,
        "option_id" => $option->getId(),
        "name" => "Layout {$layout_code}",
        "preview" => "/customization/layout/rss-feed/layout-{$layout_code}.png",
        "position" => $layout_code
    );
}

foreach ($layouts as $data) {
    $this->_db->insert("application_option_layout", $data);
}
