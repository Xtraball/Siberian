<?php

use Siberian\File;
use Siberian\Provider;
use Siberian\Exception;

/**
 * Class Installer_Backoffice_ModuleController
 */
class Installer_Backoffice_ModuleController extends Backoffice_Controller_Default
{
    /**
     * @var array
     */
    public static $MODULES = [];

    /**
     * @var bool
     */
    public $increase_timelimit = false;

    /**
     *
     */
    public function loadAction()
    {
        if (class_exists('Core_Model_Statistics')) {
            $stats = new Core_Model_Statistics();
            $stats->statistics();
        }

        function _return_bytes($val)
        {
            $val = trim((string) $val);
            if ($val === '') {
                return 0;
            }
            $last = strtolower($val[strlen($val) - 1]);
            // "128M" is multiplied below: take the number first, a suffixed string is not numeric
            if (in_array($last, ['g', 'm', 'k'], true)) {
                $val = (int) $val;
            }
            switch ($last) {
                // Le modifieur 'G' est disponible depuis PHP 5.1.0
                case 'g':
                    $val *= 1024;
                case 'm':
                    $val *= 1024;
                case 'k':
                    $val *= 1024;
            }

            return $val;
        }

        $postMaxSize = (int)_return_bytes(ini_get('post_max_size'));
        $uploadMaxFilesize = (int)_return_bytes(ini_get('upload_max_filesize'));
        $max = min($postMaxSize, $uploadMaxFilesize);

        $payload = [
            'title' => sprintf('%s > %s',
                __('Settings'),
                __('Updates & Modules')),
            'icon' => 'fa-cloud-download',
            'words' => [
                'emptyLicense' => p__('backoffice', 'License is required!'),
                'setLicense' => p__('backoffice', 'Please enter your module license before installing!'),
                'confirmLicense' => p__('backoffice', 'Validate'),
                'cancelLicense' => p__('backoffice', 'Go back!'),
                'replaceLicense' => p__('backoffice', 'The license saved for this module is expired or no longer valid. If you received a new key, enter it here, or keep the current one.'),
                'keepLicense' => p__('backoffice', 'Keep the current key'),
                'licenseNeeded' => p__('backoffice', 'A licence key is required to install this module, the installation was not started.'),
                //
                'titleMajor' => __('Major update disclaimer, confirmation required!'),
                'confirmDelete' => __('Yes, Proceed to update!'),
                'cancelDelete' => __('No, go back!'),
                'reinstallTitle' => p__('backoffice', 'This version is already installed'),
                'reinstallText' => p__('backoffice', 'Would you like to reinstall it? Files are copied again and the module schema and data are synchronized again.'),
                'reinstallConfirm' => p__('backoffice', 'Yes, reinstall'),
                'reinstallCancel' => p__('backoffice', 'No, cancel'),
                'mismatch' => __('The entered text mismatch'),
                'confirmKey' => 'yes-proceed-to-update-#VERSION#',
                'maxSize' => p__('backoffice', 'The file you are trying to send is larger than `post_max_size` or `upload_max_filesize`, please check your PHP settings.'),
                'majorMessage' =>
                    "<b class=\"delete-warning\">" .
                    __("You are about to update to version %s, this version introduces breaking changes in the platform,", "#VERSION#") .
                    "<br />" .
                    __("before updating we strongly advise you to read the release note and the following document.") .
                    "</b>" .
                    "<br />" .
                    "<br />" .
                    "<a class=\"btn btn-primary\" href=\"https://updates02.siberiancms.com/release-notes/major/#VERSION#.html\">Version #VERSION# technical notes.</a>" .
                    "<br />" .
                    "<br />" .
                    __("To prevent accidental actions we ask you to confirm your intention.") .
                    "<br />" .
                    "Please type <code style=\"user-select: none;\">yes-proceed-to-update-#VERSION#</code> to continue or close this modal to cancel."
            ],
            'ini' => [
                'max_size' => $max,
                'post_max_size' => $postMaxSize,
                'upload_max_filesize' => $uploadMaxFilesize,
            ]
        ];

        $this->_sendJson($payload);
    }

