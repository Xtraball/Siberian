<?php

$this->query("ALTER TABLE `mcommerce_order` CHANGE `customer_firstname` `customer_firstname` VARCHAR(100) NULL DEFAULT NULL");
$this->query("ALTER TABLE `mcommerce_order` CHANGE `customer_lastname` `customer_lastname` VARCHAR(100) NULL DEFAULT NULL");
$this->query("ALTER TABLE `mcommerce_order` CHANGE `customer_phone` `customer_phone` VARCHAR(100) NULL DEFAULT NULL");
$this->query("UPDATE `application_option` SET `use_my_account` = '1' WHERE `application_option`.`code` = 'm_commerce'");

// To double
$tableFields = [
    "mcommerce_cart" => [
        "subtotal_excl_tax",
        "delivery_cost",
        "delivery_tax_rate",
        "total_excl_tax",
        "total_tax",
        "total",
        "paid_amount",
        "tip",
    ],
    "mcommerce_cart_line" => [
        "base_price",
        "base_price_incl_tax",
        "price",
        "price_incl_tax",
        "qty",
        "total",
        "total_incl_tax",
        "tax_rate",
    ],
    "mcommerce_order" => [
        "subtotal_excl_tax",
        "total_excl_tax",
        "total_tax",
        "delivery_cost",
        "total",
        "paid_amount",
        "tip",
    ],
    "mcommerce_order_line" => [
        "base_price",
        "base_price_incl_tax",
        "price",
        "price_incl_tax",
        "qty",
        "total",
        "total_incl_tax",
        "tax_rate",
    ],
    "mcommerce_promo" => [
        "minimum_amount",
        "discount",
    ],
    "mcommerce_promo_log" => [
        "ttc",
        "discount",
        "total",
    ],
    "mcommerce_store" => [
        "delivery_fees",
        "min_amount",
        "min_amount_free_delivery",
        "delivery_area",
        "delivery_time",
    ],
    "mcommerce_store_delivery_method" => [
        "price",
        "min_amount_for_free_delivery",
    ],
    "mcommerce_store_tax" => [
        "rate",
    ],
    "mcommerce_tax" => [
        "rate",
    ],
];

foreach ($tableFields as $table => $fields) {
    foreach ($fields as $field) {
        try {
            $this->query("ALTER TABLE `$table` CHANGE `$field` `$field` DOUBLE UNSIGNED NOT NULL;");
        } catch (\Exception $e) {
            // Nope!
        }
    }
}