<?php

$this->query("ALTER TABLE media_gallery_image CHANGE type_id  type_id ENUM('picasa', 'custom', 'instagram', 'facebook') CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;");