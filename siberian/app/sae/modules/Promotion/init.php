<?php
$init = function($bootstrap) {
    # Exporter
    Siberian_Exporter::register("discount", "Promotion_Model_Promotion");
    Siberian_Exporter::register("qr_discount", "Promotion_Model_Promotion");

};
