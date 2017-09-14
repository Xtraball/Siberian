<?php
// Mobile Blocks
$datas = array(
    array(
        "code" => "header",
        "name" => "Header",
        "use_color" => 1,
        "color" => "#00377a",
        "use_background_color" => 1,
        "background_color" => "#739c03",
        "position" => 10
    ),
    array(
        "code" => "subheader",
        "name" => "Subheader",
        "use_color" => 1, "color" => "#00377a",
        "use_background_color" => 1,
        "background_color" => "#739c03",
        "position" => 20
    ),
    array(
        "code" => "connect_button",
        "name" => "Connect Button",
        "use_color" => 1,
        "color" => "#233799",
        "use_background_color" => 1,
        "background_color" => "#f2f2f2",
        "position" => 30
    ),
    array(
        "code" => "background",
        "name" => "Background",
        "use_color" => 1,
        "color" => "#ffffff",
        "use_background_color" => 1,
        "background_color" => "#0c6ec4",
        "position" => 40
    ),
    array(
        "code" => "discount",
        "name" => "Discount Zone",
        "use_color" => 1,
        "color" => "#fcfcfc",
        "use_background_color" => 1,
        "background_color" => "#739c03",
        "position" => 50
    ),
    array(
        "code" => "button",
        "name" => "Button",
        "use_color" => 1,
        "color" => "#fcfcfc",
        "use_background_color" => 1,
        "background_color" => "#00377a",
        "position" => 60
    ),
    array(
        "code" => "news",
        "name" => "News",
        "use_color" => 1,
        "color" => "#fcfcfc",
        "use_background_color" => 1,
        "background_color" => "#00377a",
        "position" => 70
    ),
    array(
        "code" => "comments",
        "name" => "Comments",
        "use_color" => 1,
        "color" => "#ffffff",
        "use_background_color" => 1,
        "background_color" => "#4d5d8a",
        "position" => 80
    ),
    array(
        "code" => "tabbar",
        "name" => "Tabbar",
        "use_color" => 1,
        "color" => "#ffffff",
        "use_background_color" => 1,
        "background_color" => "#739c03",
        "image_color" => "#ffffff",
        "position" => 90
    )
);

foreach($datas as $data) {
    $data["type_id"] = 1;
    $block = new Template_Model_Block();
    $block
        ->setData($data)
        ->insertOrUpdate(array("code", "type_id"));
}

# Inserting categories in 'template_category' table
$categories = array(
    "Arts",
    "Design",
    "Corporate",
);

foreach($categories as $category_name) {
    $data = array(
        "name" => $category_name,
        "code" => preg_replace('/[&\s]+/', "_", strtolower($category_name)),
    );

    $category = new Template_Model_Category();
    $category
        ->setData($data)
        ->insertOnce(array("code"));
}

# Listing all layouts
$layouts = array();
$layout = new Application_Model_Layout_Homepage();

foreach($layout->findAll() as $layout) {
    $layouts[$layout->getCode()] = $layout;
}

# Listings all block ids
$block_ids = array();
$block = new Template_Model_Block();
foreach($block->findAll() as $block) {
    $block_ids[$block->getCode()] = $block->getId();
}

