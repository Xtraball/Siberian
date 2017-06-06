<?php
	 

$this->query("ALTER TABLE `mcommerce` ADD `mask_qty_opt` TINYINT( 1 ) NOT NULL DEFAULT  '0' AFTER `age_minimum` ;");



