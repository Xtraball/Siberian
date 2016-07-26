<?php
/**
 * Class Siberian_Cron
 *
 * @author Xtraball SAS <dev@xtraball.com>
 *
 * @version 4.2.0
 */

class Siberian_Cron {

	/**
	 * @var Cron_Model_Cron
	 */
	protected $cron;

	/**
	 * @var Siberian_Log
	 */
	protected $logger;

	/**
	 * @var String
	 */
	protected $lock_base = "/var/tmp/";

	/**
	 * @var String
	 */
	protected $root_path;

	/**
	 * @var integer
	 */
	protected $start;

	public function __construct(){
		$this->cron = new Cron_Model_Cron();
		$this->logger = Zend_Registry::get("logger");
		$this->lock_base = Core_Model_Directory::getBasePathTo($this->lock_base);
		$this->start = microtime(true);
		$this->root_path = Core_Model_Directory::getBasePathTo();

		# Set the same timezone as in the Application settings.
		$timezone = System_Model_Config::getValueFor("system_timezone");
		if($timezone) {
			date_default_timezone_set($timezone);
		}
	}

	/**
	 * @param $text
	 */
	public function log($text) {
		echo sprintf("[CRON: %s]: %s\n", date("Y-m-d H:i:s"), $text);
	}

	public function triggerAll(){
		try {
			$minute     = (int)date("i");
			$hour       = (int)date("G");
			$month_day  = (int)date("j");
			$month      = (int)date('m');
			$week_day   = (int)date('w');

			$all = $this->cron->getActiveActions($minute, $hour, $month_day, $month, $week_day);

			$actions = array();
			foreach ($all as $task){
				$actions[] = array(
					"id" => $task->getCommand(),
					"command" => $task->getCommand(),
				);
				$this->execute($task);
			}
			
			return $actions;
		} catch (Exception $e) {
			if(APPLICATION_ENV === "development") {
				Zend_Debug::dump($e);
			}
			$this->log($e->getMessage());
			return false;
		}
	}

	/**
	 * @param Cron_Model_Cron $task
	 */
	protected function execute($task){
		/** Avoid duplicates when a task takes too long */
		$task->trigger();
		$success = true;

		if(!$this->isLocked($task->getId())) {
			$this->log("Executing task: ".$task->getName());
			/** Non blocking tasks */
			try {
				$command = $task->getCommand();
				if (method_exists($this, $command)) {
					$this->$command($task);
				}
			} catch (Exception $e) {
				$this->log($e->getMessage());

				# Unlock task in case of Exception
				$this->unlock($task->getId());

				$success = false;
			}
		} else {
			$this->log("Locked task: {$task->getName()}, skipping...");
		}

		if($success) {
			$task->success();
		} else {
			$task->fail();
		}
	}

	/**
	 * @param $task_id
	 * @return bool
	 */
	private function isLocked($task_id) {
		return (file_exists("{$this->lock_base}/{$task_id}.lock"));
	}

	/**
	 * Use if you want to
	 *
	 * @param $task_id
	 */
	private function lock($task_id) {
		file_put_contents("{$this->lock_base}/{$task_id}.lock", 1);
	}

	/**
	 * @param $task_id
	 */
	private function unlock($task_id) {
		$file = "{$this->lock_base}/{$task_id}.lock";
		if(file_exists($file)) {
			unlink($file);
		}
	}

	###############################################################################
	#                                                                             #
	#                         Start of the tasks block.                           #
	#                                                                             #
	###############################################################################

	/**
	 * Push instant queued messages, Apns, Gcm (Every minute)
	 *
	 * @param Cron_Model_Cron $task
	 */
	public function pushinstant($task) {
		# Init
		$now = Zend_Date::now()->toString('y-MM-dd HH:mm:ss');

        # Check for Individual Push module
        if(Push_Model_Message::hasIndividualPush()) {
            $base = Core_Model_Directory::getBasePathTo("/app/local/modules/IndividualPush/");

            # Models
            require_once "{$base}/Model/Customer/Message.php";
            require_once "{$base}/Model/Db/Table/Customer/Message.php";
        }

		# Fetch instant message in queue.
		$message = new Push_Model_Message();
		$messages = $message->findAll(
			array(
				'status IN (?)' => array('queued'),
				'send_at IS NULL OR send_at <= ?' => $now,
				'send_until IS NULL OR send_until >= ?' => $now,
				'type_id = ?' => Push_Model_Message::TYPE_PUSH
			),
			'created_at DESC'
		);

		if(count($messages) > 0) {
			# Set all fetched messages to sending
			foreach ($messages as $message) {
				$message->updateStatus('sending');
			}

			foreach ($messages as $message) {
				echo sprintf("[CRON] Message Id: %s, Title: %s \n", $message->getId(), $message->getTitle());
				# Send push
				$message->push();
			}
		}
	}

	/**
	 * Cleaning-up/rotate old/unused logs (Every day at 00:05 AM)
	 *
	 * @param Cron_Model_Cron $task
	 */
	public function logrotate($task) {
		$log_files = new DirectoryIterator("{$this->root_path}/var/log/");
		foreach($log_files as $file) {
			$filename = $file->getFilename();
			$pathname = $file->getPathname();

			# Clean up info_* logs
			if(strpos($filename, "info_") !== false) {
				unlink($pathname);
			}

			# Clean up migration_* logs
			if(strpos($filename, "migration_") !== false) {
				unlink($pathname);
			}

			# Clean up error_* logs
			if(strpos($filename, "error_") !== false) {
				unlink($pathname);
			}

			# Clean up output_* logs
			if(strpos($filename, "output.log") !== false) {
				unlink($pathname);
			}

			# Clean up output_* logs
			if(strpos($filename, "cron-output.log") !== false) {
				unlink($pathname);
			}

			# Clean up output_* logs
			if(strpos($filename, "cron.log") !== false) {
				unlink($pathname);
			}

		}
	}


	/**
	 * APK Generator queue
	 *
	 * @param Cron_Model_Cron $task
	 */
	public function apkgenerator($task) {
		# We do really need to lock this thing !
		$this->lock($task->getId());

		# Generate the APK
		/** @todo in 4.2.x */

		# Releasing
		$this->unlock($task->getId());
	}


	/**
	 * Analytics aggregation
	 *
	 * @param Cron_Model_Cron $task
	 */
	public function agregateanalytics($task) {
		Analytics_Model_Aggregate::getInstance()->run(time());
	}

	/**
	 * Install the Android tools (once)
	 *
	 * @param Cron_Model_Cron $task
	 */
	public function androidtools($task) {
		# We do really need to lock this thing !
		$this->lock($task->getId());

		try {
			$script = "{$this->root_path}/var/apps/ionic/tools/sdk-updater.php";

			require_once $script;
		} catch(Exception $e){
			$this->log($e->getMessage());
		}

		# Disable when done.
		$task->disable();
		# Releasing
		$this->unlock($task->getId());
	}



	###############################################################################
	#                                                                             #
	#                          End of the tasks block.                            #
	#                                                                             #
	###############################################################################


	public function __destruct()
	{
		/** Detect too long processes to alert admin */
		$exec_time = microtime(true) - $this->start;
		$this->log("Execution time {$exec_time}");
	}

}
