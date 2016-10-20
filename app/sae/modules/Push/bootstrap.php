<?php
class Push_Bootstrap {

    public static function init($bootstrap) {
        # Exporter
        Siberian_Exporter::register("push_notification", "Push_Model_Message");

    }
}
