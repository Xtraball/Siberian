<?php

$this->query("ALTER TABLE `module` CHANGE `version` `version` VARCHAR(30) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;");

# 4.10.0
System_Model_Config::setValueFor("campaign_is_active", "1");