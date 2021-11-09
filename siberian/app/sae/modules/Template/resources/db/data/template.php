<?php

try {
    $this->query("ALTER TABLE `template_design` CHANGE `code` `code` VARCHAR(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;");
} catch (\Exception $e) {
    // Silent!
}

try {
    $this->query("ALTER TABLE `template_category` CHANGE `code` `code` VARCHAR(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;");
} catch (\Exception $e) {
    // Silent!
}

try {
    $this->query("ALTER TABLE `template_category` CHANGE `name` `name` VARCHAR(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;");
} catch (\Exception $e) {
    // Silent!
}

// Mobile Blocks
$datas = [
    [
        'code' => 'header',
        'name' => 'Header',
        'use_color' => 1,
        'color' => '#00377a',
        'use_background_color' => 1,
        'background_color' => '#739c03',
        'position' => 10
    ],
    [
        'code' => 'subheader',
        'name' => 'Subheader',
        'use_color' => 1,
        'color' => '#00377a',
        'use_background_color' => 1,
        'background_color' => '#739c03',
        'position' => 20
    ],
    [
        'code' => 'connect_button',
        'name' => 'Connect Button',
        'use_color' => 1,
        'color' => '#233799',
        'use_background_color' => 1,
        'background_color' => '#f2f2f2',
        'position' => 30
    ],
    [
        'code' => 'background',
        'name' => 'Background',
        'use_color' => 1,
        'color' => '#ffffff',
        'use_background_color' => 1,
        'background_color' => '#0c6ec4',
        'position' => 40
    ],
    [
        'code' => 'discount',
        'name' => 'Discount Zone',
        'use_color' => 1,
        'color' => '#fcfcfc',
        'use_background_color' => 1,
        'background_color' => '#739c03',
        'position' => 50
    ],
    [
        'code' => 'button',
        'name' => 'Button',
        'use_color' => 1,
        'color' => '#fcfcfc',
        'use_background_color' => 1,
        'background_color' => '#00377a',
        'position' => 60
    ],
    [
        'code' => 'news',
        'name' => 'News',
        'use_color' => 1,
        'color' => '#fcfcfc',
        'use_background_color' => 1,
        'background_color' => '#00377a',
        'position' => 70
    ],
    [
        'code' => 'comments',
        'name' => 'Comments',
        'use_color' => 1,
        'color' => '#ffffff',
        'use_background_color' => 1,
        'background_color' => '#4d5d8a',
        'position' => 80
    ],
    [
        'code' => 'tabbar',
        'name' => 'Tabbar',
        'use_color' => 1,
        'color' => '#ffffff',
        'use_background_color' => 1,
        'background_color' => '#739c03',
        'image_color' => '#ffffff',
        'position' => 90
    ]
];

foreach ($datas as $data) {
    $data['type_id'] = 1;
    $block = new Template_Model_Block();
    $block
        ->setData($data)
        ->insertOrUpdate(['code', 'type_id']);
}


# Listing all layouts
$layouts = [];
$layout = new Application_Model_Layout_Homepage();

foreach ($layout->findAll() as $layout) {
    $layouts[$layout->getCode()] = $layout;
}

# Listings all block ids
$block_ids = [];
$block = new Template_Model_Block();
foreach ($block->findAll() as $block) {
    $block_ids[$block->getCode()] = $block->getId();
}


