<?php
# All layouts assets
Siberian_Assets::copyAssets("/app/sae/modules/Layouts/resources/var/apps/");

# Updating apps without layoutOptions
# disabled in 4.8.9
//$this->query("UPDATE `application` SET `layout_options` = (SELECT `options` FROM `application_layout_homepage` WHERE `application_layout_homepage`.`layout_id` =  `application`.`layout_id`) WHERE `layout_options` = '';");