# Inserting designs with blocks
$designs = array(
    "rouse" => array(
        "layout_id" => $layouts["layout_6"]->getId(),
        "name" => "Red&Co",
        "overview" => "/rouse/overview.png",
        "overview_new" => "/rouse/overview_new.png",
        "background_image" => "/../../images/templates/rouse/640x1136.jpg",
        "background_image_hd" => "/../../images/templates/rouse/1242x2208.jpg",
        "background_image_tablet" => "/../../images/templates/rouse/1536x2048.jpg",
        "background_image_landscape" => "/../../images/templates/rouse/rouse-1136-640.jpg",
        "background_image_landscape_hd" => "/../../images/templates/rouse/rouse-2208-1242.jpg",
        "background_image_landscape_tablet" => "/../../images/templates/rouse/rouse-2048-1536.jpg",
        "icon" => "/../../images/templates/rouse/180x180.png",
        "startup_image" => "/../../images/templates/rouse/640x960.png",
        "startup_image_retina" => "/../../images/templates/rouse/640x1136.jpg",
        "startup_image_iphone_6" => "/../../images/templates/rouse/750x1334.png",
        "startup_image_iphone_6_plus" => "/../../images/templates/rouse/1242x2208.jpg",
        "startup_image_ipad_retina" => "/../../images/templates/rouse/1536x2048.jpg",
    ),
    "bleuc" => array(
        "layout_id" => $layouts["layout_5"]->getId(),
        "name" => "Blutility",
        "overview" => "/bleuc/overview.png",
        "overview_new" => "/bleuc/overview_new.png",
        "background_image" => "/../../images/templates/bleuc/640x1136.jpg",
        "background_image_hd" => "/../../images/templates/bleuc/1242x2208.jpg",
        "background_image_tablet" => "/../../images/templates/bleuc/1536x2048.jpg",
        "background_image_landscape" => "/../../images/templates/bleuc/bleuc-1136-640.jpg",
        "background_image_landscape_hd" => "/../../images/templates/bleuc/bleuc-2208-1242.jpg",
        "background_image_landscape_tablet" => "/../../images/templates/bleuc/bleuc-2048-1536.jpg",
        "icon" => "/../../images/templates/bleuc/180x180.png",
        "startup_image" => "/../../images/templates/bleuc/640x960.png",
        "startup_image_retina" => "/../../images/templates/bleuc/640x1136.jpg",
        "startup_image_iphone_6" => "/../../images/templates/bleuc/750x1334.png",
        "startup_image_iphone_6_plus" => "/../../images/templates/bleuc/1242x2208.jpg",
        "startup_image_ipad_retina" => "/../../images/templates/bleuc/1536x2048.jpg",
    ),
    "colors" => array(
        "layout_id" => $layouts["layout_4"]->getId(),
        "name" => "Colors",
        "overview" => "/colors/overview.png",
        "overview_new" => "/colors/overview_new.png",
        "background_image" => "/../../images/templates/colors/640x1136.jpg",
        "background_image_hd" => "/../../images/templates/colors/1242x2208.jpg",
        "background_image_tablet" => "/../../images/templates/colors/1536x2048.jpg",
        "background_image_landscape" => "/../../images/templates/colors/colors-1136-640.jpg",
        "background_image_landscape_hd" => "/../../images/templates/colors/colors-2208-1242.jpg",
        "background_image_landscape_tablet" => "/../../images/templates/colors/colors-2048-1536.jpg",
        "icon" => "/../../images/templates/colors/180x180.png",
        "startup_image" => "/../../images/templates/colors/640x960.jpg",
        "startup_image_retina" => "/../../images/templates/colors/640x1136.jpg",
        "startup_image_iphone_6" => "/../../images/templates/colors/750x1334.jpg",
        "startup_image_iphone_6_plus" => "/../../images/templates/colors/1242x2208.jpg",
        "startup_image_ipad_retina" => "/../../images/templates/colors/1536x2048.jpg",
    ),
    "blank" => array(
        "layout_id" => $layouts["layout_1"]->getId(),
        "name" => "Blank",
        "overview" => "/blank/overview.png",
        "background_image" => "/../../images/application/placeholder/no-background.jpg",
        "background_image_hd" => "/../../images/application/placeholder/no-background-hd.jpg",
        "background_image_tablet" => "/../../images/application/placeholder/no-background-tablet.jpg",
        "icon" => "/../../images/application/placeholder/no-image.png",
        "startup_image" => "/../../images/application/placeholder/no-startupimage.png",
        "startup_image_retina" => "/../../images/application/placeholder/no-startupimage-retina.png",
        "startup_image_iphone_6" => "/../../images/application/placeholder/no-startupimage-iphone-6.png",
        "startup_image_iphone_6_plus" => "/../../images/application/placeholder/no-startupimage-iphone-6-plus.png",
        "startup_image_ipad_retina" => "/../../images/application/placeholder/no-startupimage-tablet.png"
    )
);

foreach($designs as $code => $data) {
    $data["code"] = $code;

    $design = new Template_Model_Design();
    $design
        ->setData($data)
        ->insertOrUpdate(array("code"));

    if (!empty($data["blocks"])) {
        foreach ($data["blocks"] as $block_code => $block_data) {

            $block_data["design_id"] = $design->getId();
            $block_data["block_id"] = $block_ids[$block_code];

            $design_block = new Template_Model_Design_Block();
            $design_block
                ->setData($block_data)
                ->insertOrUpdate(array("design_id", "block_id"));
        }
    }

}

