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
            $_data["last_success"] = ("0000-00-00 00:00:00" != $_data["last_success"]) ? $cron_task->getFormattedLastSuccess() : __("never");
            $_data["last_trigger"] = ("0000-00-00 00:00:00" != $_data["last_trigger"]) ? $cron_task->getFormattedLastTrigger() : __("never");
            $_data["last_fail"] = ("0000-00-00 00:00:00" != $_data["last_fail"]) ? $cron_task->getFormattedLastFail() : __("never");

            if(in_array($name, array_keys(self::$system_tasks))) {
                $_data["more_info"] = __(self::$system_tasks[$name]);

                $data["system_tasks"][] = $_data;
            } else {
                $data["tasks"][] = $_data;
            }
        }

        foreach($apk_queue as $apk) {
            $_data = $apk->getData();

            $_data["created_at"] = ("0000-00-00 00:00:00" != $_data["created_at"]) ? $cron_task->getFormattedCreatedAt() : __("never");
            $_data["updated_at"] = ("0000-00-00 00:00:00" != $_data["updated_at"]) ? $cron_task->getFormattedUpdatedAt() : __("never");
            $_data["status"] = __($_data["status"]);
            $_data["show_info"] = false;

            $data["apk_queue"][] = $_data;
        }

        foreach($source_queue as $source) {
            $_data = $source->getData();

            $_data["created_at"] = ("0000-00-00 00:00:00" != $_data["created_at"]) ? $cron_task->getFormattedCreatedAt() : __("never");
            $_data["updated_at"] = ("0000-00-00 00:00:00" != $_data["updated_at"]) ? $cron_task->getFormattedUpdatedAt() : __("never");
            $_data["status"] = __($_data["status"]);
            $_data["show_info"] = false;

            $data["source_queue"][] = $_data;
        }

        $this->_sendHtml($data);

    }

}