$blocks = [
    /* GENERAL */
    [
        'code' => 'background',
        'name' => 'General',
        'color' => '#111',
        'color_variable_name' => '$general-custom-text',
        'background_color' => '#ededed',
        'background_color_variable_name' => '$general-custom-bg',
        'border_color' => '#ddd',
        'border_color_variable_name' => '$general-custom-border',
        'position' => '10'
    ],
    /* HOMEPAGE */
    [
        'code' => 'homepage',
        'name' => 'Homepage',
        'color' => '#111',
        'color_variable_name' => '$homepage-custom-text',
        'background_color' => '#fff',
        'background_color_variable_name' => '$homepage-custom-bg',
        'border_color' => '#ddd',
        'border_color_variable_name' => '$homepage-custom-border',
        'image_color' => '#ddd',
        'image_color_variable_name' => '$homepage-custom-image',
        'position' => '11'
    ],
    /* HEADER */
    [
        'code' => 'header',
        'name' => 'Header',
        'color' => '#444',
        'color_variable_name' => '$bar-custom-text',
        'background_color' => '#f8f8f8',
        'background_color_variable_name' => '$bar-custom-bg',
        'border_color' => '#b2b2b2',
        'border_color_variable_name' => '$bar-custom-border',
        'position' => '12'
    ],

    /* CARD */
    [
        'code' => 'card',
        'name' => 'Card',
        'position' => '20',
        'children' => [
            [
                'code' => 'card_item_divider',
                'name' => 'Title\'s Card',
                'color' => '#222',
                'color_variable_name' => '$card-item-divider-custom-text',
                'border_color' => '#f8f8f8',
                'border_color_variable_name' => '$card-item-divider-custom-border',
                'background_color' => '#f8f8f8',
                'background_color_variable_name' => '$card-item-divider-custom-bg'
            ], [
                'code' => 'card_item',
                'name' => 'Card Item',
                'color' => '#444',
                'color_variable_name' => '$card-item-custom-text',
                'border_color' => '#fff',
                'border_color_variable_name' => '$card-item-custom-border',
                'background_color' => '#fff',
                'background_color_variable_name' => '$card-item-custom-bg'
            ]
        ]
    ],
    /* LIST */
    [
        'code' => 'list',
        'name' => 'List',
        'position' => '21',
        'children' => [
            [
                'code' => 'list_item_divider',
                'name' => 'Title\'s List',
                'color' => '#222',
                'color_variable_name' => '$list-item-divider-custom-text',
                'border_color' => '#f8f8f8',
                'border_color_variable_name' => '$list-item-divider-custom-border',
                'background_color' => '#f8f8f8',
                'background_color_variable_name' => '$list-item-divider-custom-bg'
            ], [
                'code' => 'list_item',
                'name' => 'List Item',
                'color' => '#444',
                'color_variable_name' => '$list-item-custom-text',
                'border_color' => '#444',
                'border_color_variable_name' => '$list-item-custom-border',
                'background_color' => '#fff',
                'background_color_variable_name' => '$list-item-custom-bg'
            ]
        ]
    ],
    /* SPINNER */
    [
        'code' => 'spinner',
        'name' => 'Spinner',
        'position' => '22',
        'children' => [
            [
                'code' => 'spinner_ios_text',
                'name' => 'iOS Spinner',
                'background_color' => '#69717d',
                'background_color_variable_name' => '$spinner-custom-ios-bg'
            ], [
                'code' => 'spinner_android_text',
                'name' => 'Android Spinner',
                'background_color' => '#4b8bf4',
                'background_color_variable_name' => '$spinner-custom-android-bg'
            ],
        ]
    ],
    /* BUTTONS */
    [
        'code' => 'buttons_group',
        'name' => 'Buttons',
        'position' => '80',
        'children' => [
            [
                'code' => 'buttons',
                'name' => 'Button',
                'more' => 'phone, locate, facebook, email, etc..',
                'color' => '#444',
                'color_variable_name' => '$button-custom-text',
                'background_color' => '#f8f8f8',
                'background_color_variable_name' => '$button-custom-bg',
                'border_color' => '#b2b2b2',
                'border_color_variable_name' => '$button-custom-border',
            ],
            [
                'code' => 'buttons_light',
                'name' => 'Button light',
                'color' => '#444',
                'color_variable_name' => '$button-light-custom-text',
                'background_color' => '#ffffff',
                'background_color_variable_name' => '$button-light-custom-bg',
                'border_color' => '#dddddd',
                'border_color_variable_name' => '$button-light-custom-border',
            ],
            [
                'code' => 'buttons_positive',
                'name' => 'Button positive',
                'more' => 'form submit, search, validation, confirmation',
                'color' => '#ffffff',
                'color_variable_name' => '$button-positive-custom-text',
                'background_color' => '#387ef5',
                'background_color_variable_name' => '$button-positive-custom-bg',
                'border_color' => '#0c60ee',
                'border_color_variable_name' => '$button-positive-custom-border',
            ],
            [
                'code' => 'buttons_calm',
                'name' => 'Button calm',
                'more' => 'informative, modal',
                'color' => '#ffffff',
                'color_variable_name' => '$button-calm-custom-text',
                'background_color' => '#11c1f3',
                'background_color_variable_name' => '$button-calm-custom-bg',
                'border_color' => '#0a9dc7',
                'border_color_variable_name' => '$button-calm-custom-border',
            ],
            [
                'code' => 'buttons_balanced',
                'name' => 'Button balanced',
                'more' => 'contextual, depends on module/layout',
                'color' => '#ffffff',
                'color_variable_name' => '$button-balanced-custom-text',
                'background_color' => '#33cd5f',
                'background_color_variable_name' => '$button-balanced-custom-bg',
                'border_color' => '#28a54c',
                'border_color_variable_name' => '$button-balanced-custom-border',
            ],
            [
                'code' => 'buttons_energized',
                'name' => 'Button energized',
                'more' => 'contextual, depends on module/layout',
                'color' => '#ffffff',
                'color_variable_name' => '$button-energized-custom-text',
                'background_color' => '#ffc900',
                'background_color_variable_name' => '$button-energized-custom-bg',
                'border_color' => '#e6b500',
                'border_color_variable_name' => '$button-energized-custom-border',
            ],
            [
                'code' => 'buttons_assertive',
                'name' => 'Button assertive',
                'more' => 'confirm action, deletion, etc ...',
                'color' => '#ffffff',
                'color_variable_name' => '$button-assertive-custom-text',
                'background_color' => '#ef473a',
                'background_color_variable_name' => '$button-assertive-custom-bg',
                'border_color' => '#e42112',
                'border_color_variable_name' => '$button-assertive-custom-border',
            ],
            [
                'code' => 'buttons_royal',
                'name' => 'Button royal',
                'more' => 'contextual, depends on module/layout',
                'color' => '#ffffff',
                'color_variable_name' => '$button-royal-custom-text',
                'background_color' => '#886aea',
                'background_color_variable_name' => '$button-royal-custom-bg',
                'border_color' => '#6b46e5',
                'border_color_variable_name' => '$button-royal-custom-border',
            ],
            [
                'code' => 'buttons_dark',
                'name' => 'Button dark',
                'more' => 'contextual, depends on module/layout',
                'color' => '#ffffff',
                'color_variable_name' => '$button-dark-custom-text',
                'background_color' => '#444444',
                'background_color_variable_name' => '$button-dark-custom-bg',
                'border_color' => '#111111',
                'border_color_variable_name' => '$button-dark-custom-border',
            ],
        ],
    ],
    /* BADGES */
    [
        'code' => 'badges_group',
        'name' => 'Badges',
        'position' => '85',
        'children' => [
            [
                'code' => 'badges',
                'name' => 'Badges',
                'color' => '#444',
                'color_variable_name' => '$badge-custom-text',
                'background_color' => '#f8f8f8',
                'background_color_variable_name' => '$badge-custom-bg',
            ],
            [
                'code' => 'badges_light',
                'name' => 'badge light',
                'color' => '#444',
                'color_variable_name' => '$badge-light-custom-text',
                'background_color' => '#ffffff',
                'background_color_variable_name' => '$badge-light-custom-bg',
            ],
            [
                'code' => 'badges_positive',
                'name' => 'badge positive',
                'color' => '#ffffff',
                'color_variable_name' => '$badge-positive-custom-text',
                'background_color' => '#387ef5',
                'background_color_variable_name' => '$badge-positive-custom-bg',
            ],
            [
                'code' => 'badges_calm',
                'name' => 'badge calm',
                'color' => '#ffffff',
                'color_variable_name' => '$badge-calm-custom-text',
                'background_color' => '#11c1f3',
                'background_color_variable_name' => '$badge-calm-custom-bg',
            ],
            [
                'code' => 'badges_balanced',
                'name' => 'badge balanced',
                'color' => '#ffffff',
                'color_variable_name' => '$badge-balanced-custom-text',
                'background_color' => '#33cd5f',
                'background_color_variable_name' => '$badge-balanced-custom-bg',
            ],
            [
                'code' => 'badges_energized',
                'name' => 'badge energized',
                'color' => '#ffffff',
                'color_variable_name' => '$badge-energized-custom-text',
                'background_color' => '#ffc900',
                'background_color_variable_name' => '$badge-energized-custom-bg',
            ],
            [
                'code' => 'badges_assertive',
                'name' => 'badge assertive',
                'color' => '#ffffff',
                'color_variable_name' => '$badge-assertive-custom-text',
                'background_color' => '#ef473a',
                'background_color_variable_name' => '$badge-assertive-custom-bg',
            ],
            [
                'code' => 'badges_royal',
                'name' => 'badge royal',
                'color' => '#ffffff',
                'color_variable_name' => '$badge-royal-custom-text',
                'background_color' => '#886aea',
                'background_color_variable_name' => '$badge-royal-custom-bg',
            ],
            [
                'code' => 'badges_dark',
                'name' => 'badge dark',
                'color' => '#ffffff',
                'color_variable_name' => '$badge-dark-custom-text',
                'background_color' => '#444444',
                'background_color_variable_name' => '$badge-dark-custom-bg',
            ],
        ],
    ],
    /* UTILS */
    [
        'code' => 'utils_group',
        'name' => 'Utils',
        'position' => '80',
        'children' => [
            [
                'code' => 'util_light',
                'name' => 'Util light',
                'color' => '#444',
                'color_variable_name' => '$util-light-custom-text',
                'background_color' => '#444',
                'background_color_variable_name' => '$util-light-custom-bg',
                'border_color' => '#444',
                'border_color_variable_name' => '$util-light-custom-border',
            ],
            [
                'code' => 'util_positive',
                'name' => 'Util positive',
                'color' => '#387ef5',
                'color_variable_name' => '$util-positive-custom-text',
                'background_color' => '#387ef5',
                'background_color_variable_name' => '$util-positive-custom-bg',
                'border_color' => '#387ef5',
                'border_color_variable_name' => '$util-positive-custom-border',
            ],
            [
                'code' => 'util_calm',
                'name' => 'Util calm',
                'color' => '#11c1f3',
                'color_variable_name' => '$util-calm-custom-text',
                'background_color' => '#11c1f3',
                'background_color_variable_name' => '$util-calm-custom-bg',
                'border_color' => '#11c1f3',
                'border_color_variable_name' => '$util-calm-custom-border',
            ],
            [
                'code' => 'util_balanced',
                'name' => 'Util balanced',
                'color' => '#33cd5f',
                'color_variable_name' => '$util-balanced-custom-text',
                'background_color' => '#33cd5f',
                'background_color_variable_name' => '$util-balanced-custom-bg',
                'border_color' => '#33cd5f',
                'border_color_variable_name' => '$util-balanced-custom-border',
            ],
            [
                'code' => 'util_energized',
                'name' => 'Util energized',
                'color' => '#ffc900',
                'color_variable_name' => '$util-energized-custom-text',
                'background_color' => '#ffc900',
                'background_color_variable_name' => '$util-energized-custom-bg',
                'border_color' => '#ffc900',
                'border_color_variable_name' => '$util-energized-custom-border',
            ],
            [
                'code' => 'util_assertive',
                'name' => 'Util assertive',
                'color' => '#ef473a',
                'color_variable_name' => '$util-assertive-custom-text',
                'background_color' => '#ef473a',
                'background_color_variable_name' => '$util-assertive-custom-bg',
                'border_color' => '#ef473a',
                'border_color_variable_name' => '$util-assertive-custom-border',
            ],
            [
                'code' => 'util_royal',
                'name' => 'Util royal',
                'color' => '#886aea',
                'color_variable_name' => '$util-royal-custom-text',
                'background_color' => '#886aea',
                'background_color_variable_name' => '$util-royal-custom-bg',
                'border_color' => '#886aea',
                'border_color_variable_name' => '$util-royal-custom-border',
            ],
            [
                'code' => 'util_dark',
                'name' => 'Util dark',
                'color' => '#111111',
                'color_variable_name' => '$util-dark-custom-text',
                'background_color' => '#111111',
                'background_color_variable_name' => '$util-dark-custom-bg',
                'border_color' => '#111111',
                'border_color_variable_name' => '$util-dark-custom-border',
            ],
        ],
    ],
    /* CHECKBOX */
    [
        'code' => 'checkbox',
        'name' => 'Checkbox',
        'position' => '90',
        'children' => [
            [
                'code' => 'checkbox_general',
                'name' => 'General',
                'background_color' => '#fff',
                'background_color_variable_name' => '$checkbox-general-custom-bg',
                'color' => '#444',
                'color_variable_name' => '$checkbox-general-custom-text'
            ], [
                'code' => 'checkbox_on',
                'name' => 'Checkbox on',
                'background_color' => '#387ef5',
                'background_color_variable_name' => '$checkbox-on-custom-bg',
                'color' => '#fff',
                'color_variable_name' => '$checkbox-on-custom-text'
            ], [
                'code' => 'checkbox_off',
                'name' => 'Checkbox off',
                'background_color' => '#fff',
                'background_color_variable_name' => '$checkbox-off-custom-bg'
            ],
        ]
    ],
    /* RADIO */
    [
        'code' => 'radio',
        'name' => 'Radio',
        'color' => '#444',
        'color_variable_name' => '$radio-custom-text',
        'background_color' => '#fff',
        'background_color_variable_name' => '$radio-custom-bg',
        'position' => '100'
    ],
    /* TOGGLE */
    [
        'code' => 'toggle',
        'name' => 'Toggle',
        'position' => '110',
        'children' => [
            [
                'code' => 'toggle_general',
                'name' => 'General',
                'color' => '#444',
                'color_variable_name' => '$toggle-general-custom-text',
                'background_color' => '#fff',
                'background_color_variable_name' => '$toggle-general-custom-bg'
            ], [
                'code' => 'toggle_on',
                'name' => 'Toggle on',
                'background_color' => '#387ef5',
                'background_color_variable_name' => '$toggle-on-custom-bg'
            ], [
                'code' => 'toggle_off',
                'name' => 'Toggle off',
                'background_color' => '#fff',
                'background_color_variable_name' => '$toggle-off-custom-bg',
                'border_color' => '#e6e6e6',
                'border_color_variable_name' => '$toggle-off-custom-border'
            ], [
                'code' => 'toggle_handle_on',
                'name' => 'Toggle\'s Handle on',
                'background_color' => '#fff',
                'background_color_variable_name' => '$toggle-handle-on-custom-bg'
            ], [
                'code' => 'toggle_handle_off',
                'name' => 'Toggle\'s Handle off',
                'background_color' => '#fff',
                'background_color_variable_name' => '$toggle-handle-off-custom-bg'
            ],
        ]
    ],
    /* TOOLTIP */
    [
        'code' => 'tooltip',
        'name' => 'Tooltip',
        'color' => '#fff',
        'color_variable_name' => '$tooltip-custom-text',
        'background_color' => '#444',
        'background_color_variable_name' => '$tooltip-custom-bg',
        'position' => '120'
    ],
    /* ICON */
    [
        'code' => 'icons',
        'name' => 'Icons',
        'position' => '125',
        'children' => [
            [
                'code' => 'icon',
                'name' => 'Icon',
                'color' => '#fff',
                'color_variable_name' => '$icon-custom',
            ], [
                'code' => 'icon_active',
                'name' => 'Icon active',
                'color' => '#444',
                'color_variable_name' => '$icon-active-custom',
            ], [
                'code' => 'icon_inactive',
                'name' => 'Icon inactive',
                'color' => '#606060',
                'color_variable_name' => '$icon-inactive-custom',
            ], [
                'code' => 'icon_warning',
                'name' => 'Icon warning',
                'color' => '#e67f00',
                'color_variable_name' => '$icon-warning-custom',
            ], [
                'code' => 'icon_danger',
                'name' => 'Icon danger',
                'color' => '#ad3830',
                'color_variable_name' => '$icon-danger-custom',
            ]
        ]
    ],

    /* DIALOG */
    [
        'code' => 'dialog',
        'name' => 'Dialog',
        'position' => '135',
        'children' => [
            [
                'code' => 'dialog_text',
                'name' => 'Dialog text',
                'color' => '#000',
                'color_variable_name' => '$dialog-custom-text',
            ], [
                'code' => 'dialog_bg',
                'name' => 'Dialog background',
                'color' => '#fff',
                'color_variable_name' => '$dialog-custom-bg',
            ], [
                'code' => 'dialog_button',
                'name' => 'Dialog button',
                'color' => '#007aff',
                'color_variable_name' => '$dialog-custom-button',
            ],
        ]
    ],
    /* E-MAIL */
    [
        'code' => 'emails_group',
        'name' => 'E-mails',
        'position' => '150',
        'children' => [
            [
                'code' => 'description',
                'name' => 'Description',
                'more' => 'This section allows you to customize the e-mails sent from the Application.',
            ],
            [
                'code' => 'email_body',
                'name' => 'Body',
                'background_color' => '#e1e1e1',
                'background_color_variable_name' => '$email-body-custom-bg',
            ],
            [
                'code' => 'email_header',
                'name' => 'Header',
                'color' => '#ffffff',
                'color_variable_name' => '$email-header-custom-text',
                'background_color' => '#93b5cd',
                'background_color_variable_name' => '$email-header-custom-bg'
            ],
            [
                'code' => 'email_content',
                'name' => 'Content',
                'color' => '#888888',
                'color_variable_name' => '$email-content-custom-text',
                'background_color' => '#ffffff',
                'background_color_variable_name' => '$email-content-custom-bg'
            ],
            [
                'code' => 'email_footer',
                'name' => 'Footer',
                'color' => '#606060',
                'color_variable_name' => '$email-footer-custom-text',
            ],
            [
                'code' => 'email_button_primary',
                'name' => 'Button primary',
                'color' => '#ffffff',
                'color_variable_name' => '$email-button-primary-custom-text',
                'background_color' => '#3498db',
                'background_color_variable_name' => '$email-button-primary-custom-bg'
            ],
            [
                'code' => 'email_button_danger',
                'name' => 'Button danger',
                'color' => '#ffffff',
                'color_variable_name' => '$email-button-danger-custom-text',
                'background_color' => '#c10600',
                'background_color_variable_name' => '$email-button-danger-custom-bg'
            ],
        ],
    ],
];


