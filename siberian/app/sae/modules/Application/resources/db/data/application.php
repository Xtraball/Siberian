<?php

$icons = array(
    '/tabbar/user_account.png',
    '/tabbar/user_account-flat.png',
    '/tabbar/user_account1-flat.png',
    '/tabbar/user_account2-flat.png',
);
Siberian_Feature::installIcons("customer_account", $icons);

$icons = array(
    '/tabbar/more_items.png',
    '/tabbar/more_items-flat.png',
);
Siberian_Feature::installIcons("more_items", $icons);


/** Categories for Layouts */
$layout_categories = array(
    array(
        "code" => "default",
        "name" => "Default",
    ),
    array(
        "code" => "custom",
        "name" => "Custom",
    )
);

foreach($layout_categories as $category_data) {
    $layout_category = new Application_Model_Layout_Category();
    $layout_category
        ->setData($category_data)
        ->insertOnce(array("code"));
}

$layout_category = new Application_Model_Layout_Category();
$default_layout_category = $layout_category->find("default", "code");

$datas = array(
    array(
        'name' => 'Layout 1',
        'category_id' => $default_layout_category->getId(),
        'visibility' => Application_Model_Layout_Homepage::VISIBILITY_ALWAYS,
        'code' => 'layout_1',
        'preview' => '/customization/layout/homepage/layout_1.png',
        'preview_new' => '/customization/layout/homepage/layout_1_modal.png',
        'use_more_button' => 1,
        'use_horizontal_scroll' => 0,
        'number_of_displayed_icons' => 5,
        'position' => "bottom",
        "order" => 10,
        "is_active" => 1,
        "use_homepage_slider" => 1,
        "options" => Siberian_Json::encode(array(
            "shadow" => "shadow",
        )),
    ),
    array(
        'name' => 'Layout 2',
        'category_id' => $default_layout_category->getId(),
        'visibility' => Application_Model_Layout_Homepage::VISIBILITY_HOMEPAGE,
        'code' => 'layout_2',
        'preview' => '/customization/layout/homepage/layout_2.png',
        'preview_new' => '/customization/layout/homepage/layout_2_modal.png',
        'use_more_button' => 1,
        'use_horizontal_scroll' => 0,
        'number_of_displayed_icons' => 10,
        'position' => "bottom",
        "order" => 20,
        "is_active" => 1,
        "use_homepage_slider" => 1,
        "options" => Siberian_Json::encode(array(
            "shadow" => "shadow",
        )),
    ),
    array(
        'name' => 'Layout 3',
        'category_id' => $default_layout_category->getId(),
        'visibility' => Application_Model_Layout_Homepage::VISIBILITY_HOMEPAGE,
        'code' => 'layout_3',
        'preview' => '/customization/layout/homepage/layout_3.png',
        'preview_new' => '/customization/layout/homepage/layout_3_modal.png',
        'use_more_button' => 0,
        'use_horizontal_scroll' => 0,
        'number_of_displayed_icons' => null,
        'position' => "bottom",
        "order" => 30,
        "is_active" => 1,
        "use_homepage_slider" => 1,
        "options" => Siberian_Json::encode(array(
            "title" => "titlevisible",
        )),
    ),
    array(
        'name' => 'Layout 4',
        'category_id' => $default_layout_category->getId(),
        'visibility' => Application_Model_Layout_Homepage::VISIBILITY_HOMEPAGE,
        'code' => 'layout_4',
        'preview' => '/customization/layout/homepage/layout_4.png',
        'preview_new' => '/customization/layout/homepage/layout_4_modal.png',
        'use_more_button' => 0,
        'use_horizontal_scroll' => 0,
        'number_of_displayed_icons' => null,
        'position' => "bottom",
        "order" => 40,
        "is_active" => 1,
        "use_homepage_slider" => 1,
        "options" => Siberian_Json::encode(array(
            "title" => "titlevisible",
        )),
    ),
    array(
        'name' => 'Layout 5',
        'category_id' => $default_layout_category->getId(),
        'visibility' => Application_Model_Layout_Homepage::VISIBILITY_HOMEPAGE,
        'code' => 'layout_5',
        'preview' => '/customization/layout/homepage/layout_5.png',
        'preview_new' => '/customization/layout/homepage/layout_5_modal.png',
        'use_more_button' => 0,
        'use_horizontal_scroll' => 0,
        'number_of_displayed_icons' => null,
        'position' => "bottom",
        "order" => 50,
        "is_active" => 1,
        "use_homepage_slider" => 1,
        "options" => Siberian_Json::encode(array(
            "textTransform" => "title-lowcase",
            "title" => "titlevisible",
        )),
    ),
    array(
        'name' => 'Layout 6',
        'category_id' => $default_layout_category->getId(),
        'visibility' => Application_Model_Layout_Homepage::VISIBILITY_HOMEPAGE,
        'code' => 'layout_6',
        'preview' => '/customization/layout/homepage/layout_6.png',
        'preview_new' => '/customization/layout/homepage/layout_6_modal.png',
        'use_more_button' => 0,
        'use_horizontal_scroll' => 0,
        'number_of_displayed_icons' => null,
        'position' => "bottom",
        "order" => 60,
        "is_active" => 1,
        "use_homepage_slider" => 1,
        "options" => Siberian_Json::encode(array(
            "label" => "label-left",
            "textTransform" => "title-lowcase",
        )),
    ),
    array(
        'name' => 'Layout 7',
        'category_id' => $default_layout_category->getId(),
        'visibility' => Application_Model_Layout_Homepage::VISIBILITY_HOMEPAGE,
        'code' => 'layout_7',
        'preview' => '/customization/layout/homepage/layout_7.png',
        'preview_new' => '/customization/layout/homepage/layout_7_modal.png',
        'use_more_button' => 0,
        'use_horizontal_scroll' => 0,
        'number_of_displayed_icons' => null,
        'position' => "bottom",
        "order" => 70,
        "is_active" => 1,
        "use_homepage_slider" => 1,
        "options" => Siberian_Json::encode(array(
            "borders" => array(
                "border-right",
                "border-bottom",
            ),
            "textTransform" => "title-lowcase",
            "title" => "titlevisible",
        )),
    ),
    array(
        'name' => 'Layout 8',
        'category_id' => $default_layout_category->getId(),
        'visibility' => Application_Model_Layout_Homepage::VISIBILITY_HOMEPAGE,
        'code' => 'layout_8',
        'preview' => '/customization/layout/homepage/layout_8.png',
        'preview_new' => '/customization/layout/homepage/layout_8_modal.png',
        'use_more_button' => 0,
        'use_horizontal_scroll' => 0,
        'number_of_displayed_icons' => null,
        'position' => "bottom",
        "order" => 80,
        "is_active" => 0,
    ),
    array(
        'name' => 'Layout 9',
        'category_id' => $default_layout_category->getId(),
        'visibility' => Application_Model_Layout_Homepage::VISIBILITY_TOGGLE,
        'code' => 'layout_9',
        'preview' => '/customization/layout/homepage/layout_9.png',
        'preview_new' => '/customization/layout/homepage/layout_9_modal.png',
        'use_more_button' => 0,
        'use_horizontal_scroll' => 0,
        'number_of_displayed_icons' => null,
        'position' => "left",
        "order" => 90,
        "is_active" => 1,
        "use_homepage_slider" => 0,
        "options" => Siberian_Json::encode(array(
            "background" => "no-background",
            "textTransform" => "title-lowcase",
            "title" => "titlevisible",
            "sidebarWidthUnit" => "pixel",
            "sidebarWidth" => 10,
            "sidebarWidthPixel" => 120,
        )),
    ),
    array(
        'name' => 'Layout 10',
        'category_id' => $default_layout_category->getId(),
        'visibility' => Application_Model_Layout_Homepage::VISIBILITY_HOMEPAGE,
        'code' => 'layout_10',
        'preview' => '/customization/layout/homepage/layout_10.png',
        'preview_new' => '/customization/layout/homepage/layout_10_modal.png',
        'use_more_button' => 1,
        'use_horizontal_scroll' => 0,
        'number_of_displayed_icons' => 5,
        'position' => 'bottom',
        "order" => 100,
        "is_active" => 1,
        "use_homepage_slider" => 1,
        "options" => Siberian_Json::encode(array(
            "shadow" => "shadow",
            "border" => "visible",
        )),
    ),
    array(
        'name' => 'Layout 3 - Horizontal',
        'category_id' => $default_layout_category->getId(),
        'visibility' => Application_Model_Layout_Homepage::VISIBILITY_HOMEPAGE,
        'code' => 'layout_3_h',
        'preview' => '/customization/layout/homepage/layout_3-h.png',
        'preview_new' => '/customization/layout/homepage/layout_3-h_modal.png',
        'use_more_button' => 0,
        'use_horizontal_scroll' => 1,
        "number_of_displayed_icons" => 6,
        'position' => "bottom",
        "order" => 35,
        "is_active" => 1,
        "use_homepage_slider" => 1,
        "options" => Siberian_Json::encode(array(
            "colorizePager" => "0",
        )),
    ),
    array(
        'name' => 'Layout 4 - Horizontal',
        'category_id' => $default_layout_category->getId(),
        'visibility' => Application_Model_Layout_Homepage::VISIBILITY_HOMEPAGE,
        'code' => 'layout_4_h',
        'preview' => '/customization/layout/homepage/layout_4-h.png',
        'preview_new' => '/customization/layout/homepage/layout_4-h_modal.png',
        'use_more_button' => 0,
        'use_horizontal_scroll' => 1,
        "number_of_displayed_icons" => 6,
        'position' => "bottom",
        "order" => 45,
        "is_active" => 1,
        "use_homepage_slider" => 1,
        "options" => Siberian_Json::encode(array(
            "colorizePager" => "0",
        )),
    ),
    array(
        'name' => 'Layout 5 - Horizontal',
        'category_id' => $default_layout_category->getId(),
        'visibility' => Application_Model_Layout_Homepage::VISIBILITY_HOMEPAGE,
        'code' => 'layout_5_h',
        'preview' => '/customization/layout/homepage/layout_5-h.png',
        'preview_new' => '/customization/layout/homepage/layout_5-h_modal.png',
        'use_more_button' => 0,
        'use_horizontal_scroll' => 1,
        "number_of_displayed_icons" => 4,
        'position' => "bottom",
        "order" => 55,
        "is_active" => 1,
        "use_homepage_slider" => 1,
        "options" => Siberian_Json::encode(array(
            "colorizePager" => "0",
        )),
    ),
    array(
        'name' => 'Layout 3 - Full',
        'category_id' => $default_layout_category->getId(),
        'visibility' => Application_Model_Layout_Homepage::VISIBILITY_HOMEPAGE,
        'code' => 'layout_14',
        'preview' => '/customization/layout/homepage/layout_14.png',
        'preview_new' => '/customization/layout/homepage/layout_14_modal.png',
        'use_more_button' => 0,
        'use_horizontal_scroll' => 1,
        "number_of_displayed_icons" => 4,
        'position' => "bottom",
        "order" => 38,
        "is_active" => 1,
        "use_homepage_slider" => 0,
    ),
    array(
        'name' => 'Layout 5 - Full',
        'category_id' => $default_layout_category->getId(),
        'visibility' => Application_Model_Layout_Homepage::VISIBILITY_HOMEPAGE,
        'code' => 'layout_15',
        'preview' => '/customization/layout/homepage/layout_15.png',
        'preview_new' => '/customization/layout/homepage/layout_15_modal.png',
        'use_more_button' => 0,
        'use_horizontal_scroll' => 1,
        "number_of_displayed_icons" => 4,
        'position' => "bottom",
        "order" => 58,
        "is_active" => 1,
        "use_homepage_slider" => 0,
    ),
    array(
        'name' => 'Layout 11 - Fullscreen',
        'category_id' => $default_layout_category->getId(),
        'visibility' => Application_Model_Layout_Homepage::VISIBILITY_HOMEPAGE,
        'code' => 'layout_16',
        'preview' => '/customization/layout/homepage/layout_16.png',
        'preview_new' => '/customization/layout/homepage/layout_16_modal.png',
        'use_more_button' => 0,
        'use_horizontal_scroll' => 1,
        "number_of_displayed_icons" => 4,
        'position' => "bottom",
        "order" => 110,
        "is_active" => 1,
        "use_homepage_slider" => 0,
    ),
    array(
        'name' => 'Layout 12 - Metro',
        'category_id' => $default_layout_category->getId(),
        'visibility' => Application_Model_Layout_Homepage::VISIBILITY_HOMEPAGE,
        'code' => 'layout_17',
        'preview' => '/customization/layout/homepage/layout_17.png',
        'preview_new' => '/customization/layout/homepage/layout_17_modal.png',
        'use_more_button' => 0,
        'use_horizontal_scroll' => 1,
        "number_of_displayed_icons" => 4,
        'position' => "bottom",
        "order" => 120,
        "is_active" => 1,
        "use_homepage_slider" => 0,
        "options" => Siberian_Json::encode(array(
            "icons" => "default",
            "visible" => "1",
        )),
    )
);