# Assigning designs to categories
$categories_designs = array(
    "design" => array(
        "rouse"
    ),
    "corporate" => array(
        "bleuc"
    ),
    "arts" => array(
        "colors"
    ),
);

# Listing all design ids
$design_ids = array();
$design = new Template_Model_Design();
foreach ($design->findAll() as $design_data) {
    $design_ids[$design_data->getCode()] = $design_data->getId();
}

# Listing all category ids
$category_ids = array();
$category = new Template_Model_Category();
foreach ($category->findAll() as $category_data) {
    $category_ids[$category_data->getCode()] = $category_data->getId();
}

foreach ($categories_designs as $category_code => $design_codes) {
    $categories_designs_data = array("category_id" => $category_ids[$category_code]);

    foreach ($design_codes as $design_code) {
        $categories_designs_data["design_id"] = $design_ids[$design_code];

        $design_category = new Template_Model_Design_Category();
        $design_category
                ->setData($categories_designs_data)
                ->insertOrUpdate(array("design_id", "category_id"));
    }

}

# Assigning features to designs
$design_codes = array(
    "rouse" => array(
       "set_meal" => array("icon" => "/set_meal/meat1-flat.png"),
       "booking" => array("icon" => "/booking/booking1-flat.png"),
       "catalog" => array("icon" => "/catalog/catalog1-flat.png"),
       "discount" => array("icon" => "/discount/discount1-flat.png"),
       "loyalty" => array("icon" => "/loyalty/loyalty1-flat.png")
    ),
    "bleuc" => array(
        "facebook" => array("icon" => "/social_facebook/facebook1-flat.png"),
        "weblink_multi" => array("name" => "Links", "icon" => "/weblink/link1-flat.png"),
        "push_notification" => array("icon" => "/push_notifications/push1-flat.png"),
        "tip" => array("icon" => "/tip/tip1-flat.png")
    ),
    "colors" => array(
       "music_gallery" => array("icon" => "/musics/music1-flat.png"),
       "image_gallery" => array("icon" => "/images/image1-flat.png"),
       "video_gallery" => array("icon" => "/videos/video1-flat.png"),
       "fanwall" => array("icon" => "/fanwall/fanwall1-flat.png"),
       "radio" => array("icon" => "/radio/radio1-flat.png"),
       "calendar" => array("icon" => "/calendar/calendar1-flat.png"),
       "newswall" => array("icon" => "/newswall/newswall1-flat.png"),
       "code_scan" => array("icon" => "/code_scan/scan1-flat.png")
    ),
);

foreach ($design_codes as $design_code => $option_codes) {
    foreach ($option_codes as $option_code => $option_infos) {

        $design = new Template_Model_Design();
        $design->find($design_code, "code");

        $option = new Application_Model_Option();
        $options = $option->findAll(array("code IN (?)" => $option_code));

        foreach($options as $option) {

            $icon_id = NULL;
            if(isset($option_infos["icon"])) {
                $icon = new Media_Model_Library_Image();
                $icon->find($option_infos["icon"], "link");

                if (!$icon->getData()) {
                    $icon
                        ->setLibraryId($option->getLibraryId())
                        ->setLink($option_infos["icon"])
                        ->setOptionId($option->getId())
                        ->setCanBeColorized(1)
                        ->setPosition(0)
                        ->save()
                    ;
                }

                $icon_id = $icon->getId();
            }

            $data = array(
                "design_id" => $design->getId(),
                "option_id" => $option->getId(),
                "option_tabbar_name" => isset($option_infos["name"]) ? $option_infos["name"] : NULL,
                "option_icon" => $icon_id,
                "option_background_image" => isset($option_infos["background_image"]) ? $option_infos["background_image"] : NULL
            );

            $design_content = new Template_Model_Design_Content();
            $design_content
                ->setData($data)
                ->insertOrUpdate(array("design_id", "option_id"));

        }
    }
}

