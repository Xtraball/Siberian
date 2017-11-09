<?php
try {
    $this->query("ALTER TABLE `media_gallery_video_youtube` CHANGE `param` `param` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;");
} catch(Exception $e) {

}