<?php

$this->query("ALTER TABLE `mcommerce_order` CHANGE `customer_firstname` `customer_firstname` VARCHAR(100) NULL DEFAULT NULL");
$this->query("ALTER TABLE `mcommerce_order` CHANGE `customer_lastname` `customer_lastname` VARCHAR(100) NULL DEFAULT NULL");
$this->query("ALTER TABLE `mcommerce_order` CHANGE `customer_phone` `customer_phone` VARCHAR(100) NULL DEFAULT NULL");
$this->query("UPDATE `application_option` SET `use_my_account` = '1' WHERE `application_option`.`code` = 'm_commerce'");