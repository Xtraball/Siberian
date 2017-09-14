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
	 * @var array
	 */
	protected $locked_tasks = array();

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

    /**
     * @return array|bool|void
     */
	public function triggerAll(){
		if(!Cron_Model_Cron::is_active()) {
			$this->log("Cron is disabled in your system, see: Backoffice > Settings > Advanced > Configuration > Cron");
			return;
		}

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
     * @param $command
     */
	public function runTaskByCommand($command) {
        try {
            $tasks = $this->cron->getTaskByCommand($command);
            foreach($tasks as $task) {
                if(!$task->getId()) {
                    throw new Siberian_Exception('The task doesn\'t exists.');
                }
                $this->execute($task);
            }
        } catch (Exception $e) {
            $this->log('[runTaskByCommand::ERROR]: ' . $e->getMessage());
        }
    }

	/**
	 * @param Cron_Model_Cron $task
	 */
	public function execute($task){
		/** Avoid duplicates when a task takes too long */
		$task->trigger();
		$success = true;

		if(!$this->isLocked($task->getId())) {
			$this->log("Executing task: ".$task->getName());

			/** Non blocking tasks */
			try {

				$command = $task->getCommand();
				if(strpos($command, "::") !== false) {
					# Split Class::method
					$parts = explode("::", $command);
					$class = $parts[0];
					$method = $parts[1];

					# Tests.
					if(class_exists($class) && method_exists($class, $method)) {
						call_user_func($command, $this, $task);
					}

				} else {
					# Local method
					if (method_exists($this, $command)) {
						$this->$command($task);
					}
				}

			} catch (Exception $e) {
				$this->log($e->getMessage());

				# Unlock task in case of Exception
				$this->unlock($task->getId());

				$task->saveLastError($e->getMessage());

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
	public function isLocked($task_id) {
		return (file_exists("{$this->lock_base}/{$task_id}.lock"));
	}

	/**
	 * Use if you want to
	 *
	 * @param $task_id
	 */
	public function lock($task_id) {
		$this->locked_tasks[] = $task_id;
		file_put_contents("{$this->lock_base}/{$task_id}.lock", 1);
	}

	/**
	 * @param $task_id
	 */
	public function unlock($task_id) {
		$file = "{$this->lock_base}/{$task_id}.lock";
		if(file_exists($file)) {
			unlink($file);
			unset($this->locked_tasks[$task_id]);
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
			if(is_readable("{$base}/Model/Customer/Message.php") && is_readable("{$base}/Model/Db/Table/Customer/Message.php")) {
				require_once "{$base}/Model/Customer/Message.php";
				require_once "{$base}/Model/Db/Table/Customer/Message.php";
			}
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

		# This folder is not always present +4.9.1.
		if(is_readable("{$this->root_path}/var/log/modules/")) {
            $module_log_files = new DirectoryIterator("{$this->root_path}/var/log/modules/");
            foreach($module_log_files as $file) {
                $pathname = $file->getPathname();

                # Clean up all logs
                unlink($pathname);
            }
        }

	}

	/** NOTE: APK & Sources queues shares the same lock, as one may break the other */

	/**
	 * APK Generator queue
	 *
	 * @param Cron_Model_Cron $task
	 */
	public function apkgenerator($task) {
		# We do really need to lock this thing !
		if(!$this->isLocked("generator")) {
			$this->lock("generator");

			# Generate the APK
			$queue = Application_Model_ApkQueue::getQueue();
			foreach($queue as $apk) {
                # Keep APK Queue id
                $apk_id = $apk->getId();

				try {
					$this->log(sprintf("Generating App: ID[%s], Name[%s], Target[APK]", $apk->getAppId(), $apk->getName()));
					$apk->changeStatus("building");

					# Trying to clean-up old related processes
					exec("pkill -9 -U $(id -u) aapt; pkill -9 -U $(id -u) java");

					$apk->generate();

                    # +After generation**
                    exec("pkill -9 -U $(id -u) aapt; pkill -9 -U $(id -u) java");

				} catch(Exception $e) {
					$this->log($e->getMessage());
                    # Trying to fetch APK
                    $refetch_apk = new Application_Model_ApkQueue();
                    $refetch_apk = $refetch_apk->find($apk_id);
                    if(!$refetch_apk->getId()) {
                        $task->saveLastError("APK Generation was cancelled during the build phase, unable to continue.");
                    } else {
                        $refetch_apk->changeStatus("failed");
                        $task->saveLastError($e->getMessage());
                    }
				}

			}

			# Releasing
			$this->unlock("generator");
		} else {
			$this->log("Locked task: {$task->getName()} / generator, skipping...");
		}
	}

	/**
	 * Sources Generator queue
	 *
	 * @param Cron_Model_Cron $task
	 */
	public function sources($task) {
		# We do really need to lock this thing !
		if(!$this->isLocked("generator")) {
			$this->lock("generator");

			# Generate the Source ZIP
			$queue = Application_Model_SourceQueue::getQueue();
			foreach($queue as $source) {
                # Keep Source Queue id
                $source_id = $source->getId();

				try {
					$this->log(sprintf("Generating App sources: ID[%s], Name[%s], Target[%s]", $source->getAppId(), $source->getName(), $source->getType()));
					$source->changeStatus("building");
					$source->generate();
				} catch(Exception $e) {
					$this->log($e->getMessage());

                    # Trying to fetch Source
                    $refetch_source = new Application_Model_SourceQueue();
                    $refetch_source = $refetch_source->find($source_id);
                    if(!$refetch_source->getId()) {
                        $task->saveLastError("Source Generation was cancelled during the build phase, unable to continue.");
                    } else {
                        $refetch_source->changeStatus("failed");
                        $task->saveLastError($e->getMessage());
                    }
				}

			}

			# Releasing
			$this->unlock("generator");
		} else {
			$this->log("Locked task: {$task->getName()} / generator, skipping...");
		}
	}

    /**
     * Let's Encrypt certificates renewal
     *
     * @param $task
     */
    public function letsencrypt($task) {
        $letsencrypt_disabled = System_Model_Config::getValueFor("letsencrypt_disabled");
        if($letsencrypt_disabled > time()) {
            $this->log(__("[Let's Encrypt] cron renewal is disabled until %s due to rate limit hit, skipping.", date("d/m/Y H:i:s", $letsencrypt_disabled)));
            return;

        } else {

            # Enabling again after the 7 days period
            System_Model_Config::setValueFor("letsencrypt_disabled", 0);
        }

        if(!$this->isLocked($task->getId())) {
            $this->lock($task->getId());

            $email = System_Model_Config::getValueFor("support_email");
            $root = Core_Model_Directory::getBasePathTo("/");
            $base = Core_Model_Directory::getBasePathTo("/var/apps/certificates/");

            // Check panel type
            $panel_type = System_Model_Config::getValueFor("cpanel_type");

            // Ensure folders have good rights
            exec("chmod -R 775 {$base}");
            if(is_readable("{$root}/.well-known")) {
                exec("chmod -R 775 {$root}/.well-known");
            }

            $lets_encrypt = new Siberian_LetsEncrypt($base, $root, false);
            $le_is_init = false;

            // Use staging environment
            $letsencrypt_env = System_Model_Config::getValueFor("letsencrypt_env");
            if($letsencrypt_env == "staging") {
                $lets_encrypt->setIsStaging();
            }

            if(!empty($email)) {
                $lets_encrypt->contact = array("mailto:{$email}");
            }

            try {
                $ssl_certificates = new System_Model_SslCertificates();

                $to_renew = new Zend_Db_Expr("renew_date < updated_at");
                $certs = $ssl_certificates->findAll(array(
                    "source = ?" => System_Model_SslCertificates::SOURCE_LETSENCRYPT,
                    "status = ?" => "enabled",
                    $to_renew
                ));

                foreach($certs as $cert) {

                    try {

                        // Before generating certificate again, compare $hostnames
                        $renew = false;
                        $domains = $cert->getDomains();
                        $retain_domains = array();
                        if(is_readable($cert->getCertificate()) && !empty($domains)) {

                            $cert_content = openssl_x509_parse(file_get_contents($cert->getCertificate()));

                            if(isset($cert_content["extensions"]) && $cert_content["extensions"]["subjectAltName"]) {
                                $certificate_hosts = explode(",", str_replace("DNS:", "", $cert_content["extensions"]["subjectAltName"]));
                                $hostnames = Siberian_Json::decode($cert->getDomains());

                                foreach($hostnames as $hostname) {
                                    $hostname = trim($hostname);

                                    $isNotInArray = !in_array($hostname, $certificate_hosts);
                                    $endWithDot = preg_match("/.*\.$/im", $hostname);
                                    $r = dns_get_record($hostname, DNS_CNAME);
                                    $isCname = (!empty($r) && isset($r[0]) && isset($r[0]["target"]) && ($r[0]["target"] === $cert->getHostname()));
                                    $isSelf = ($hostname === $cert->getHostname());

                                    if($isNotInArray && !$endWithDot && ($isCname || $isSelf)) {
                                        $renew = true;
                                        $this->log(__("[Let's Encrypt] will add %s to SAN.", $hostname));

                                        $retain_domains[] = $hostname;
                                    }

                                    if($endWithDot) {
                                        $this->log(__("[Let's Encrypt] removed domain %s, domain in dot notation is not supported.", $hostname));
                                    }
                                }
                            }

                            // Or compare expiration date (will expire in 5/30 days or less)
                            if(!$renew) {

                                $diff = $cert_content["validTo_time_t"] - time();

                                //$thirty_days = 2592000;
                                $five_days = 432000;

                                # Go with five days for now.
                                if($diff < $five_days) {
                                    # Should renew
                                    $renew = true;
                                    $this->log(__("[Let's Encrypt] will expire in %s days.", floor($diff / 86400)));
                                }
                            }

                        } else {
                            $renew = true;
                        }


                        if($renew) {
                            $result = false;
                            if(!$le_is_init) {
                                $lets_encrypt->initAccount();
                                $le_is_init = true;
                            }

                            # Save back domains
                            if(sizeof($domains) != sizeof($retain_domains)) {
                                $cert
                                    ->setDomains(Siberian_Json::encode($retain_domains))
                                    ->save();
                            }

                            // Clear log between hostnames.
                            $lets_encrypt->clearLog();
                            $result = $lets_encrypt->signDomains(Siberian_Json::decode($cert->getDomains()));
                        } else {
                            $result = true;
                        }

                        if($result) {
                            // Change updated_at date, time()+10 to ensure renew is newer than updated_at
                            $cert
                                ->setErrorCount(0)
                                ->setRenewDate(time_to_date(time()+10, "YYYY-MM-dd HH:mm:ss"))
                                ->save();

                            // Sync cPanel - Plesk - VestaCP (beta) - DirectAdmin (beta)
                            try {
                                switch($panel_type) {
                                    case "plesk":
                                            $siberian_plesk = new Siberian_Plesk();
                                            $siberian_plesk->removeCertificate($cert);
                                            $siberian_plesk->updateCertificate($cert);
                                            $siberian_plesk->selectCertificate($cert);
                                        break;
                                    case "cpanel":
                                            $cpanel = new Siberian_Cpanel();
                                            $cpanel->updateCertificate($cert);
                                        break;
                                    case "vestacp":
                                            $vestacp = new Siberian_VestaCP();
                                            $vestacp->updateCertificate($cert);
                                        break;
                                    case "directadmin":
                                            $directadmin = new Siberian_DirectAdmin();
                                            $directadmin->updateCertificate($cert);
                                        break;
                                    case "self":
                                            $this->log("Self-managed sync is not available for now.");
                                        break;
                                }
                            } catch(Exception $e) {
                                $this->log(__("[Let's Encrypt] Something went wrong with the API Sync to %s, retry or check in your panel if your SSL certificate is correctly setup.", $panel_type));
                            }

                            // SocketIO
                            if(class_exists("SocketIO_Model_SocketIO_Module") && method_exists("SocketIO_Model_SocketIO_Module", "killServer")) {
                                SocketIO_Model_SocketIO_Module::killServer();
                            }

                        } else {
                            $cert
                                ->setErrorCount($cert->getErrorCount() + 1)
                                ->setErrorDate(time_to_date(time(), "YYYY-MM-dd HH:mm:ss"))
                                ->setRenewDate(time_to_date(time()+10, "YYYY-MM-dd HH:mm:ss"))
                                ->setErrorLog($lets_encrypt->getLog())
                                ->save();
                        }

                    } catch (Exception $e) {
                        if ((strpos($e->getMessage(), "many currently pending authorizations") !== false) ||
                            (strpos($e->getMessage(), "many certificates already issued") !== false)) {
                            # We hit the rate limit, disable for the next seven days
                            $in_a_week = time() + 604800;
                            System_Model_Config::setValueFor("letsencrypt_disabled", $in_a_week);
                        }

                        $cert
                            ->setErrorCount($cert->getErrorCount() + 1)
                            ->setErrorDate(time_to_date(time(), "YYYY-MM-dd HH:mm:ss"))
                            ->setErrorLog($lets_encrypt->getLog())
                            ->save();
                    }

                    # Disable the certificate after too much errors
                    if($cert->getErrorCount() >= 3) {
                        $cert
                            ->setStatus("disabled")
                            ->save();

                        # Send a message to the Admin
                        $description = "It seems that the renewal of the following SSL Certificate %s is failing, please check in <b>Settings > Advanced > Configuration</b> for the specified certificate.";

                        $notification = new Backoffice_Model_Notification();
                        $notification
                            ->setTitle(__("Alert: The SSL Certificate %s automatic renewal failed.", $cert->getHostname()))
                            ->setDescription(__($description, $cert->getHostname()))
                            ->setSource("cron")
                            ->setType("alert")
                            ->setIsHighPriority(1)
                            ->setObjectType(get_class($cert))
                            ->setObjectId($cert->getId())
                            ->save();

                        Backoffice_Model_Notification::sendEmailForNotification($notification);
                    }
                }

            } catch(Exception $e) {
                $this->log($e->getMessage());
                $task->saveLastError($e->getMessage());
            }

            // Releasing
            $this->unlock($task->getId());
        } else {
            $this->log("Locked task: {$task->getName()}, skipping...");
        }
    }

	/**
	 * Analytics aggregation
	 *
	 * @param Cron_Model_Cron $task
	 */
	public function agregateanalytics($task) {
		//caluling on day after and before to be sure for results
		Analytics_Model_Aggregate::getInstance()->run(time() - 60 * 60 * 24);
		Analytics_Model_Aggregate::getInstance()->run(time());
		Analytics_Model_Aggregate::getInstance()->run(time() + 60 * 60 * 24);
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
		    # Running a clear/tmp before.
            Siberian_Cache::__clearTmp();

            # Testing disk space (4GB required, 2Gb for archive, 2Gb for extracted)
            $result = exec("echo $(($(stat -f --format=\"%a*%S\" .)))");

            if($result > 4000000000) {
                $script = "{$this->root_path}/var/apps/ionic/tools/sdk-updater.php";

                require_once $script;
            } else {

                # Send a message to the Admin
                $description = "Android SDK can't be updated, you need at least 4GB of free disk space.";

                $notification = new Backoffice_Model_Notification();
                $notification
                    ->setTitle(__("Alert: Android SDK can't be updated."))
                    ->setDescription(__($description))
                    ->setSource("cron")
                    ->setType("android-sdk-update")
                    ->setIsHighPriority(1)
                    ->setObjectType("Android_Sdk_Update")
                    ->setObjectId(1)
                    ->save();

                Backoffice_Model_Notification::sendEmailForNotification($notification);
            }


		} catch(Exception $e){
			$this->log($e->getMessage());
			$task->saveLastError($e->getMessage());
		}

		# Disable when done.
		$task->disable();
		# Releasing
		$this->unlock($task->getId());
	}

    /**
     * Check payments recurrencies
     *
     * @param $task
     */
	public function checkpayments($task) {
        # We do really need to lock this thing !
        $this->lock($task->getId());

        try {
            # This handles paypal only!
            Payment_PaypalController::checkRecurrencies();

        } catch(Exception $e){
            $this->log($e->getMessage());
            $task->saveLastError($e->getMessage());
        }

        # Releasing
        $this->unlock($task->getId());
    }

    /**
     * Check disk usage every day
     *
     * @param $task
     */
    public function diskusage($task) {
        # We do really need to lock this thing !
        $this->lock($task->getId());

        try {
            # Timeout to 5 minutes.
            $this->log('[Fetching current disk usage]');
            Siberian_Cache::getDiskUsage(true);

        } catch(Exception $e){
            $this->log($e->getMessage());
            $task->saveLastError($e->getMessage());
        }

        $this->log('[Done fetching current disk usage]');

        # Releasing
        $this->unlock($task->getId());
    }

	/**
	 * Rebuilds the cache
	 *
	 * @param Cron_Model_Cron $task
	 */
	public function cachebuilder($task) {
		# We do really need to lock this thing !
		$this->lock($task->getId());

		try {
		    # Clear cache, etc...
            $default_cache = Zend_Registry::get("cache");
            $default_cache->clean(Zend_Cache::CLEANING_MODE_ALL);

            # Clear cron errors
            Cron_Model_Cron::clearErrors();

			# Disable when success.
			$task->disable();

        } catch(Exception $e){
			$this->log($e->getMessage());
			$task->saveLastError($e->getMessage());
		}

		# Releasing
		$this->unlock($task->getId());
	}

    /**
     * Watch disk quota every hour
     *
     * @param $task
     */
	public function quotawatcher($task) {
        # We do really need to lock this thing !
        $this->lock($task->getId());

        try {
            $global_root = Core_Model_Directory::getBasePathTo("");
            $global_quota = System_Model_Config::getValueFor("global_quota");
            exec("du -cmsL {$global_root}", $output);
            $parts = explode("\t", end($output));
            $global_size = $parts[0];

            # Send an alert.
            if($global_size > $global_quota) {
                // Send a quota alert.
            }

        } catch(Exception $e){
            $this->log($e->getMessage());
            $task->saveLastError($e->getMessage());
        }

        # Releasing
        $this->unlock($task->getId());
    }

    /**
     * Alerts watcher
     *
     * @param $task
     */
    public function alertswatcher($task) {
        # We do really need to lock this thing !
        $this->lock($task->getId());

        try {
            # Search for stuck builds
            $current_time = time();
            $apk_stucks = Application_Model_ApkQueue::getStuck($current_time);
            $source_stucks = Application_Model_SourceQueue::getStuck($current_time);

            # APK Round
            foreach($apk_stucks as $apk_stuck) {
                $description = "You have an APK generation stuck for more than 1 hour, please check in <b>Settings > Advanced > Cron</b> for the stuck build.<br />To unlock further builds you can remove locks from the button below.";

                $notification = new Backoffice_Model_Notification();
                $notification
                    ->setTitle(__("Alert: The APK build for the Application: %s (%s) is stuck for more than one hour.", $apk_stuck->getName(), $apk_stuck->getAppId()))
                    ->setDescription(__($description))
                    ->setSource("cron")
                    ->setType("alert")
                    ->setIsHighPriority(1)
                    ->setObjectType(get_class($apk_stuck))
                    ->setObjectId($apk_stuck->getId())
                    ->save();

                # Also change stuck build to failed.
                $apk_stuck->changeStatus("failed");
            }

            # Sources Round
            foreach($source_stucks as $source_stuck) {
                $description = "You have a Source generation stuck for more than 1 hour, please check in <b>Settings > Advanced > Cron</b> for the stuck build.<br />To unlock further builds you can remove locks from the button below.";

                $notification = new Backoffice_Model_Notification();
                $notification
                    ->setTitle(__("Alert: The Source build for the Application: %s (%s) is stuck for more than one hour.", $source_stuck->getName(), $source_stuck->getAppId()))
                    ->setDescription(__($description))
                    ->setSource("cron")
                    ->setType("alert")
                    ->setIsHighPriority(1)
                    ->setObjectType(get_class($source_stuck))
                    ->setObjectId($source_stuck->getId())
                    ->save();

                # Also change stuck build to failed.
                $source_stuck->changeStatus("failed");
            }

            # More alerts to come.

        } catch(Exception $e){
            $this->log($e->getMessage());
            $task->saveLastError($e->getMessage());
        }

        # Releasing
        $this->unlock($task->getId());
    }



	###############################################################################
	#                                                                             #
	#                          End of the tasks block.                            #
	#                                                                             #
	###############################################################################


	public function __destruct() {
		/** Detect too long processes to alert admin */
		$exec_time = microtime(true) - $this->start;
		$this->log("Execution time {$exec_time}");
	}

}
