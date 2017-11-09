<?php
$this->query('ALTER TABLE cms_application_page_block_button CHANGE type_id  type_id ENUM(\'link\',\'phone\',\'email\') CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT \'phone\';');
