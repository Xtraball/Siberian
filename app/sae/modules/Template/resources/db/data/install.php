<?php
// Mobile Blocks
$datas = array(
    array('code' => 'header', 'name' => 'Header', 'use_color' => 1, 'color' => '#00377a', 'use_background_color' => 1, 'background_color' => '#739c03', 'position' => 10),
    array('code' => 'subheader', 'name' => 'Subheader', 'use_color' => 1, 'color' => '#00377a', 'use_background_color' => 1, 'background_color' => '#739c03', 'position' => 20),
    array('code' => 'connect_button', 'name' => 'Connect Button', 'use_color' => 1, 'color' => '#233799', 'use_background_color' => 1, 'background_color' => '#f2f2f2', 'position' => 30),
    array('code' => 'background', 'name' => 'Background', 'use_color' => 1, 'color' => '#ffffff', 'use_background_color' => 1, 'background_color' => '#0c6ec4', 'position' => 40),
    array('code' => 'discount', 'name' => 'Discount Zone', 'use_color' => 1, 'color' => '#fcfcfc', 'use_background_color' => 1, 'background_color' => '#739c03', 'position' => 50),
    array('code' => 'button', 'name' => 'Button', 'use_color' => 1, 'color' => '#fcfcfc', 'use_background_color' => 1, 'background_color' => '#00377a', 'position' => 60),
    array('code' => 'news', 'name' => 'News', 'use_color' => 1, 'color' => '#fcfcfc', 'use_background_color' => 1, 'background_color' => '#00377a', 'position' => 70),
    array('code' => 'comments', 'name' => 'Comments', 'use_color' => 1, 'color' => '#ffffff', 'use_background_color' => 1, 'background_color' => '#4d5d8a', 'position' => 80),
    array('code' => 'tabbar', 'name' => 'Tabbar', 'use_color' => 1, 'color' => '#ffffff', 'use_background_color' => 1, 'background_color' => '#739c03', 'image_color' => '#ffffff', 'position' => 90)
);

foreach($datas as $data) {
    $data["type_id"] = 1;
    $block = new Template_Model_Block();
    $block->setData($data)->save();
}

// Inserting categories in 'template_category' table
$categories = array(
    "Entertainment",
    "Local Business",
    "Music"
);

foreach($categories as $category_name) {
    $category_data = array();
    $category_data['name'] = $category_name;
    $category_data['code'] = preg_replace('/[&\s]+/', "_", strtolower($category_name));

    $category = new Template_Model_Category();
    $category->find($category_data['code'], "code");

    $category->setData($category_data)
        ->save()
    ;
}

// Listing all layouts
$layouts = array();
$layout = new Application_Model_Layout_Homepage();

foreach($layout->findAll() as $layout) {
    $layouts[$layout->getCode()] = $layout;
}

// Listings all block ids
$block_ids = array();
$block = new Template_Model_Block();
foreach($block->findAll() as $block) {
    $block_ids[$block->getCode()] = $block->getId();
}