    /**
     *
     */
    public function downloadupdateAction()
    {
        try {
            $fatalErrors = false;
            $_errors = [];

            if (function_exists('exec')) {
                // Testing zip/unzip !
                $base = Core_Model_Directory::getBasePathTo('/var/tmp/');
                $zip_file = Core_Model_Directory::getBasePathTo('/var/tmp/test.zip');
                $test_file = Core_Model_Directory::getBasePathTo('/var/tmp/test.file');

                if (file_exists($test_file)) {
                    unlink($test_file);
                }
                if (file_exists($zip_file)) {
                    unlink($zip_file);
                }

                try {
                    File::putContents($test_file, 'test');
                    chdir($base);
                    exec('zip test.zip test.file');
                    if (!file_exists($zip_file)) {
                        //$_errors[] = 'Please enable/add binary: zip & unzip';
                        //$fatalErrors = true;
                    } else {
                        // now test unzip
                        if (file_exists($test_file)) {
                            unlink($test_file);
                        }
                        exec('unzip test.zip');
                        if (!file_exists($test_file)) {
                            //$_errors[] = 'Please enable/add binary: unzip';
                            //$fatalErrors = true;
                        }
                    }
                } catch (\Exception $e) {
                    $_errors[] = 'Please enable/add binary: zip';
                    $fatalErrors = true;
                } finally {
                    // Unlink files
                    if (file_exists($test_file)) {
                        unlink($test_file);
                    }
                    if (file_exists($zip_file)) {
                        unlink($zip_file);
                    }
                }
            } else {
                $_errors[] = 'Please enable/add function: exec()';
                $fatalErrors = true;
            }

            if ($fatalErrors) {
                throw new Siberian_Exception(implode_polyfill(', ', $_errors));
            }

            set_time_limit(6000);
            ini_set('max_execution_time', 6000);
            ini_set('memory_limit', '512M');

            $data = $this->_fetchUpdates();

            if (empty($data['success'])) {
                throw new Siberian_Exception(__('An error occurred while loading. Please, try again later.'));
            }

            if (!empty($data['url']) && !empty($data['filename'])) {

                // The same download as `php cli update`
                $tmp_path = Installer_Model_Installer_Update::platform($_SERVER['HTTP_HOST'] ?? null)
                    ->download($data, Core_Model_Directory::getTmpDirectory(true));

                $payload = $this->_getPackageDetails($tmp_path);
            } else {
                $payload = [
                    'success' => true,
                    'message' => __($data['message'])
                ];
            }
        } catch (\Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }

        $this->_sendJson($payload);
    }

    /**
     * @throws Zend_Exception
     * @throws Zend_File_Transfer_Exception
     * @throws Zend_Loader_Exception
     */
    public function uploadAction()
    {
        try {
            // Demo version
            if (__getConfig('is_demo')) {
                throw new Exception("This is a demo version, no modules can be uploaded");
            }

            if (empty($_FILES) || empty($_FILES['file']['name'])) {
                throw new Exception(__("No file has been sent"));
            }

            $adapter = new Zend_File_Transfer_Adapter_Http();
            $adapter->setDestination(Core_Model_Directory::getTmpDirectory(true));

            if ($adapter->receive()) {
                $file = $adapter->getFileInfo();
                $payload = $this->_getPackageDetails($file['file']['tmp_name'], true);

                // Testing package validity
                if (!self::_sIsAllowed($payload['package_details']['_name'])) {
                    throw new Exception(p__('backoffice', 'Only modules sold on our marketplace can be installed on your Hosted service, if you want to know more please contact our support team.'));
                }
            } else {
                $messages = $adapter->getMessages();
                if (!empty($messages)) {
                    $message = implode_polyfill("\n", $messages);
                } else {
                    $message = __("An error occurred during the process. Please try again later.");
                }

                throw new Exception($message);
            }
        } catch (\Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage(),
                'trace' => $e->getTrace()
            ];
        }

