<?php
$name = "Catalog";
$category = "monetization";

# Install icons
$icons = [
    [
        'path' => '/catalog/catalog1.png',
        'keywords' => 'fork,spoon,knife',
    ],
    [
        'path' => '/catalog/catalog2.png',
        'keywords' => 'fork,spoon,knife',
    ],
    [
        'path' => '/catalog/catalog3.png',
        'keywords' => 'fork,spoon,knife',
    ],
    [
        'path' => '/catalog/catalog4.png',
        'keywords' => 'fork,knife,plate',
    ],
    [
        'path' => '/catalog/catalog5.png',
        'keywords' => 'fork,knife,plate',
    ],
    [
        'path' => '/catalog/catalog6.png',
        'keywords' => 'carnet,notes',
    ],
    [
        'path' => '/catalog/catalog7.png',
        'keywords' => 'label,tag,price,sale',
    ],
    [
        'path' => '/catalog/catalog8.png',
        'keywords' => 'basket,cart',
    ],
    [
        'path' => '/catalog/catalog9.png',
        'keywords' => 'basket,cart',
    ],
    [
        'path' => '/catalog/catalog10.png',
        'keywords' => 'euro,money,currency,symbol',
    ],
    [
        'path' => '/catalog/catalog11.png',
        'keywords' => 'box,book',
    ],
];

$result = Siberian_Feature::installIcons($name, $icons);

# Install the Feature
$data = [
    'library_id' => $result["library_id"],
    'icon_id' => $result["icon_id"],
    'code' => 'catalog',
    'name' => $name,
    'model' => 'Catalog_Model_Category',
    'desktop_uri' => 'catalog/application/',
    'mobile_uri' => 'catalog/mobile_category_list/',
    "mobile_view_uri" => "catalog/mobile_category_product_view/",
    "mobile_view_uri_parameter" => "product_id",
    'only_once' => 0,
    'is_ajax' => 1,
    'position' => 30,
    'social_sharing_is_available' => 1,
];

$option = Siberian_Feature::install($category, $data, ['code']);

# Layouts
$layout_data = [1, 2, 3];
$slug = "catalog";

Siberian_Feature::installLayouts($option->getId(), $slug, $layout_data);

# Icons Flat
$icons = [
    [
        'path' => '/catalog/catalog1-flat.png',
        'keywords' => 'book',
    ],
    [
        'path' => '/catalog/catalog2-flat.png',
        'keywords' => 'book',
    ],
    [
        'path' => '/catalog/catalog3-flat.png',
        'keywords' => 'book',
    ],
];

Siberian_Feature::installIcons("{$name}-flat", $icons);
