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
        'deprecated' => false,
        'icon_path' => '/magento/magento1.png',
        'icons_flat' => array(
            '/link/linkmagento-1-flat.png',
            '/link/linkmagento-2-flat.png',
            '/link/linkmagento-3-flat.png',
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
    'WooCommerce Link' => array(
        'deprecated' => true,
        'icon_path' => '/woocommerce/woocommerce1.png',
        'icons_flat' => array(
            '/link/linkwoocommerce-1-flat.png',
            '/link/linkwoocommerce-2-flat.png',
            '/link/linkwoocommerce-3-flat.png',
            '/woocommerce/woocommerce1-flat.png',
            '/woocommerce/woocommerce2-flat.png',
        ),
        'datas' => array(
            'code' => 'woocommerce',
            'name' => 'WooCommerce Link',
            'model' => 'Weblink_Model_Type_Mono',
            'desktop_uri' => 'weblink/application_woocommerce/',
            'mobile_uri' => 'weblink/mobile_mono/'
        )
    ),
    'Prestashop' => array(
        'deprecated' => false,
        'icon_path' => '/prestashop/prestashop1.png',
        'icons_flat' => array(
            '/link/linkprestashop-1-flat.png',
            '/link/linkprestashop-2-flat.png',
            '/link/linkprestashop-3-flat.png',
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
        'deprecated' => false,
        'icon_path' => '/volusion/volusion1.png',
        'icons_flat' => array(
            '/link/linkvolusion-1-flat.png',
            '/link/linkvolusion-2-flat.png',
            '/link/linkvolusion-3-flat.png',
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
        'deprecated' => false,
        'icon_path' => '/shopify/shopify1.png',
        'icons_flat' => array(
            '/link/linkshopify-1-flat.png',
            '/link/linkshopify-2-flat.png',
            '/link/linkshopify-3-flat.png',
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

if(!Installer_Model_Installer::isInstalled()) {
    unset($features['WooCommerce Link']);
}

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
    Siberian_Feature::removeIcons("{$feature_name}-flat");
    Siberian_Feature::installIcons("{$feature_name}-flat", $feature['icons_flat']);
}


# Icons Flat
$icons = array(
    '/weblink/link1-flat.png',
    '/weblink/link2-flat.png',
    '/weblink/link3-flat.png'
);

Siberian_Feature::installIcons("{$name}-flat", $icons);