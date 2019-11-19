<?php

# MCommerce special
$datas = [
    ['code' => 'in_store', 'name' => 'In store', 'is_free' => 1],
    ['code' => 'carry_out', 'name' => 'Carry Out', 'is_free' => 1],
    ['code' => 'home_delivery', 'name' => 'Delivery', 'is_free' => 0]
];

foreach ($datas as $data) {
    $method = new Mcommerce_Model_Delivery_Method();
    $method
        ->setData($data)
        ->insertOrUpdate(["code"]);
}

$datas = [
    [
        'code' => 'paypal',
        'name' => 'Paypal',
        'online_payment' => 1
    ],
    [
        'code' => 'cash',
        'name' => 'Cash',
        'online_payment' => 0
    ],
    [
        'code' => 'check',
        'name' => 'Check',
        'online_payment' => 0
    ],
    [
        'code' => 'meal_voucher',
        'name' => 'Meal Voucher',
        'online_payment' => 0
    ],
    [
        'code' => 'cc_upon_delivery',
        'name' => 'Credit card (pay upon pickup or delivery)',
        'online_payment' => 0
    ],
    [
        'code' => 'stripe',
        'name' => 'Credit card (online payment)',
        'online_payment' => 1
    ],
];

foreach ($datas as $data) {
    $method = new Mcommerce_Model_Payment_Method();
    $method
        ->setData($data)
        ->insertOnce(["code"]);
}