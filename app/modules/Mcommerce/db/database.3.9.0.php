<?php
	 


$this->query("ALTER TABLE `mcommerce` ADD `show_ten` TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER`show_search` ;");
$this->query("ALTER TABLE `mcommerce` ADD `require_address` TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER`show_ten` ;");
$this->query("ALTER TABLE `mcommerce` ADD `age_control` INT( 1 ) NOT NULL DEFAULT '0' AFTER `require_address` ;");
$this->query("ALTER TABLE `mcommerce` ADD `age_minimum` TINYINT( 2 ) NULL DEFAULT NULL AFTER `age_control` ;");
$this->query("ALTER TABLE `mcommerce` ADD `require_datedelivery` TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER`require_address` ;");
$this->query("ALTER TABLE `mcommerce` ADD `show_customercomment` TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER`show_ten` ;");
$this->query("ALTER TABLE `mcommerce` ADD `mask_qty_opt` TINYINT( 1 ) NOT NULL DEFAULT  '0' AFTER `age_minimum` ;");

$this->query("ALTER TABLE `mcommerce_cart` ADD `customer_streetc` VARCHAR( 255 ) NULL AFTER`customer_street` ;");
$this->query("ALTER TABLE `mcommerce_order` ADD `customer_streetc` VARCHAR( 255 ) NULL AFTER`customer_street`;");
$this->query("ALTER TABLE `mcommerce_cart` ADD `customer_birthday` DATE NULL DEFAULT NULL AFTER`customer_city` ;");
$this->query("ALTER TABLE `mcommerce_order` ADD `customer_birthday` DATE NULL DEFAULT NULL AFTER`customer_city` ;");
$this->query("ALTER TABLE `mcommerce_cart` ADD `delivery_datetime` DATETIME NULL DEFAULT NULL AFTER`delivery_method_id` ;");
$this->query("ALTER TABLE `mcommerce_order` ADD `delivery_datetime` DATETIME NULL DEFAULT NULL AFTER `delivery_method_id` ;");
$this->query("ALTER TABLE `mcommerce_cart` ADD `delivery_comment` VARCHAR(150) NULL DEFAULT NULL AFTER`delivery_datetime` ;");
$this->query("ALTER TABLE `mcommerce_order` ADD `delivery_comment` VARCHAR(150) NULL DEFAULT NULL AFTER`delivery_datetime` ;");


