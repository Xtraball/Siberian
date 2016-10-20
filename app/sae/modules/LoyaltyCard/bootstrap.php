<?php
class LoyaltyCard_Bootstrap {

    public static function init($bootstrap) {
        # Exporter
        Siberian_Exporter::register("loyalty", "LoyaltyCard_Model_LoyaltyCard");

    }
}
