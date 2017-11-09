<?php

class Backoffice_Advanced_CronController extends Backoffice_Controller_Default {

    public static $system_tasks = array(
        "agregateanalytics" => "",
        "androidtools" => "Update Android SDK Tools, run only when necessary, triggered by regular updates.",
        "apkgenerator" => "",
        "logrotate" => "Rotate logs every day.",
        "pushinstant" => "",
        "sources" => "",
        "cachebuilder" => "",
        "letsencrypt" => "",
        "statistics" => "Send anonymous statistics to improve the platform and help understanding usage of the apps builder.",
        "quotawatcher" => "Disk quota usage watcher.",
        "alertswatcher" => "System monitor to send alerts.",
    );

    public function loadAction() {

        $html = array(
            "title" => __("Advanced")." > ".__("Cron"),
            "icon" => "fa-clock-o",
        );

        $this->_sendHtml($html);

    }

    public function findallAction() {

        $cron = new Cron_Model_Cron();
        $cron_tasks = $cron->findAll(array(), array("name ASC"));

        $apk_model = new Application_Model_ApkQueue();
        $apk_queue = $apk_model->findAll(array(), array("apk_queue_id DESC"), array("limit" => 50));

        $source_model = new Application_Model_SourceQueue();
        $source_queue = $source_model->findAll(array(), array("source_queue_id DESC"), array("limit" => 50));

        $data = array(
            "system_tasks" => array(),
            "tasks" => array(),
            "apk_queue" => array(),
            "source_queue" => array(),
        );

        foreach($cron_tasks as $cron_task) {
            $name = $cron_task->getCommand();
            $_data = $cron_task->getData();
            $_data["show_info"] = false;
            $_data["last_success"] = ("0000-00-00 00:00:00" != $_data["last_success"]) ? datetime_to_format($_data["last_success"]) : __("never");
            $_data["last_trigger"] = ("0000-00-00 00:00:00" != $_data["last_trigger"]) ? datetime_to_format($_data["last_trigger"]) : __("never");
            $_data["last_fail"] = ("0000-00-00 00:00:00" != $_data["last_fail"]) ? datetime_to_format($_data["last_fail"]) : __("never");

            if(in_array($name, array_keys(self::$system_tasks))) {
                $_data["more_info"] = __(self::$system_tasks[$name]);

                $data["system_tasks"][] = $_data;
            } else {
                $data["tasks"][] = $_data;
            }
        }

        $base = Core_Model_Directory::getBasePathTo("");

        foreach($apk_queue as $apk) {
            $_data = $apk->getData();

            $_data["created_at"] = ("0000-00-00 00:00:00" != $_data["created_at"]) ? datetime_to_format($_data["created_at"]) : __("never");
            $_data["updated_at"] = ("0000-00-00 00:00:00" != $_data["updated_at"]) ? datetime_to_format($_data["updated_at"]) : __("never");
            $_data["status_code"] = $_data["status"];
            $_data["status"] = __($_data["status"]);
            $_data["stuck"] = (($_data["status"] == "building") && (($_data["build_start_time"] + Siberian_Date::HOUR_SECONDS) < time()));
            $_data["path"] = (!empty($_data["path"])) ? "/".str_replace($base, "", $_data["path"]) : false;
            $_data["show_info"] = false;

            $data["apk_queue"][] = $_data;
        }

        foreach($source_queue as $source) {
            $_data = $source->getData();

            $_data["created_at"] = ("0000-00-00 00:00:00" != $_data["created_at"]) ? datetime_to_format($_data["created_at"]) : __("never");
            $_data["updated_at"] = ("0000-00-00 00:00:00" != $_data["updated_at"]) ? datetime_to_format($_data["updated_at"]) : __("never");
            $_data["status_code"] = $_data["status"];
            $_data["status"] = __($_data["status"]);
            $_data["stuck"] = (($_data["status"] == "building") && (($_data["build_start_time"] + Siberian_Date::HOUR_SECONDS) < time()));
            $_data["path"] = (!empty($_data["path"])) ? "/".str_replace($base, "", $_data["path"]) : false;
            $_data["show_info"] = false;

            $data["source_queue"][] = $_data;
        }

        $this->_sendHtml($data);

    }

}
