<?php

/**
 * Class Application_Model_Queue
 */
class Application_Model_Queue extends Core_Model_Default {

    public static $TYPES = array(
        "ios",
        "iosnoads",
        "android",
        "apk",
    );

    /**
     * Cancelling queue
     *
     * @param $application_id
     * @param $type
     * @param $device
     */
    public static function cancel($application_id, $type, $device) {
        switch($type) {
            case "apk":
                    $queue = new Application_Model_ApkQueue();
                    $queues = $queue->findAll(array(
                        "app_id = ?" => $application_id,
                        "status NOT IN (?)" => array("success", "building"),
                    ));
                    foreach($queues as $queue) {
                        $queue->delete();
                    }
                break;
            case "zip":
                    $queue = new Application_Model_SourceQueue();
                    $queues = $queue->findAll(array(
                        "app_id = ?" => $application_id,
                        "type = ?" => $device,
                        "status NOT IN (?)" => array("success", "building"),
                    ));
                    foreach($queues as $queue) {
                        $queue->delete();
                    }
                break;
        }
    }

    /**
     * Global queue (may add IPA & Other Android/iOS Versions)
     *
     * @param $application_id
     * @return array
     */
    public static function getPosition($application_id) {
        $db = Zend_Db_Table::getDefaultAdapter();

        $select_source = $db->select()
            ->from("source_queue", array(
                "id" => new Zend_Db_Expr("source_queue_id"),
                "type",
                "name",
                "path",
                "app_id",
                "created_at",
                "updated_at",
            ))
            ->where("status IN (?)", array("queued", "building"))
        ;

        $select_apk = $db->select()
            ->from("apk_queue", array(
                "id" => new Zend_Db_Expr("apk_queue_id"),
                "type" => new Zend_Db_Expr("'apk'"),
                "name",
                "path",
                "app_id",
                "created_at",
                "updated_at",
            ))
            ->where("status IN (?)", array("queued", "building"))
        ;

        $select = $db
            ->select()
            ->union(array(
                $select_source,
                $select_apk,
            ))
            ->order("created_at ASC")
        ;

        $results = $db->fetchAll($select);
        $total = sizeof($results);

        $positions = array();
        foreach(self::$TYPES as $type) {
            $positions[$type] = 0;
            $found = false;

            foreach($results as $result) {
                $positions[$type]++;
                if(($result["app_id"] == $application_id) && ($result["type"]  == $type)) {
                    $found = true;
                    break;
                }
            }

            if(!$found) {
                $positions[$type] = 0;
            }
        }

        return array(
            "positions" => $positions,
            "total" => $total,
        );
    }

    /**
     * @return float|int
     * @throws Zend_Db_Select_Exception
     */
    public static function getBuildTime() {
        $db = Zend_Db_Table::getDefaultAdapter();

        $select_source = $db->select()
            ->from("source_queue", array(
                "build_time",
            ))
            ->where("status IN (?)", array("success"))
        ;

        $select_apk = $db->select()
            ->from("apk_queue", array(
                "build_time",
            ))
            ->where("status IN (?)", array("success"))
        ;

        $source = $db->fetchAll($select_source);
        $apk = $db->fetchAll($select_apk);

        $total = sizeof($source) + sizeof($apk);
        $build_time = 0;
        $build_source = 0;
        $build_apk = 0;

        $build_times = array(
            "source" => 0,
            "apk" => 0,
            "global" => 0,
        );

        foreach($source as $result) {
            $build_source += $result["build_time"];
            $build_time += $result["build_time"];
        }
        foreach($apk as $result) {
            $build_apk += $result["build_time"];
            $build_time += $result["build_time"];
        }

        if((sizeof($source) > 0) && ($build_source > 0)) {
            $build_times["source"] = round($build_source / sizeof($source));
        }

        if((sizeof($build_apk) > 0) && ($build_apk > 0)) {
            $build_times["apk"] = round($build_apk / sizeof($apk));
        }

        if(($total > 0) && ($build_time > 0)) {
            $build_times["global"] = round($build_time / $total);
        }

        return $build_times;
    }
}
