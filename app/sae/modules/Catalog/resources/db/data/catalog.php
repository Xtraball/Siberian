<?php
$name = "Catalog";
$category = "monetization";

# Install icons
$icons = array(
    '/catalog/catalog1.png',
    '/catalog/catalog2.png',
    '/catalog/catalog3.png',
    '/catalog/catalog4.png',
    '/catalog/catalog5.png',
    '/catalog/catalog6.png',
    '/catalog/catalog7.png',
    '/promotion/discount4.png',
    '/catalog/catalog8.png',
    '/catalog/catalog9.png',
    '/catalog/catalog10.png',
    '/catalog/catalog11.png',
);

$result = Siberian_Feature::installIcons($name, $icons);

# Install the Feature
$data = array(
    'library_id'                    => $result["library_id"],
    'icon_id'                       => $result["icon_id"],
    'code'                          => 'catalog',
    'name'                          => $name,
    'model'                         => 'Catalog_Model_Category',
    'desktop_uri'                   => 'catalog/application/',
    'mobile_uri'                    => 'catalog/mobile_category_list/',
    "mobile_view_uri"               => "catalog/mobile_category_product_view/",
    "mobile_view_uri_parameter"     => "product_id",
    'only_once'                     => 0,
    'is_ajax'                       => 1,
    'position'                      => 30,
    'social_sharing_is_available'   => 1,
);

$option = Siberian_Feature::install($category, $data, array('code'));

# Layouts
$layout_data = array(1, 2, 3);
$slug = "catalog";

Siberian_Feature::installLayouts($option->getId(), $slug, $layout_data);

# Icons Flat
$icons = array(
    '/catalog/catalog1-flat.png',
    '/catalog/catalog2-flat.png',
    '/catalog/catalog3-flat.png',
);

Siberian_Feature::installIcons("{$name}-flat", $icons);
