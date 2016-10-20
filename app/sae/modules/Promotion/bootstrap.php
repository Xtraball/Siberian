<?php
class Promotion_Bootstrap {

    public static function init($bootstrap) {
        # Exporter
        Siberian_Exporter::register("discount", "Promotion_Model_Promotion");
        Siberian_Exporter::register("qr_discount", "Promotion_Model_Promotion");

    }
}
