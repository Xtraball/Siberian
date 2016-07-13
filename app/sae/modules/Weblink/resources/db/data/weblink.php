<?php
$name = "Weblink";
$category = "integration";

# Install icons
$icons = array(
    '/weblink/link1.png',
    '/weblink/link2.png',
    '/weblink/link3.png'
);

$result = Siberian_Feature::installIcons($name, $icons);

# Option weblink_mono
$data = array(
    'library_id'    => $result["library_id"],
    'icon_id'       => $result["icon_id"],
    'code'          => "weblink_mono",
    'name'          => "Link",
    'model'         => "Weblink_Model_Type_Mono",
    'desktop_uri'   => "weblink/application_mono/",
    'mobile_uri'    => "weblink/mobile_mono/",
    'only_once'     => 0,
    'is_ajax'       => 0,
    'position'      => 150
);

$link_option = Siberian_Feature::install($category, $data, array('code'));


# Option weblink_multi
$data = array(
    'library_id'    => $result["library_id"],
    'icon_id'       => $result["icon_id"],
    'code'          => "weblink_multi",
    'name'          => "Links",
    'model'         => "Weblink_Model_Type_Multi",
    'desktop_uri'   => "weblink/application_multi/",
    'mobile_uri'    => "weblink/mobile_multi/",
    'only_once'     => 0,
    'is_ajax'       => 1,
    'position'      => 160
);

$links_option = Siberian_Feature::install($category, $data, array('code'));


$features = array(
    'Magento' => array(
        'icon_path' => '/magento/magento1.png',
        'icons_flat' => array(
            '/magento/magento1-flat.png',
            '/magento/magento2-flat.png',
        ),
        'datas' => array(
            'code' => 'magento',
            'name' => 'Magento',
            'model' => 'Weblink_Model_Type_Mono',
            'desktop_uri' => 'weblink/application_magento/',
            'mobile_uri' => 'weblink/mobile_mono/'
        )
    ),
    'WooCommerce' => array(
        'icon_path' => '/woocommerce/woocommerce1.png',
        'icons_flat' => array(
            '/woocommerce/woocommerce1-flat.png',
            '/woocommerce/woocommerce2-flat.png',
        ),
        'datas' => array(
            'code' => 'woocommerce',
            'name' => 'WooCommerce',
            'model' => 'Weblink_Model_Type_Mono',
            'desktop_uri' => 'weblink/application_woocommerce/',
            'mobile_uri' => 'weblink/mobile_mono/'
        )
    ),
    'Prestashop' => array(
        'icon_path' => '/prestashop/prestashop1.png',
        'icons_flat' => array(
            '/prestashop/prestashop1-flat.png',
            '/prestashop/prestashop2-flat.png',
            '/prestashop/prestashop3-flat.png',
        ),
        'datas' => array(
            'code' => 'prestashop',
            'name' => 'Prestashop',
            'model' => 'Weblink_Model_Type_Mono',
            'desktop_uri' => 'weblink/application_prestashop/',
            'mobile_uri' => 'weblink/mobile_mono/'
        )
    ),
    'Volusion' => array(
        'icon_path' => '/volusion/volusion1.png',
        'icons_flat' => array(
            '/volusion/volusion1-flat.png',
            '/volusion/volusion2-flat.png',
        ),
        'datas' => array(
            'code' => 'volusion',
            'name' => 'Volusion',
            'model' => 'Weblink_Model_Type_Mono',
            'desktop_uri' => 'weblink/application_volusion/',
            'mobile_uri' => 'weblink/mobile_mono/'
        )
    ),
    'Shopify' => array(
        'icon_path' => '/shopify/shopify1.png',
        'icons_flat' => array(
            '/shopify/shopify1-flat.png',
            '/shopify/shopify2-flat.png',
            '/shopify/shopify3-flat.png',
        ),
        'datas' => array(
            'code' => 'shopify',
            'name' => 'Shopify',
            'model' => 'Weblink_Model_Type_Mono',
            'desktop_uri' => 'weblink/application_shopify/',
            'mobile_uri' => 'weblink/mobile_mono/'
        )
    )
);

foreach($features as $feature_name => $feature) {

    $icons = array(
        $feature['icon_path'],
    );

    $result = Siberian_Feature::installIcons($feature_name, $icons);

    $data = array_merge(
        $feature['datas'],
        array(
            'library_id'    => $result["library_id"],
            'icon_id'       => $result["icon_id"],
            'only_once'     => 0,
            'is_ajax'       => 0,
            'position'      => 155
        )
    );

    $link_option = Siberian_Feature::install($category, $data, array('code'));

    # Icons Flat
    Siberian_Feature::installIcons("{$feature_name}-flat", $feature['icons_flat']);
    
}


# Icons Flat
$icons = array(
    '/weblink/link1-flat.png',
    '/weblink/link2-flat.png',
    '/weblink/link3-flat.png'
);

Siberian_Feature::installIcons("{$name}-flat", $icons);