$blocks = array(
    /* GENERAL */
    array(
        "code" => "background",
        "name" => "General",
        "background_color" => "#ededed",
        "background_color_variable_name" => '$general-custom-bg',
        "position" => "10"
    ),
    /* HEADER */
    array(
        "code" => "header",
        "name" => "Header",
        "color" => "#444",
        "color_variable_name" => '$bar-custom-text',
        "background_color" => "#f8f8f8",
        "background_color_variable_name" => '$bar-custom-bg',
        "border_color" => "#b2b2b2",
        "border_color_variable_name" => '$bar-custom-border',
        "position" => "20"
    ),
    /* HOMEPAGE */
    array(
        "code" => "homepage",
        "name" => "Homepage",
        "color" => "#111",
        "color_variable_name" => '$homepage-custom-text',
        "background_color" => "#fff",
        "background_color_variable_name" => '$homepage-custom-bg',
        "border_color" => "#ddd",
        "border_color_variable_name" => '$homepage-custom-border',
        "image_color" => "#ddd",
        "image_color_variable_name" => '$homepage-custom-image',
        "position" => "30"
    ),
    /* LIST */
    array(
        "code" => "list",
        "name" => "List",
        "position" => "50",
        "children" => array(
            array(
                "code" => "list_item_divider",
                "name" => "Title's List",
                "color" => "#222",
                "color_variable_name" => '$list-item-divider-custom-text',
                "background_color" => "#f8f8f8",
                "background_color_variable_name" => '$list-item-divider-custom-bg'
            ), array(
                "code" => "list_item",
                "name" => "List Item",
                "color" => "#444",
                "color_variable_name" => '$list-item-custom-text',
                "background_color" => "#fff",
                "background_color_variable_name" => '$list-item-custom-bg'
            )
        )
    ),
    /* CARD */
    array(
        "code" => "card",
        "name" => "Card",
        "position" => "70",
        "children" => array(
            array(
                "code" => "card_item_divider",
                "name" => "Title's Card",
                "color" => "#222",
                "color_variable_name" => '$card-item-divider-custom-text',
                "background_color" => "#f8f8f8",
                "background_color_variable_name" => '$card-item-divider-custom-bg'
            ), array(
                "code" => "card_item",
                "name" => "Card Item",
                "color" => "#444",
                "color_variable_name" => '$card-item-custom-text',
                "background_color" => "#fff",
                "background_color_variable_name" => '$card-item-custom-bg'
            )
        )
    ),
    /* BUTTONS */
    array(
        "code" => "buttons_group",
        "name" => "Buttons",
        "position" => "80",
        "children" => array(
            array(
                "code" => "buttons",
                "name" => "Button",
                "more" => "phone, locate, facebook, email, etc..",
                "color" => "#444",
                "color_variable_name" => '$button-custom-text',
                "background_color" => "#f8f8f8",
                "background_color_variable_name" => '$button-custom-bg',
                "border_color" => "#b2b2b2",
                "border_color_variable_name" => '$button-custom-border',
            ),
            array(
                "code" => "buttons_light",
                "name" => "Button light",
                "color" => "#444",
                "color_variable_name" => '$button-light-custom-text',
                "background_color" => "#ffffff",
                "background_color_variable_name" => '$button-light-custom-bg',
                "border_color" => "#dddddd",
                "border_color_variable_name" => '$button-light-custom-border',
            ),
            array(
                "code" => "buttons_positive",
                "name" => "Button positive",
                "more" => "form submit, search, validation, confirmation",
                "color" => "#ffffff",
                "color_variable_name" => '$button-positive-custom-text',
                "background_color" => "#387ef5",
                "background_color_variable_name" => '$button-positive-custom-bg',
                "border_color" => "#0c60ee",
                "border_color_variable_name" => '$button-positive-custom-border',
            ),
            array(
                "code" => "buttons_calm",
                "name" => "Button calm",
                "more" => "informative, modal",
                "color" => "#ffffff",
                "color_variable_name" => '$button-calm-custom-text',
                "background_color" => "#11c1f3",
                "background_color_variable_name" => '$button-calm-custom-bg',
                "border_color" => "#0a9dc7",
                "border_color_variable_name" => '$button-calm-custom-border',
            ),
            array(
                "code" => "buttons_balanced",
                "name" => "Button balanced",
                "more" => "contextual, depends on module/layout",
                "color" => "#ffffff",
                "color_variable_name" => '$button-balanced-custom-text',
                "background_color" => "#33cd5f",
                "background_color_variable_name" => '$button-balanced-custom-bg',
                "border_color" => "#28a54c",
                "border_color_variable_name" => '$button-balanced-custom-border',
            ),
            array(
                "code" => "buttons_energized",
                "name" => "Button energized",
                "more" => "contextual, depends on module/layout",
                "color" => "#ffffff",
                "color_variable_name" => '$button-energized-custom-text',
                "background_color" => "#ffc900",
                "background_color_variable_name" => '$button-energized-custom-bg',
                "border_color" => "#e6b500",
                "border_color_variable_name" => '$button-energized-custom-border',
            ),
            array(
                "code" => "buttons_assertive",
                "name" => "Button assertive",
                "more" => "confirm action, deletion, etc ...",
                "color" => "#ffffff",
                "color_variable_name" => '$button-assertive-custom-text',
                "background_color" => "#ef473a",
                "background_color_variable_name" => '$button-assertive-custom-bg',
                "border_color" => "#e42112",
                "border_color_variable_name" => '$button-assertive-custom-border',
            ),
            array(
                "code" => "buttons_royal",
                "name" => "Button royal",
                "more" => "contextual, depends on module/layout",
                "color" => "#ffffff",
                "color_variable_name" => '$button-royal-custom-text',
                "background_color" => "#886aea",
                "background_color_variable_name" => '$button-royal-custom-bg',
                "border_color" => "#6b46e5",
                "border_color_variable_name" => '$button-royal-custom-border',
            ),
            array(
                "code" => "buttons_dark",
                "name" => "Button dark",
                "more" => "contextual, depends on module/layout",
                "color" => "#ffffff",
                "color_variable_name" => '$button-dark-custom-text',
                "background_color" => "#444444",
                "background_color_variable_name" => '$button-dark-custom-bg',
                "border_color" => "#111111",
                "border_color_variable_name" => '$button-dark-custom-border',
            ),
        ),
    ),
    /* CHECKBOX */
    array(
        "code" => "checkbox",
        "name" => "Checkbox",
        "position" => "90",
        "children" => array(
            array(
                "code" => "checkbox_general",
                "name" => "General",
                "background_color" => "#fff",
                "background_color_variable_name" => '$checkbox-general-custom-bg',
                "color" => "#444",
                "color_variable_name" => '$checkbox-general-custom-text'
            ), array(
                "code" => "checkbox_on",
                "name" => "Checkbox on",
                "background_color" => "#387ef5",
                "background_color_variable_name" => '$checkbox-on-custom-bg',
                "color" => "#fff",
                "color_variable_name" => '$checkbox-on-custom-text'
            ), array(
                "code" => "checkbox_off",
                "name" => "Checkbox off",
                "background_color" => "#fff",
                "background_color_variable_name" => '$checkbox-off-custom-bg'
            ),
        )
    ),
    /* RADIO */
    array(
        "code" => "radio",
        "name" => "Radio",
        "color" => "#444",
        "color_variable_name" => '$radio-custom-text',
        "background_color" => "#fff",
        "background_color_variable_name" => '$radio-custom-bg',
        "position" => "100"
    ),
    /* TOGGLE */
    array(
        "code" => "toggle",
        "name" => "Toggle",
        "position" => "110",
        "children" => array(
            array(
                "code" => "toggle_general",
                "name" => "General",
                "color" => "#444",
                "color_variable_name" => '$toggle-general-custom-text',
                "background_color" => "#fff",
                "background_color_variable_name" => '$toggle-general-custom-bg'
            ), array(
                "code" => "toggle_on",
                "name" => "Toggle on",
                "background_color" => "#387ef5",
                "background_color_variable_name" => '$toggle-on-custom-bg'
            ), array(
                "code" => "toggle_off",
                "name" => "Toggle off",
                "background_color" => "#fff",
                "background_color_variable_name" => '$toggle-off-custom-bg',
                "border_color" => "#e6e6e6",
                "border_color_variable_name" => '$toggle-off-custom-border'
            ), array(
                "code" => "toggle_handle_on",
                "name" => "Toggle's Handle on",
                "background_color" => "#fff",
                "background_color_variable_name" => '$toggle-handle-on-custom-bg'
            ), array(
                "code" => "toggle_handle_off",
                "name" => "Toggle's Handle off",
                "background_color" => "#fff",
                "background_color_variable_name" => '$toggle-handle-off-custom-bg'
            ),
        )
    ),
    /* TOOLTIP */
    array(
        "code" => "tooltip",
        "name" => "Tooltip",
        "color" => "#fff",
        "color_variable_name" => '$tooltip-custom-text',
        "background_color" => "#444",
        "background_color_variable_name" => '$tooltip-custom-bg',
        "position" => "120"
    ),
    /* ICON */
    array(
        "code" => "icon",
        "name" => "Icon",
        "position" => "125",
        "children" => array(
            array(
                "code" => "icon",
                "name" => "Icon",
                "color" => "#fff",
                "color_variable_name" => '$icon-custom',
            ), array(
                "code" => "icon_active",
                "name" => "Icon active",
                "color" => "#333",
                "color_variable_name" => '$icon-active-custom',
            ), array(
                "code" => "icon_inactive",
                "name" => "Icon inactive",
                "color" => "#cccccc",
                "color_variable_name" => '$icon-inactive-custom',
            ),
        )
    ),
    /* SPINNER */
    array(
        "code" => "spinner",
        "name" => "Spinner",
        "position" => "130",
        "children" => array(
            array(
                "code" => "spinner_ios_text",
                "name" => "iOS Spinner",
                "background_color" => "#69717d",
                "background_color_variable_name" => '$spinner-custom-ios-bg'
            ), array(
                "code" => "spinner_android_text",
                "name" => "Android Spinner",
                "background_color" => "#4b8bf4",
                "background_color_variable_name" => '$spinner-custom-android-bg'
            ),
        )
    ),
    /* DIALOG */
    array(
        "code" => "dialog",
        "name" => "Dialog",
        "position" => "135",
        "children" => array(
            array(
                "code" => "dialog_text",
                "name" => "Dialog text",
                "color" => "#000",
                "color_variable_name" => '$dialog-custom-text',
            ), array(
                "code" => "dialog_bg",
                "name" => "Dialog background",
                "color" => "#fff",
                "color_variable_name" => '$dialog-custom-bg',
            ), array(
                "code" => "dialog_button",
                "name" => "Dialog button",
                "color" => "#007aff",
                "color_variable_name" => '$dialog-custom-button',
            ),
        )
    )
);