// Inserting designs with blocks
$designs = array(
    "fairground" => array(
        "layout_id" => $layouts["layout_3"]->getId(),
        "name" => "Fairground",
        "overview" => "/fairground/overview.png",
        "background_image" => "/../../images/templates/fairground/640x1136.jpg",
        "background_image_hd" => "/../../images/templates/fairground/1242x2208.jpg",
        "background_image_tablet" => "/../../images/templates/fairground/1536x2048.jpg",
        "icon" => "/../../images/templates/fairground/180x180.png",
        "startup_image" => "/../../images/templates/fairground/640x960.png",
        "startup_image_retina" => "/../../images/templates/fairground/640x1136.jpg",
        "startup_image_iphone_6" => "/../../images/templates/fairground/750x1334.png",
        "startup_image_iphone_6_plus" => "/../../images/templates/fairground/1242x2208.jpg",
        "startup_image_ipad_retina" => "/../../images/templates/fairground/1536x2048.jpg",
        "blocks" => array(
            "header" => array(
                "color" => "#323b40",
                "background_color" => "#ee4b63"
            ),
            "subheader" => array(
                "color" => "#323b40",
                "background_color" => "#fdc32f"
            ),
            "connect_button" => array(
                "color" => "#323b40",
                "background_color" => "#6fb7b1"
            ),
            "background" => array(
                "color" => "#323b40",
                "background_color" => "#f9e4d1"
            ),
            "discount" => array(
                "color" => "#ee4b63",
                "background_color" => "#f9e4d1"
            ),
            "button" => array(
                "color" => "#323b40",
                "background_color" => "#6fb7b1"
            ),
            "news" => array(
                "color" => "#323b40",
                "background_color" => "#f9e4d1"
            ),
            "comments" => array(
                "color" => "#",
                "background_color" => "#fdc32f"
            ),
            "tabbar" => array(
                "color" => "#ee4b63",
                "background_color" => "transparent",
                "image_color" => "#ee4b63"
            )
        )
    ),
    "pizza" => array(
        "layout_id" => $layouts["layout_9"]->getId(),
        "layout_visibility" => "toggle",
        "name" => "Pizza",
        "overview" => "/pizza/overview.png",
        "background_image" => "/../../images/templates/pizza/640x1136.jpg",
        "background_image_hd" => "/../../images/templates/pizza/1242x2208.jpg",
        "background_image_tablet" => "/../../images/templates/pizza/1536x2048.jpg",
        "icon" => "/../../images/templates/pizza/180x180.png",
        "startup_image" => "/../../images/templates/pizza/640x960.png",
        "startup_image_retina" => "/../../images/templates/pizza/640x1136.jpg",
        "startup_image_iphone_6" => "/../../images/templates/pizza/750x1334.png",
        "startup_image_iphone_6_plus" => "/../../images/templates/pizza/1242x2208.jpg",
        "startup_image_ipad_retina" => "/../../images/templates/pizza/1536x2048.jpg",
        "blocks" => array(
            "header" => array(
                "color" => "#ffffff",
                "background_color" => "#00a72d"
            ),
            "subheader" => array(
                "color" => "#ffffff",
                "background_color" => "#e50017"
            ),
            "connect_button" => array(
                "color" => "#ffffff",
                "background_color" => "#00a72d"
            ),
            "background" => array(
                "color" => "#00a72d",
                "background_color" => "#ffffff"
            ),
            "discount" => array(
                "color" => "#ffffff",
                "background_color" => "#e50017"
            ),
            "button" => array(
                "color" => "#ffffff",
                "background_color" => "#00a72d"
            ),
            "news" => array(
                "color" => "#00a72d",
                "background_color" => "#ffffff"
            ),
            "comments" => array(
                "color" => "#ffffff",
                "background_color" => "#00a72d"
            ),
            "tabbar" => array(
                "color" => "#00a72d",
                "background_color" => "#ffffff",
                "image_color" => "#00a72d"
            )
        )
    ),
    "dj" => array(
        "layout_id" => $layouts["layout_2"]->getId(),
        "name" => "DJ",
        "overview" => "/dj/overview.png",
        "background_image" => "/../../images/templates/dj/640x1136.jpg",
        "background_image_hd" => "/../../images/templates/dj/1242x2208.jpg",
        "background_image_tablet" => "/../../images/templates/dj/1536x2048.jpg",
        "icon" => "/../../images/templates/dj/180x180.png",
        "startup_image" => "/../../images/templates/dj/640x960.png",
        "startup_image_retina" => "/../../images/templates/dj/640x1136.jpg",
        "startup_image_iphone_6" => "/../../images/templates/dj/750x1334.png",
        "startup_image_iphone_6_plus" => "/../../images/templates/dj/1242x2208.jpg",
        "startup_image_ipad_retina" => "/../../images/templates/dj/1536x2048.jpg",
        "blocks" => array(
            "header" => array(
                "color" => "#404040",
                "background_color" => "#e0c341"
            ),
            "subheader" => array(
                "color" => "#404040",
                "background_color" => "#f0d970"
            ),
            "connect_button" => array(
                "color" => "#404040",
                "background_color" => "#e0c341"
            ),
            "background" => array(
                "color" => "#f0d970",
                "background_color" => "#b65c12"
            ),
            "discount" => array(
                "color" => "#404040",
                "background_color" => "#e0c341"
            ),
            "button" => array(
                "color" => "#b65c12",
                "background_color" => "#e0c341"
            ),
            "news" => array(
                "color" => "#f0d970",
                "background_color" => "#b65c12"
            ),
            "comments" => array(
                "color" => "#404040",
                "background_color" => "#e0c341"
            ),
            "tabbar" => array(
                "color" => "#e0c341",
                "background_color" => "transparent",
                "image_color" => "#e0c341"
            )
        )
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
    $design = new Template_Model_Design();
    $design->find($code, "code");

    if(!$design->getId()) {
        $design->setData($data)
            ->setCode($code)
            ->save();

        if (!empty($data["blocks"])) {
            foreach ($data["blocks"] as $block_code => $block_data) {
                $block_data["design_id"] = $design->getId();
                $block_data["block_id"] = $block_ids[$block_code];
                $this->_db->insert("template_design_block", $block_data);
            }
        }
    }

}

// Assigning designs to categories
$categories_designs = array(
    "entertainment" => array(
        "fairground"
    ),
    "local_business" => array(
        "pizza"
    ),
    "music" => array(
        "dj"
    )
);

// Listing all design ids
$design_ids = array();
$design = new Template_Model_Design();
foreach ($design->findAll() as $design_data) {
    $design_ids[$design_data->getCode()] = $design_data->getId();
}

// Listing all category ids
$category_ids = array();
$category = new Template_Model_Category();
foreach ($category->findAll() as $category_data) {
    $category_ids[$category_data->getCode()] = $category_data->getId();
}

foreach ($categories_designs as $category_code => $design_codes) {
    $categories_designs_data = array("category_id" => $category_ids[$category_code]);

    foreach ($design_codes as $design_code) {
        $categories_designs_data["design_id"] = $design_ids[$design_code];
        $this->_db->insert("template_design_category", $categories_designs_data);
    }

}

// Assigning features to designs
$design_codes = array(
    "dj" => array(
        "newswall" => array("icon" => "/newswall/newswall2.png"),
        "music_gallery" => array("name" => "Playlists"),
        "push_notification" => array("name" => "Messages", "icon" => "/push_notifications/push2.png"),
        "image_gallery" => array("icon" => "/images/image5.png"),
        "facebook" => array(),
        "calendar" => array("icon" => "/calendar/calendar2.png"),
        "video_gallery" => array("icon" => "/videos/video2.png"),
        "custom_page" => array("name" => "About me"),
        "booking" => array("icon" => "/booking/booking4.png")
    ),
    "fairground" => array(
        "fanwall" => array("icon" => "/../../images/templates/fairground/icons/fanwall.png"),
        "loyalty" => array("name" => "Loyalty", "icon" => "/loyalty/loyalty4.png"),
        "social_gaming" => array("icon" => "/contest/contest4.png"),
        "discount" => array("name" => "Coupons", "icon" => "/discount/discount5.png"),
        "calendar" => array("icon" => "/calendar/calendar2.png"),
        "image_gallery" => array("icon" => "/images/image7.png"),
        "push_notification" => array("name" => "Push", "icon" => "/push_notifications/push3.png"),
        "video_gallery" => array(),
        "newswall" => array("name" => "News"),
        "facebook" => array()
    ),
    "pizza" => array(
        "m_commerce" => array("name" => "Orders"),
        "loyalty" => array("name" => "Loyalty"),
        "social_gaming" => array(),
        "discount" => array(),
        "facebook" => array(),
        "contact" => array()
    )
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
                    $icon->setLibraryId($option->getLibraryId())
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
            $design_content->setData($data)->save();
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
        "code" => "buttons",
        "name" => "Button",
        "color" => "#444",
        "color_variable_name" => '$button-custom-text',
        "background_color" => "#f8f8f8",
        "background_color_variable_name" => '$button-custom-bg',
        "border_color" => "#b2b2b2",
        "border_color_variable_name" => '$button-custom-border',
        "position" => "80"
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
    )
);


