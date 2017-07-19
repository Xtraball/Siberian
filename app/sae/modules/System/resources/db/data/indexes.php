<?php
/**
 * Missing indexes, to optimize sql queries
 */

# Siberian 5.0

try {
    $this->query("ALTER TABLE `cron` ADD KEY `cron_search` (`is_active`,`minute`,`hour`,`month_day`,`month`,`week_day`);");
} catch(Exception $e) { /** Silent*/ }

try {
    $this->query("ALTER TABLE `apk_queue` ADD KEY `apk_queue_search` (`status`,`build_start_time`) USING BTREE;");
} catch(Exception $e) { /** Silent*/ }

try {
    $this->query("ALTER TABLE `source_queue` ADD KEY `apk_queue_search` (`status`,`build_start_time`) USING BTREE;");
} catch(Exception $e) { /** Silent*/ }

try {
    $this->query("ALTER TABLE `tour_step` ADD KEY `tour_step_search_tour` (`title`,`language_code`,`url`);");
} catch(Exception $e) { /** Silent*/ }

try {
    $this->query("ALTER TABLE `sales_invoice` ADD INDEX `sales_invoice_search_createdat` (`created_at`);");
} catch(Exception $e) { /** Silent*/ }

try {
    $this->query("ALTER TABLE `admin` ADD INDEX `admin_search_createdat` (`created_at`);");
} catch(Exception $e) { /** Silent*/ }

try {
    $this->query("ALTER TABLE `session` ADD INDEX `gc_sessions` (`modified`, `lifetime`);");
} catch(Exception $e) { /** Silent*/ }

try {
    $this->query("ALTER TABLE `system_config` ADD INDEX `config_search_code`  (`code`);");
} catch(Exception $e) { /** Silent*/ }

try {
    $this->query("ALTER TABLE `push_messages` ADD INDEX `push_cron_search` (`status`, `send_at`, `send_until`, `type_id`, `created_at`);");
} catch(Exception $e) { /** Silent*/ }

try {
    $this->query("ALTER TABLE `cms_application_page` ADD INDEX `cms_application_page_optim_index` (`page_id`, `value_id`);");
} catch(Exception $e) { /** Silent*/ }
