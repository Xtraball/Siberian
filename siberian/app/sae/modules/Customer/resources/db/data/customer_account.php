<?php
$name = 'My account';
$category = 'contact';

# Install icons
$icons = [
    '/tabbar/user_flat1.png',
    '/tabbar/user_flat2.png',
    '/tabbar/user_flat3.png',
    '/tabbar/user_account-flat.png',
    '/tabbar/user_account1-flat.png',
    '/tabbar/user_account2-flat.png',
];

$result = Siberian_Feature::installIcons($name, $icons);

# Install the Feature
$data = [
    'library_id' => $result['library_id'],
    'icon_id' => $result['icon_id'],
    'code' => 'tabbar_account',
    'name' => $name,
    'model' => 'Customer_Model_Customer',
    'desktop_uri' => 'customer/account/',
    'mobile_uri' => 'customer/mobile_view/',
    'mobile_view_uri' => 'customer/mobile_view/',
    'mobile_view_uri_parameter' => null,
    'only_once' => 1,
    'is_ajax' => 1,
    'position' => 140
];

$option = Siberian_Feature::install($category, $data, ['code']);

# Layouts
$layoutData = [1];
$slug = "tabbar_account";

Siberian_Feature::installLayouts($option->getId(), $slug, $layoutData);

# Icons Flat
$icons = [
    '/tabbar/user_flat1.png',
    '/tabbar/user_flat2.png',
    '/tabbar/user_flat3.png',
    '/tabbar/user_account-flat.png',
    '/tabbar/user_account1-flat.png',
    '/tabbar/user_account2-flat.png',
];

Siberian_Feature::installIcons("{$name}-flat", $icons);
