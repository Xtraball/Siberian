<?php
class Wordpress_Bootstrap {

    public static function init($bootstrap) {
        # Exporter
        Siberian_Exporter::register("wordpress", "Wordpress_Model_Wordpress");

    }
}
