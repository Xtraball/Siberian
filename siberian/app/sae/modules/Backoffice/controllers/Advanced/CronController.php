<?php

/**
 * Class Backoffice_Advanced_CronController
 */
class Backoffice_Advanced_CronController extends Backoffice_Controller_Default
{

    /**
     * @var array
     */
    public static $system_tasks = [
        'agregateanalytics' => '',
        'androidtools' => 'Update Android SDK Tools, run only when necessary, triggered by regular updates.',
        'apkgenerator' => '',
        'logrotate' => 'Rotate logs every day.',
        'pushinstant' => '',
        'sources' => '',
        'cachebuilder' => '',
        'letsencrypt' => '',
        'statistics' =>
            'Send anonymous statistics to improve the platform and help understanding usage of the apps builder.',
        'quotawatcher' => 'Disk quota usage watcher.',
        'alertswatcher' => 'System monitor to send alerts.',
        'checkpayments' => 'Callback for recurrences.',
        'diskusage' => 'Disk usage watcher.',
        'Application_Model_Application::getSizeOnDisk' => 'Get Application disk usage',
    ];

    public function loadAction()
    {
        $payload = [
            'title' => sprintf('%s > %s > %s',
                __('Settings'),
                __('Advanced'),
                __('Cron')),
            'icon' => 'fa-clock-o',
        ];

        $this->_sendJson($payload);
    }

    /**
     *
     */
    public function findallAction()
    {
        $cron = new Cron_Model_Cron();
        $cron_tasks = $cron->findAll([], ['name ASC']);

        $apk_model = new Application_Model_ApkQueue();
        $apk_queue = $apk_model->findAll([], ['apk_queue_id DESC'], ['limit' => 50]);

        $source_model = new Application_Model_SourceQueue();
        $source_queue = $source_model->findAll([], ['source_queue_id DESC'], ['limit' => 50]);

        $data = [
            'system_tasks' => [],
            'tasks' => [],
            'apk_queue' => [],
            'source_queue' => [],
        ];

        foreach ($cron_tasks as $cron_task) {
            $name = $cron_task->getCommand();
            $_data = $cron_task->getData();


            // Humanize cron schedule
            $cronString = sprintf("%s %s %s %s %s",
                $_data["minute"],
                $_data["hour"],
                $_data["month_day"],
                $_data["month"],
                $_data["week_day"]);
            $cronString = str_replace("-1", "*", $cronString);
            $schedule = \Siberian\Cron\Human::fromCronString($cronString);
            $_data["cron_expr"] = $cronString;
            $_data["human"] = $schedule->asNaturalLanguage();

            $_data["show_info"] = false;
            $_data["last_success"] = ("0000-00-00 00:00:00" != $_data["last_success"]) ? datetime_to_format($_data["last_success"]) : __("never");
            $_data["last_trigger"] = ("0000-00-00 00:00:00" != $_data["last_trigger"]) ? datetime_to_format($_data["last_trigger"]) : __("never");
            $_data["last_fail"] = ("0000-00-00 00:00:00" != $_data["last_fail"]) ? datetime_to_format($_data["last_fail"]) : __("never");

            if (in_array($name, array_keys(self::$system_tasks))) {
                $_data["more_info"] = __(self::$system_tasks[$name]);

                $data["system_tasks"][] = $_data;
            } else {
                $data["tasks"][] = $_data;
            }
        }

        $base = Core_Model_Directory::getBasePathTo("");

        foreach ($apk_queue as $apk) {
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

        foreach ($source_queue as $source) {
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

        $this->_sendJson($data);
    }

    public function restartApkAction()
    {
        try {
            $request = $this->getRequest();
            $queueId = $request->getParam('queueId', null);

            $apkQueue = (new Application_Model_ApkQueue())
                ->find($queueId);

            if (!$apkQueue->getId()) {
                throw new \Siberian\Exception(__('This build do not exists.'));
            }

            // Duplicated the build, restart!
            $apkQueue
                ->unsData('id')
                ->unsData('apk_queue_id')
                ->setStatus('queued')
                ->unsData('created_at')
                ->unsData('updated_at')
                ->setBuildTime(0)
                ->setBuildStartTime(0)
                ->setLog('')
                ->save();

            $payload = [
                'success' => true,
                'message' => __('Success'),
            ];
        } catch (\Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }

}
