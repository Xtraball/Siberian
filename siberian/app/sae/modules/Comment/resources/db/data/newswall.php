<?php

// ==================================
// ==================================
// Do not install anymore NewsWall
// ==================================
// ==================================

$option = (new Application_Model_Option())
    ->find("newswall", "code");
if ($option->getId()) {
    $name = "Newswall";
    $category = "social";

    // Install icons!
    $icons = [
        "/newswall/newswall1.png",
        "/newswall/newswall2.png",
        "/newswall/newswall3.png",
        "/newswall/newswall4.png"
    ];

    $result = Siberian_Feature::installIcons($name, $icons);

    // Install the Feature!
    $data = [
        'library_id' => $result["library_id"],
        'icon_id' => $result["icon_id"],
        "code" => "newswall",
        "name" => $name,
        "model" => "Comment_Model_Comment",
        "desktop_uri" => "comment/application/",
        "mobile_uri" => "comment/mobile_list/",
        "mobile_view_uri" => "comment/mobile_view/",
        "mobile_view_uri_parameter" => "comment_id",
        "only_once" => 0,
        "is_ajax" => 1,
        "position" => 10,
        "social_sharing_is_available" => 1,
        "use_my_account" => 1,
        'backoffice_description' => 'This feature is disabled by default since update 4.17.0 and is deprecated in favor of Social Wall.'
    ];

    $option = Siberian_Feature::install($category, $data, ['code']);

    // Layouts!
    $layout_data = [1, 2, 3, 4];
    $slug = "newswall";

    Siberian_Feature::installLayouts($option->getId(), $slug, $layout_data);

    // Icons Flat!
    $icons = [
        "/newswall/newswall1-flat.png",
        "/newswall/newswall2-flat.png",
        "/newswall/newswall3-flat.png",
    ];

    Siberian_Feature::installIcons("{$name}-flat", $icons);


    // Disable only not done yet!
    // deprecated from version 4.12.24
    if (__get('newswall_v1_deprecated') !== 'done') {
        $option
            ->setIsEnabled(0)
            ->save();

        __set('newswall_v1_deprecated', 'done');
    }
}