<?php
$init = function($bootstrap) {
    # Exporter
    Siberian_Exporter::register("loyalty", "LoyaltyCard_Model_LoyaltyCard");
};
