<?php

// ==================================
// ==================================
// Do not install anymore FanWall
// ==================================
// ==================================

$option = (new Application_Model_Option())
    ->find("fanwall", "code");
if ($option->getId()) {

    $name = "Fan Wall";
    $category = "social";

    // Install icons!
    $icons = [
        "/fanwall/fanwall1.png",
        "/fanwall/fanwall2.png",
        "/fanwall/fanwall3.png",
        "/fanwall/fanwall4.png"
    ];

    $result = Siberian_Feature::installIcons($name, $icons);

    // Install the Feature!
    $data = [
        'library_id' => $result["library_id"],
        'icon_id' => $result["icon_id"],
        "code" => "fanwall",
        "name" => $name,
        "model" => "Comment_Model_Comment",
        "desktop_uri" => "comment/application/",
        "mobile_uri" => "comment/mobile_list/",
        "mobile_view_uri" => "comment/mobile_view/",
        "mobile_view_uri_parameter" => "comment_id",
        "only_once" => 0,
        "is_ajax" => 1,
        "position" => 15,
        "social_sharing_is_available" => 1,
        "use_my_account" => 1,
        'backoffice_description' => 'This feature is disabled by default since update 4.17.0 and is deprecated in favor of Social Wall.'
    ];

    $option = Siberian_Feature::install($category, $data, ['code']);

    // Layouts!
    $layout_data = [1];
    $slug = "newswall";

    Siberian_Feature::installLayouts($option->getId(), $slug, $layout_data);

    // Icons Flat!
    $icons = [
        "/fanwall/fanwall1-flat.png",
        "/fanwall/fanwall2-flat.png",
        "/fanwall/fanwall3-flat.png",
    ];

    Siberian_Feature::installIcons("{$name}-flat", $icons);

    // Disable only not done yet!
    // deprecated from version 4.12.24
    if (__get('fanwall_v1_deprecated') !== 'done') {
        $option
            ->setIsEnabled(0)
            ->save();

        __set('fanwall_v1_deprecated', 'done');
    }
}