<?php
class Rss_Bootstrap {

    public static function init($bootstrap) {
        # Exporter
        Siberian_Exporter::register("rss_feed", "Rss_Model_Feed");

    }
}