foreach ($blocks as $data) {

    $data['type_id'] = 3;
    $block = new Template_Model_Block();
    $block
        ->setData($data)
        ->insertOrUpdate(['code', 'type_id']);

    if (!empty($data['children'])) {

        $position = $block->getPosition();
        foreach ($data['children'] as $child_data) {

            $position += 2;
            $child_data['type_id'] = 3;
            $child_data['parent_id'] = $block->getId();
            $child_data['position'] = $position;
            $child = new Template_Model_Block();
            $child
                ->setData($child_data)
                ->insertOrUpdate(['code', 'type_id']);

        }
    }
}

# Listing all layouts
$layouts = [];
$layout = new Application_Model_Layout_Homepage();

foreach ($layout->findAll() as $layout) {
    $layouts[$layout->getCode()] = $layout;
}

# Listings all block ids
$block_ids = [];
$blocks = new Template_Model_Block();

foreach ($blocks->findAll() as $block) {
    $block_ids[$block->getCode()] = $block->getId();
    $children = $block->getChildren() ? $block->getChildren() : [$block];
    foreach ($children as $child) {
        $block_ids[$child->getCode()] = $child->getId();
    }
}


// Removes `util` blocks
$block = (new Template_Model_Block())->find("util", "code");
if ($block->getId()) {
    try {
        $this->query("DELETE FROM template_block_app WHERE block_id = '" . $block->getId() . "';");
    } catch (\Exception $e) {
        // Silently fails!
    }
    $block->delete();
}