<?php
$datas = array(
    array('name' => 'Layout 1',              'visibility' => Application_Model_Layout_Homepage::VISIBILITY_ALWAYS,   'code' => 'layout_1',   'preview' => '/customization/layout/homepage/layout_1.png',   'use_more_button' => 1, 'use_horizontal_scroll' => 0, 'number_of_displayed_icons' => 5,     'position' => "bottom", "order" => 10, "is_active" => 1),
    array('name' => 'Layout 2',              'visibility' => Application_Model_Layout_Homepage::VISIBILITY_HOMEPAGE, 'code' => 'layout_2',   'preview' => '/customization/layout/homepage/layout_2.png',   'use_more_button' => 1, 'use_horizontal_scroll' => 0, 'number_of_displayed_icons' => 10,    'position' => "bottom", "order" => 20, "is_active" => 1),
    array('name' => 'Layout 3',              'visibility' => Application_Model_Layout_Homepage::VISIBILITY_HOMEPAGE, 'code' => 'layout_3',   'preview' => '/customization/layout/homepage/layout_3.png',   'use_more_button' => 0, 'use_horizontal_scroll' => 0, 'number_of_displayed_icons' => null,  'position' => "bottom", "order" => 30, "is_active" => 1),
    array('name' => 'Layout 4',              'visibility' => Application_Model_Layout_Homepage::VISIBILITY_HOMEPAGE, 'code' => 'layout_4',   'preview' => '/customization/layout/homepage/layout_4.png',   'use_more_button' => 0, 'use_horizontal_scroll' => 0, 'number_of_displayed_icons' => null,  'position' => "bottom", "order" => 40, "is_active" => 1),
    array('name' => 'Layout 5',              'visibility' => Application_Model_Layout_Homepage::VISIBILITY_HOMEPAGE, 'code' => 'layout_5',   'preview' => '/customization/layout/homepage/layout_5.png',   'use_more_button' => 0, 'use_horizontal_scroll' => 0, 'number_of_displayed_icons' => null,  'position' => "bottom", "order" => 50, "is_active" => 1),
    array('name' => 'Layout 6',              'visibility' => Application_Model_Layout_Homepage::VISIBILITY_HOMEPAGE, 'code' => 'layout_6',   'preview' => '/customization/layout/homepage/layout_6.png',   'use_more_button' => 0, 'use_horizontal_scroll' => 0, 'number_of_displayed_icons' => null,  'position' => "bottom", "order" => 60, "is_active" => 1),
    array('name' => 'Layout 7',              'visibility' => Application_Model_Layout_Homepage::VISIBILITY_HOMEPAGE, 'code' => 'layout_7',   'preview' => '/customization/layout/homepage/layout_7.png',   'use_more_button' => 0, 'use_horizontal_scroll' => 0, 'number_of_displayed_icons' => null,  'position' => "bottom", "order" => 70, "is_active" => 1),
    array('name' => 'Layout 8',              'visibility' => Application_Model_Layout_Homepage::VISIBILITY_HOMEPAGE, 'code' => 'layout_8',   'preview' => '/customization/layout/homepage/layout_8.png',   'use_more_button' => 0, 'use_horizontal_scroll' => 0, 'number_of_displayed_icons' => null,  'position' => "bottom", "order" => 80, "is_active" => 0),
    array('name' => 'Layout 9',              'visibility' => Application_Model_Layout_Homepage::VISIBILITY_TOGGLE,   'code' => 'layout_9',   'preview' => '/customization/layout/homepage/layout_9.png',   'use_more_button' => 0, 'use_horizontal_scroll' => 0, 'number_of_displayed_icons' => null,  'position' => "left",   "order" => 90, "is_active" => 1),
    array('name' => 'Layout 10',             'visibility' => Application_Model_Layout_Homepage::VISIBILITY_HOMEPAGE, 'code' => 'layout_10',  'preview' => '/customization/layout/homepage/layout_10.png',  'use_more_button' => 1, 'use_horizontal_scroll' => 0, 'number_of_displayed_icons' => 5,     'position' => 'bottom', "order" => 100, "is_active" => 1),
    array('name' => 'Layout 3 - Horizontal', 'visibility' => Application_Model_Layout_Homepage::VISIBILITY_HOMEPAGE, 'code' => 'layout_3_h', 'preview' => '/customization/layout/homepage/layout_3-h.png', 'use_more_button' => 0, 'use_horizontal_scroll' => 1, "number_of_displayed_icons" => 6,     'position' => "bottom", "order" => 35, "is_active" => 1),
    array('name' => 'Layout 4 - Horizontal', 'visibility' => Application_Model_Layout_Homepage::VISIBILITY_HOMEPAGE, 'code' => 'layout_4_h', 'preview' => '/customization/layout/homepage/layout_4-h.png', 'use_more_button' => 0, 'use_horizontal_scroll' => 1, "number_of_displayed_icons" => 6,     'position' => "bottom", "order" => 45, "is_active" => 1),
    array('name' => 'Layout 5 - Horizontal', 'visibility' => Application_Model_Layout_Homepage::VISIBILITY_HOMEPAGE, 'code' => 'layout_5_h', 'preview' => '/customization/layout/homepage/layout_5-h.png', 'use_more_button' => 0, 'use_horizontal_scroll' => 1, "number_of_displayed_icons" => 4,     'position' => "bottom", "order" => 55, "is_active" => 1)
);

foreach($datas as $data) {
    $layout = new Application_Model_Layout_Homepage();
    $layout->setData($data)->save();
}


$categories = array(
    array("code" => "social",       "name" => "Social",         "icon" => "icon-share",     "position" => 10),
    array("code" => "media",        "name" => "Media",          "icon" => "icon-play",      "position" => 20),
    array("code" => "contact",      "name" => "Contact",        "icon" => "icon-phone",     "position" => 30),
    array("code" => "monetization", "name" => "Monetization",   "icon" => "icon-money",     "position" => 40),
    array("code" => "customization","name" => "Customization",  "icon" => "icon-edit",      "position" => 50),
    array("code" => "integration",  "name" => "Integration",    "icon" => "icon-globe",     "position" => 60),
    array("code" => "events",       "name" => "Events",         "icon" => "icon-calendar",  "position" => 70),
    array("code" => "misc",         "name" => "Misc",           "icon" => "icon-code",      "position" => 80)
);

foreach($categories as $category_data) {
    $category = new Application_Model_Option_Category();
    $category->setData($category_data)
        ->save()
    ;
    foreach($category_data["features"] as $feature_code) {
        $this->_db->update("application_option", array("category_id" => $category->getId()), array("code = ?" => $feature_code));
    }
}
