<?php

use Siberian\Exporter;

class_alias("\Push\Model\StandalonePush", "Push_Model_StandalonePush");

$init = function($bootstrap) {
    # Exporter
    Exporter::register("push_notification", "Push_Model_Message");
};