foreach($blocks as $data) {

    $data["type_id"] = 3;
    $block = new Template_Model_Block();
    $block->setData($data)->save();

    if(!empty($data["children"])) {

        $position = $block->getPosition();
        foreach($data["children"] as $child_data) {

            $position += 2;
            $child_data["type_id"] = 3;
            $child_data["parent_id"] = $block->getId();
            $child_data["position"] = $position;
            $child = new Template_Model_Block();
            $child->setData($child_data)->save();

        }
    }
}

// Listing all layouts
$layouts = array();
$layout = new Application_Model_Layout_Homepage();

foreach($layout->findAll() as $layout) {
    $layouts[$layout->getCode()] = $layout;
}

// Listings all block ids
$block_ids = array();
$blocks = new Template_Model_Block();

foreach($blocks->findAll() as $block) {
    $block_ids[$block->getCode()] = $block->getId();
    $children = $block->getChildren() ? $block->getChildren() : array($block);
    foreach($children as $child) {
        $block_ids[$child->getCode()] = $child->getId();
    }
}

// Inserting designs with blocks
$designs = array(
    "fairground" => array(
        "layout_id" => $layouts["layout_3"]->getId(),
        "name" => "Fairground",
        "blocks" => array(
            "header" => array(
                "color" => "#323b40",
                "background_color" => "#ee4b63"
            ),
            "background" => array(
                "color" => "#323b40",
                "background_color" => "#f9e4d1"
            ),
            "homepage" => array(
                "color" => "#ee4b63",
                "background_color" => "transparent",
                "border_color" => "#ffffff",
                "image_color" => "#ee4b63"
            ),
            "list_item_divider" => array(
                "color" => "#323b40",
                "background_color" => "#abbbdd"
            ),
            "list_item" => array(
                "color" => "#323b40",
                "background_color" => "#d6f1ef"
            ),
            "card_item_divider" => array(
                "color" => "#323b40",
                "background_color" => "#abbbdd"
            ),
            "card_item" => array(
                "color" => "#323b40",
                "background_color" => "#d6f1ef"
            ),
            "buttons" => array(
                "color" => "#323b40",
                "background_color" => "#6fb7b2",
                "border_color" => "#2a7e78"
            ),
            "checkbox_on" => array(
                "color" => "#323b40",
                "background_color" => "#6fb7b2"
            ),
            "checkbox_off" => array(
                "background_color" => "#323b40"
            ),
            "checkbox_general" => array(
                "color" => "#323b40",
                "background_color" => "#f3aa71"
            ),
            "radio" => array(
                "color" => "#323b40",
                "background_color" => "#6fb7b1"
            ),
            "toggle_on" => array(
                "background_color" => "#6fb7b1"
            ),
            "toggle_general" => array(
                "color" => "#323b40",
                "background_color" => "#d6f1ef"
            ),
            "toggle_off" => array(
                "background_color" => "transparent",
                "border_color" => "#e6e6e6"
            ),
            "toggle_handle_on" => array(
                "background_color" => "#ee4b63"
            ),
            "toggle_handle_off" => array(
                "background_color" => "#323b40"
            ),
            "tooltip" => array(
                "color" => "#323b40",
                "background_color" => "#ee4b63"
            ),
            "spinner_ios_text" => array(
                "background_color" => "#ee4b63"
            ),
            "spinner_android_text" => array(
                "background_color" => "#ee4b63"
            )
        )
    ),
    "pizza" => array(
        "layout_id" => $layouts["layout_9"]->getId(),
        "layout_visibility" => "toggle",
        "name" => "Pizza",
        "blocks" => array(
            "header" => array(
                "color" => "#ffffff",
                "background_color" => "#00a72d",
                "border_color" => "#00a72d"
            ),
            "background" => array(
                "background_color" => "#ffffff",
                "color" => "#00a72d"
            ),
            "homepage" => array(
                "color" => "#00a72d",
                "background_color" => "#ffffff",
                "border_color" => "#00a72d",
                "image_color" => "#00a72d"
            ),
            "list_item_divider" => array(
                "color" => "#ffffff",
                "background_color" => "#e50017"
            ),
            "list_item" => array(
                "color" => "#00a72d",
                "background_color" => "#ffffff"
            ),
            "card_item_divider" => array(
                "color" => "#ffffff",
                "background_color" => "#e50017"
            ),
            "card_item" => array(
                "color" => "#00a72d",
                "background_color" => "#ffffff"
            ),
            "buttons" => array(
                "color" => "#ffffff",
                "background_color" => "#00a72d",
                "border_color" => "#e50017"
            ),
            "checkbox_on" => array(
                "color" => "#ffffff",
                "background_color" => "#00a72d"
            ),
            "checkbox_off" => array(
                "background_color" => "#e50017"
            ),
            "radio" => array(
                "color" => "#00a72d",
                "background_color" => "#ffffff"
            ),
            "toggle_on" => array(
                "background_color" => "#e50017"
            ),
            "toggle_handle_on" => array(
                "background_color" => "#00a72d"
            ),
            "tooltip" => array(
                "color" => "#ffffff",
                "background_color" => "#00a72d"
            ),
            "spinner_ios_text" => array(
                "background_color" => "#e50017"
            ),
            "spinner_android_text" => array(
                "background_color" => "#e50017"
            )
        )
    ),
    "dj" => array(
        "layout_id" => $layouts["layout_2"]->getId(),
        "name" => "DJ",
        "blocks" => array(
            "header" => array(
                "color" => "#404040",
                "background_color" => "#e0c341"
            ),
            "background" => array(
                "background_color" => "#b65c12"
            ),
            "homepage" => array(
                "color" => "#e0c341",
                "background_color" => "transparent",
                "border_color" => "#ddd",
                "image_color" => "#e0c341"
            ),
            "list_item_divider" => array(
                "color" => "#f0d970",
                "background_color" => "#404040"
            ),
            "list_item" => array(
                "color" => "#404040",
                "background_color" => "#f0d970"
            ),
            "card_item_divider" => array(
                "color" => "#f0d970",
                "background_color" => "#404040"
            ),
            "card_item" => array(
                "color" => "#404040",
                "background_color" => "#f0d970"
            ),
            "buttons" => array(
                "color" => "#404040",
                "background_color" => "#f0d970",
                "border_color" => "#404040"
            ),
            "checkbox_on" => array(
                "color" => "#f0d970",
                "background_color" => "#365695"
            ),
            "checkbox_off" => array(
                "background_color" => "#404040"
            ),
            "checkbox_general" => array(
                "color" => "#404040",
                "background_color" => "#f0d970"
            ),
            "radio" => array(
                "color" => "#404040",
                "background_color" => "#f0d970"
            ),
            "toggle_on" => array(
                "background_color" => "#7c95c7"
            ),
            "toggle_general" => array(
                "color" => "#444",
                "background_color" => "#f0d970"
            ),
            "toggle_off" => array(
                "background_color" => "#ffffff",
                "border_color" => "#404040"
            ),
            "toggle_handle_on" => array(
                "background_color" => "#365695"
            ),
            "toggle_handle_off" => array(
                "background_color" => "#404040"
            ),
            "tooltip" => array(
                "color" => "#404040",
                "background_color" => "#f0d970"
            ),
            "spinner_ios_text" => array(
                "background_color" => "#f0d970"
            ),
            "spinner_android_text" => array(
                "background_color" => "#f0d970"
            )
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

                $this->_db->insert("template_design_block", $block_data);
            }
        }
    }
}
