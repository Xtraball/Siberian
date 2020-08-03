<?php

$queries = [
    'ALTER TABLE `siberiancms_pe`.`iap_product` ADD UNIQUE `UNIQUE_ALIAS_APP` (`alias`, `app_id`);',
];

foreach ($queries as $query) {
    try {
        $this->query($query);
    } catch (\Exception $e) {
        // Done, silent!
    }
}