foreach($blocks as $data) {

    $data["type_id"] = 3;
    $block = new Template_Model_Block();
    $block
        ->setData($data)
        ->insertOrUpdate(array("code", "type_id"));

    if(!empty($data["children"])) {

        $position = $block->getPosition();
        foreach($data["children"] as $child_data) {

            $position += 2;
            $child_data["type_id"] = 3;
            $child_data["parent_id"] = $block->getId();
            $child_data["position"] = $position;
            $child = new Template_Model_Block();
            $child
                ->setData($child_data)
                ->insertOrUpdate(array("code", "type_id"));

        }
    }
}

# Listing all layouts
$layouts = array();
$layout = new Application_Model_Layout_Homepage();

foreach($layout->findAll() as $layout) {
    $layouts[$layout->getCode()] = $layout;
}

# Listings all block ids
$block_ids = array();
$blocks = new Template_Model_Block();

foreach($blocks->findAll() as $block) {
    $block_ids[$block->getCode()] = $block->getId();
    $children = $block->getChildren() ? $block->getChildren() : array($block);
    foreach($children as $child) {
        $block_ids[$child->getCode()] = $child->getId();
    }
}

# Inserting designs with blocks
$designs = array(
    "rouse" => array(
        "layout_id" => $layouts["layout_6"]->getId(),
        "name" => "Rouse",
        "blocks" => array(
            "header" => array(
                "color" => "#ffffff",
                "background_color" => "#EE4B63",
                "border_color" => "#ffffff"
            ),
            "buttons_positive" => array(
                "color" => "#ffffff",
                "background_color" => "#EE4B63",
                "border_color" => "#ffffff",
            ),
            "subheader" => array(
                "color" => "#ffffff",
                "background_color" => "#EE4B63",
                "border_color" => "#ffffff"
            ),
            "homepage" => array(
                "color" => "#ffffff",
                "background_color" => "#ffffff",
                "background_opacity" => 20,
                "border_color" => "#ffffff",
                "border_opacity" => 100,
                "image_color" => "#ffffff"
            ),
            "background" => array(
                "background_color" => "#242037"
            ),
            "list_item_divider" => array(
                "color" => "#ffffff",
                "background_color" => "#EE4B63"
            ),
            "list_item" => array(
                "color" => "#000222",
                "background_color" => "#ffffff"
            ),
            "card_item_divider" => array(
                "color" => "#000222",
                "background_color" => "#ee4b63"
            ),
            "checkbox_on" => array(
                "color" => "#ffffff",
                "background_color" => "#ee4b63"
            ),
            "toggle_on" => array(
                "background_color" => "#ee4b63"
            ),
            "spinner_android_text" => array(
                "background_color" => "#ee4b63"
            ),
        ),
    ),
    "bleuc" => array(
        "layout_id" => $layouts["layout_5"]->getId(),
        "name" => "Bleuc",
        "blocks" => array(
            "header" => array(
                "color" => "#ffffff",
                "background_color" => "#1374CE",
                "border_color" => "#ffffff",
            ),
            "buttons_positive" => array(
                "color" => "#ffffff",
                "background_color" => "#1374CE",
                "border_color" => "#ffffff",
            ),
            "subheader" => array(
                "color" => "#ffffff",
                "background_color" => "#1374CE",
                "border_color" => "#ffffff"
            ),
            "homepage" => array(
                "color" => "#ffffff",
                "background_color" => "#1374CE",
                "background_opacity" => 20,
                "border_color" => "#ffffff",
                "border_opacity" => 0,
                "image_color" => "#ffffff"
            ),
            "background" => array(
                "background_color" => "#ffffff"
            ),
            "list_item_divider" => array(
                "color" => "#ffffff",
                "background_color" => "#1374CE"
            ),
            "list_item" => array(
                "color" => "#000222",
                "background_color" => "#ffffff"
            ),
            "card_item_divider" => array(
                "color" => "#000222",
                "background_color" => "#1374CE"
            ),
            "checkbox_on" => array(
                "color" => "#ffffff",
                "background_color" => "#1374CE"
            ),
            "toggle_on" => array(
                "background_color" => "#1374CE"
            ),
            "spinner_android_text" => array(
                "background_color" => "#1374CE"
            ),
        ),
    ),
    "colors" => array(
        "layout_id" => $layouts["layout_4"]->getId(),
        "name" => "Colors",
        "blocks" => array(
            "header" => array(
                "color" => "#ffffff",
                "background_color" => "#ee4b63",
                "border_color" => "#ffffff"
            ),
            "buttons_positive" => array(
                "color" => "#ffffff",
                "background_color" => "#ee4b63",
                "border_color" => "#ffffff"
            ),
            "subheader" => array(
                "color" => "#ffffff",
                "background_color" => "#ee4b63",
                "border_color" => "#ffffff"
            ),
            "homepage" => array(
                "color" => "#0faca4",
                "background_color" => "#0faca4",
                "border_color" => "#0faca4",
                "image_color" => "#ffffff"
            ),
            "background" => array(
                "background_color" => "#0faca4"
            ),
            "list_item_divider" => array(
                "color" => "#ffffff",
                "background_color" => "#0faca4"
            ),
            "list_item" => array(
                "color" => "#000222",
                "background_color" => "#ffffff"
            ),
            "card_item_divider" => array(
                "color" => "#ffffff",
                "background_color" => "#0faca4"
            ),
            "checkbox_on" => array(
                "color" => "#ffffff",
                "background_color" => "#0faca4"
            ),
            "toggle_on" => array(
                "background_color" => "#0faca4"
            ),
            "spinner_android_text" => array(
                "background_color" => "#0faca4"
            ),
        )
    )
);


foreach($designs as $code => $data) {
    $design = new Template_Model_Design();
    $design->find($code, "code");

    if($design->getId()) {
        if (!empty($data["blocks"])) {
            foreach ($data["blocks"] as $block_code => $block_data) {
                $block_data["design_id"] = $design->getId();
                $block_data["block_id"] = $block_ids[$block_code];

                $template_block = new Template_Model_Design_Block();
                $template_block
                    ->setData($block_data)
                    ->insertOrUpdate(array("design_id", "block_id"));
            }
        }
    }
}
