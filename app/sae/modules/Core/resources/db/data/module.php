<?php

$this->query("ALTER TABLE `module` CHANGE `version` `version` VARCHAR(30) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;");
