<?php

$queries = [
    "ALTER TABLE `session` ADD FULLTEXT `IDX_SEARCH_ID` (`session_id`);",
    "ALTER TABLE `session` ADD FULLTEXT `IDX_SEARCH_DATA` (`data`);",
];

foreach ($queries as $query) {
    try {
        $this->query($query);
    } catch (\Exception $e) {
        // Nope!
    }
}