        $this->_sendJson($payload);
    }

    /**
     *
     */
    public function checkpermissionsAction()
    {
        if ($file = $this->getRequest()->getParam("file")) {
            try {
                $filename = base64_decode($file);
                $file = Core_Model_Directory::getTmpDirectory(true) . "/$filename";

                if (!file_exists($file)) {
                    throw new \Siberian\Exception(__("The file %s does not exist", $filename));
                }

                // The same check as `php cli update` (web user first, then the saved FTP access)
                Installer_Model_Installer_Update::checkPermissions($file);

                $payload = [
                    'success' => true
                ];

            } catch (\Exception $e) {
                $payload = [
                    'error' => true,
                    'message' => $e->getMessage()
                ];
            }

            $this->_sendJson($payload);
        }
    }

    public function saveftpAction()
    {

        if (($data = $this->_jsonBody()) !== null) {

            try {

                $error_code = 0;
                $ftp_host = !empty($data["host"]) ? $data["host"] : null;
                $ftp_user = !empty($data["username"]) ? $data["username"] : null;
                $ftp_password = !empty($data["password"]) ? $data["password"] : null;
                $ftp_port = !empty($data["port"]) ? $data["port"] : Siberian_Ftp::DEFAULT_PORT;
                $ftp_path = null;

                if (!empty($data["path"])) {
                    $ftp_path = rtrim($data["path"], "/");
                }
                if (!$ftp_path) {
                    $ftp_path = Siberian_Ftp::DEFAULT_PATH;
                }

                $ftp = new Siberian_Ftp($ftp_host, $ftp_user, $ftp_password, $ftp_port, $ftp_path);
                if (!$ftp->checkConnection()) {
                    $error_code = 1;
                    throw new \Siberian\Exception(__("Unable to connect to your FTP. Please check the connection information."));
                } else if (!$ftp->isSiberianDirectory()) {
                    $error_code = 2;
                    throw new \Siberian\Exception(__("Unable to detect your site. Please make sure the entered path is correct."));
                }

                $fields = [
                    "ftp_host" => $ftp_host,
                    "ftp_username" => $ftp_user,
                    "ftp_password" => $ftp_password,
                    "ftp_port" => $ftp_port,
                    "ftp_path" => $ftp_path,
                ];

                foreach ($fields as $key => $value) {
                    $config = new System_Model_Config();
                    $config->find($key, "code");

                    if (!$config->getId()) {
                        $config->setCode($key)
                            ->setLabel(ucfirst(implode_polyfill(" ", explode("_", $key))));
                    }

                    $config->setCode($key)
                        ->setValue($value)
                        ->save();
                }

                $data = [
                    "success" => 1,
                    "message" => __("Info successfully saved")
                ];

            } catch (\Exception $e) {
                $data = [
                    "error" => 1,
                    "code" => $error_code,
                    "message" => $e->getMessage()
                ];
            }

            $this->_sendJson($data);
        }
    }

    public function copyAction()
    {
        if ($file = $this->getRequest()->getParam("file")) {

            $data = [];

            try {

                $filename = base64_decode($file);
                $file = Core_Model_Directory::getTmpDirectory(true) . "/$filename";

                if (!file_exists($file)) {
                    throw new Siberian_Exception(__("The file %s does not exist", $filename));
                }

                // The same copy as `php cli update`
                Installer_Model_Installer_Update::copyPackage($file);
                $data = ["success" => 1];

            } catch (\Exception $e) {
                $data = [
                    "error" => 1,
                    "message" => $e->getMessage()
                ];
            }

            $this->_sendJson($data);
        }

    }

    public function installAction()
    {

        # Increase the timelimit to ensure update will finish
        //$this->increase_timelimit = set_time_limit(300);

        $data = [];
        try {

            // Opt-in only: without the parameter nothing changes.
            $reinstall = (string) $this->getRequest()->getParam('reinstall', '');

            // The same steps as `php cli update`; the tick stops before the time limit and the browser calls again
            self::$MODULES = Installer_Model_Installer_Update::installPending($reinstall, function () {
                $this->_signalRetry();
            });

            $host = $this->getRequest()->getHeader("host");
            if ($host AND $host == base64_decode("YXBwcy5tb2JpdXNjcy5jb20=")) {
                $email = base64_decode("Y29udGFjdEBzaWJlcmlhbmNtcy5jb20=");
                $object = "$host - Siberian Update";
                $message = "Siberian " . Siberian_Version::NAME . " " . Siberian_Version::VERSION;
                mail($email, $object, $message);
            }

            $data = [
                "success" => 1,
                "message" => __("Module successfully installed")
            ];

            # Try to increase max execution time (if the set failed)
            $this->_signalRetry();

            $protocol = isset($_SERVER['HTTPS']) ? 'https://' : 'http://';
            Installer_Model_Installer_Update::finalize($protocol . $this->getRequest()->getHttpHost());

        } catch (Siberian_Exec_Exception $e) {
            $data = [
                "success" => 1,
                "reached_timeout" => true,
                "message" => $e->getMessage()
            ];
        } catch (\Exception $e) {
            $data = [
                "error" => 1,
                "message" => $e->getMessage()
            ];
        }

        $this->_sendHtml($data);

    }

    /**
     * Detect if we are close to the timeout and send a signal to continue the installation process.
     *
     * @throws Siberian_Exec_Exception
     */
    protected function _signalRetry()
    {
        if (!$this->increase_timelimit) {
            if (Siberian_Exec::willReachMaxExecutionTime(5)) {
                throw new Siberian_Exec_Exception("Installation will continue, please wait ...");
            }
        }
    }

    protected function _fetchUpdates()
    {
        // The same check as `php cli update`: channel, licence and messages live in Siberian\Update\Platform
        return Installer_Model_Installer_Update::platform($_SERVER['HTTP_HOST'] ?? null)->check();
    }

    /**
     * @param $file
     * @param bool $skipSave
     * @return array
     * @throws Zend_Exception
     */
    protected function _getPackageDetails($file, $skipSave = false)
    {
        $installer = new Installer_Model_Installer();
        $installer->parse($file, $skipSave);

        $package = $installer->getPackageDetails();

        $path = pathinfo($file);
        $filename = $path['filename'] . '.' . $path['extension'];

        $data = [
            'success' => true,
            'filename' => base64_encode($filename),
            'package_details' => [
                '_name' => $package->getName(),
                'name' => __('%s Update', $package->getName()),
                'version' => $package->getVersion(),
                'description' => $package->getDescription(),
                'code' => $package->getCode() ?? false,
                'item_id' => $package->getItemId() ?? false,
            ]
        ];

        $data['release_note'] = $package->getReleaseNote() ??
            [
                'url' => false,
                'show' => false,
            ];
        $data['package_details']['installed_version'] = false;
        $data['package_details']['can_reinstall'] = false;
        try {
            $installed = (new Installer_Model_Installer_Module())->prepare($package->getName(), false);
            if ($installed->isInstalled()) {
                $data['package_details']['installed_version'] = $installed->getVersion();
                $data['package_details']['can_reinstall'] = Installer_Model_Installer_Reinstall::isAllowed(
                    true,
                    $installed->getVersion(),
                    $package->getVersion());
            }
        } catch (\Exception $e) {
            // Best effort, the regular install flow does not depend on it.
        }
        $data['package_details']['restore_apps'] = $package->getRestoreApps() ?? false;
        $data['package_details']['cleanup_files'] = $package->getCleanupFiles() ?? false;

        return $data;
    }

    public function checkModuleLicenseAction()
    {
        $code = false;
        try {

            $request = $this->getRequest();
            $code = $request->getParam('code', false);
            $itemId = $request->getParam('itemId', false);

            if ($code === false || $itemId === false) {
                throw new \Siberian\Exception(p__('backoffice', 'Params code & itemId are required.'));
            }

            $license = trim(__get($code . '_key'));

            if (empty($license)) {
                $code = 'license_empty';
                throw new \Siberian\Exception(p__('backoffice', 'License required.'));
            }

            try {
                \Siberian\License::checkModuleLicense($code, $itemId);
            } catch (\Siberian\License\ReplaceableException $e) {
                $code = 'license_replaceable';
                throw $e;
            } catch (\Exception $e) {
                $code = 'license_error';
                throw $e;
            }

            $payload = [
                'success' => true,
            ];
        } catch (\Exception $e) {
            $payload = [
                'error' => true,
                'code' => $code,
                'message' => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }

    public function setModuleLicenseAction()
    {
        $code = false;
        try {

            $request = $this->getRequest();
            $code = $request->getParam('code', false);
            $itemId = $request->getParam('itemId', false);
            $license = trim($request->getParam('license', false));

            if ($code === false || $itemId === false || $license === false) {
                throw new \Siberian\Exception(p__('backoffice', 'Params code, itemId & license are required.'));
            }

            try {
                \Siberian\License::checkModuleLicense($code, $itemId, $license);
                __set($code . '_key', $license);
            } catch (\Exception $e) {
                $code = 'license_error';
                throw $e;
            }

            $payload = [
                'success' => true,
            ];
        } catch (\Exception $e) {
            $payload = [
                'error' => true,
                'code' => $code,
                'message' => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }

    /**
     * Module updates, list. `force=1` asks the downloader again (the cache is served if it is down).
     */
    public function checkupdatesAction()
    {
        try {
            $updates = Installer_Model_ModuleUpdates::service();
            $list = $updates->refresh((bool) $this->getRequest()->getParam('force', false));
            $payload = [
                'success' => true,
                'updates' => $updates->pending(Installer_Model_ModuleUpdates::installed(), $list),
                'stale' => $list['stale'],
                'fetched_at' => $list['fetched_at'],
                'terms_accepted' => $updates->termsAccepted($list),
                'terms_hash' => $list['manifest']['terms']['hash'],
            ];
        } catch (\Siberian\ModuleUpdates\Exception $e) {
            $payload = Installer_Model_ModuleUpdates::errorPayload($e);
        }

        $this->_sendJson($payload);
    }

    /**
     * The live terms of download, served by the downloader.
     */
    public function termsAction()
    {
        try {
            $payload = ['success' => true, 'terms' => Installer_Model_ModuleUpdates::service()->fetchTerms()];
        } catch (\Siberian\ModuleUpdates\Exception $e) {
            $payload = Installer_Model_ModuleUpdates::errorPayload($e);
        }

        $this->_sendJson($payload);
    }

    /**
     * Records that this administrator accepted the terms identified by `hash`.
     */
    public function accepttermsAction()
    {
        try {
            if (!$this->getRequest()->isPost()) {
                throw new \Siberian\ModuleUpdates\Exception('bad_request', p__('backoffice', 'This action requires a POST request.'));
            }
            $session = $this->getSession(Core_Model_Session::TYPE_BACKOFFICE);
            Installer_Model_ModuleUpdates::service()->acceptTerms(
                (string) $this->getRequest()->getParam('hash', ''),
                $session->getBackofficeUserId() ?: 'backoffice'
            );
            $payload = ['success' => true];
        } catch (\Siberian\ModuleUpdates\Exception $e) {
            $payload = Installer_Model_ModuleUpdates::errorPayload($e);
        }

        $this->_sendJson($payload);
    }

    /**
     * Downloads the update of one module, then answers exactly like downloadupdateAction: the
     * backoffice continues with the usual permissions, copy and install steps.
     */
    public function updatemoduleAction()
    {
        try {
            if (!$this->getRequest()->isPost()) {
                throw new \Siberian\ModuleUpdates\Exception('bad_request', p__('backoffice', 'This action requires a POST request.'));
            }
            if (__getConfig('is_demo')) {
                throw new \Siberian\ModuleUpdates\Exception('demo', p__('backoffice', 'This is a demo version, no modules can be updated.'));
            }

            set_time_limit(600);
            $code = (string) $this->getRequest()->getParam('code', '');
            // Only for a module without a saved key (or to replace a refused one); POST body, never the URL.
            $license = (string) $this->getRequest()->getPost('license', '');
            $path = Installer_Model_ModuleUpdates::download($code, $license);

            $payload = $this->_getPackageDetails($path);
        } catch (\Siberian\ModuleUpdates\Exception $e) {
            $payload = Installer_Model_ModuleUpdates::errorPayload($e);
        } catch (\Exception $e) {
            $payload = ['error' => true, 'code' => 'install', 'message' => $e->getMessage()];
        }

        $this->_sendJson($payload);
    }

    public static function _sIsAllowed($moduleName)
    {
        // Skip for SAE!
        if (Siberian\Version::is('SAE')) {
            return true;
        }

        $licenseCheck = System_Controller_Backoffice_Default::getLicenseType();

        // If it's an hosted, this is on.
        $isHosted = false;
        if (array_key_exists('result', $licenseCheck) &&
            array_key_exists('type', $licenseCheck['result']) &&
            in_array($licenseCheck['result']['type'], ['mae-hosted', 'pe-hosted'])) {
            $isHosted = true;
        }

        // It's not an hosted, skip whitelist!
        // The whitelist bypass is enforced!
        if (!$isHosted || (__getConfig('bypass_whitelist') === true)) {
            return true;
        }

        // The module is whitelisted, continue the install.
        $whitelist = Provider::getWhitelistHosted();
        if (array_key_exists($moduleName, $whitelist)) {
            return true;
        }

        // Do not allow the module to be installed!
        return false;
    }

}
