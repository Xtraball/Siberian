<?php

$queries = [
    "ALTER TABLE media_gallery_image CHANGE type_id  type_id ENUM('picasa', 'custom', 'instagram', 'flickr') CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;",
];

foreach ($queries as $query) {
    try {
        $this->query($query);
    } catch (\Exception $e) {
        // Silently fails!
    }
}