foreach($datas as $data) {
    $layout = new Application_Model_Layout_Homepage();
    $layout
        ->setData($data)
        ->insertOrUpdate(array("code"));
}

$layout_category = new Application_Model_Layout_Category();
$custom_layout_category = $layout_category->find("custom", "code");

$layout_model = new Application_Model_Layout_Homepage();
$layouts = $layout_model->findAll();
foreach($layouts as $layout) {
    if($layout->getData("category_id") != $default_layout_category->getId()) {
        $layout->setCategoryId($custom_layout_category->getId())->save();
    }
}

$categories = array(
    array(
        "code" => "social",
        "name" => "Social",
        "icon" => "icon-share",
        "position" => 10
    ),
    array(
        "code" => "media",
        "name" => "Media",
        "icon" => "icon-play",
        "position" => 20
    ),
    array(
        "code" => "contact",
        "name" => "Contact",
        "icon" => "icon-phone",
        "position" => 30
    ),
    array(
        "code" => "monetization",
        "name" => "Monetization",
        "icon" => "icon-money",
        "position" => 40
    ),
    array(
        "code" => "customization",
        "name" => "Customization",
        "icon" => "icon-edit",
        "position" => 50
    ),
    array(
        "code" => "integration",
        "name" => "Integration",
        "icon" => "icon-globe",
        "position" => 60
    ),
    array(
        "code" => "events",
        "name" => "Events",
        "icon" => "icon-calendar",
        "position" => 70
    ),
    array(
        "code" => "misc",
        "name" => "Misc",
        "icon" => "icon-code",
        "position" => 80
    )
);

foreach($categories as $category_data) {
    $category = new Application_Model_Option_Category();
    $category
        ->setData($category_data)
        ->insertOnce(array("code"));
}


# run in 4.12.12 clean-up empty applications
try {
    $this->query("DELETE FROM application 
WHERE (name IS NULL OR name = '')
AND (bundle_id IS NULL OR bundle_id = '')
AND (package_name IS NULL OR package_name = '')
AND (design_id IS NULL OR design_id = '')
AND admin_id = 0;");
} catch(Exception $e) {
    if(method_exists($this, "log")) {
        $this->log("Skipped application clean-up, already done.");
    }
}



