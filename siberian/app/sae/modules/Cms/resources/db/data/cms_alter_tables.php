<?php
$queries = [
    "ALTER TABLE cms_application_page_block_button CHANGE type_id  type_id ENUM('link','phone','email') CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'phone';",
    "ALTER TABLE `cms_application_page_block_button` CHANGE `hide_navbar` `hide_navbar` TINYINT(1) NULL DEFAULT '0';",
    "ALTER TABLE `cms_application_page_block_button` CHANGE `use_external_app` `use_external_app` TINYINT(1) NULL DEFAULT '0';",
    "ALTER TABLE `cms_application_page_block_button` CHANGE `external_browser` `external_browser` TINYINT(1) NULL DEFAULT '0';",
    "ALTER TABLE `session` ADD `source` TEXT NULL AFTER `data`;",
    "ALTER TABLE `cms_application_page_block_source` CHANGE `height` `height` SMALLINT(1) UNSIGNED NOT NULL;",
    "UPDATE `cms_application_page_block_text` SET `image_position` = `position`;",
    "ALTER TABLE `cms_application_page_block_text` DROP COLUMN `position`; "
];

foreach ($queries as $query) {
    try {
        $this->query($query);
    } catch (\Exception $e) {
        // Silent!
    }
}
