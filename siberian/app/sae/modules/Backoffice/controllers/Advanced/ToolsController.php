<?php

use Siberian\Version;
use Siberian\Request;
use Siberian\Exception;

/**
 * Class Backoffice_Advanced_ToolsController
 */
class Backoffice_Advanced_ToolsController extends System_Controller_Backoffice_Default
{

    /**
     *
     */
    public function loadAction()
    {
        $html = [
            'title' => sprintf('%s > %s > %s',
                __('Settings'),
                __('Advanced'),
                __('Tools')),
            'icon' => 'fa-file-code-o',
        ];
        $this->_sendJson($html);
    }

    /**
     *
     */
    public function runtestAction()
    {
        $data = Siberian_Tools_Integrity::checkIntegrity();

        $data['messageEmpty'] = __('Everything seems ok.');

        $this->_sendJson($data);
    }

    /**
     *
     */
    public function restoreappsAction()
    {
        try {
            $varApps = path('var/apps');
            self::checkWriteable();

            $version = Version::VERSION;
            $versionKey = '%VERSION%';

            // Check if release exists
            $sources = \Siberian\Provider::getSourcess();

            $releaseUrl = str_replace($versionKey, $version, $sources['release_url']['url']);
            Request::get($releaseUrl);
            if (Request::$statusCode == '404') {
                throw new Exception(__('There is not corresponding release to restore from, process aborted!'));
            }

            $browser = str_replace($versionKey, $version, $sources['browser']['url']);
            $android = str_replace($versionKey, $version, $sources['android']['url']);
            $ios = str_replace($versionKey, $version, $sources['ios']['url']);
            $iosNoads = str_replace($versionKey, $version, $sources['ios_noads']['url']);

            // Clean-up before run!
            chdir($varApps . '/ionic');
            self::verboseExec('rm -fv ./android.tgz');
            self::verboseExec('rm -fv ./ios.tgz');
            self::verboseExec('rm -fv ./ios-noads.tgz');
            self::verboseExec('rm -fv ../browser.tgz');

            // Download archives from GitHub
            chdir($varApps);
            self::verboseExec('wget -v ' . $browser);
            chdir($varApps . '/ionic');
            self::verboseExec('wget -v ' . $android);
            self::verboseExec('wget -v ' . $ios);
            self::verboseExec('wget -v ' . $iosNoads);

            if (!is_readable('./android.tgz') ||
                !is_readable('./ios.tgz') ||
                !is_readable('./ios-noads.tgz') ||
                !is_readable('../browser.tgz')) {
                throw new Exception(__('Something went wrong while restoring files, process aborted!'));
            }

            // Clean-up & Extract!
            chdir($varApps);
            self::verboseExec('rm -Rfv ./browser');
            self::verboseExec('rm -Rfv ./overview');
            self::verboseExec('tar pvxzf browser.tgz');
            self::verboseExec('mv ./browser ./overview'); // use mv, instead of cp, and untar browser a second time
            self::verboseExec('tar pvxzf browser.tgz');
            chdir($varApps . '/ionic');
            self::verboseExec('rm -Rfv ./android');
            self::verboseExec('tar pvxzf android.tgz');
            self::verboseExec('rm -Rfv ./ios');
            self::verboseExec('tar pvxzf ios.tgz');
            self::verboseExec('rm -Rfv ./ios-noads');
            self::verboseExec('tar pxzf ios-noads.tgz');

            // Clean-up after work!
            chdir($varApps . '/ionic');
            self::verboseExec('rm -fv ./android.tgz');
            self::verboseExec('rm -fv ./ios.tgz');
            self::verboseExec('rm -fv ./ios-noads.tgz');
            self::verboseExec('rm -fv ../browser.tgz');

            self::checkWriteable();

            foreach ($writable as $folder) {
                $tmpPath = path($varApps . $folder);
                if (!is_writable($tmpPath)) {
                    throw new Exception(
                        p__('backoffice',
                            'The folder %s is not writable, please check that your web user can write to it.',
                            $tmpPath));
                }
            }

            $payload = [
                'success' => true,
                'message' => __('Sources are successfully restored.')
            ];
        } catch (\Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }

        $this->_sendJson($payload);
    }

    /**
     *
     */
    public static function checkWriteable()
    {
        // Ensure all folders var/apps are writable, parent MUST be writeable in order to remove file in it.
        $varApps = path('var/apps');
        self::verboseExec('chmod -Rv 777 "' . $varApps . '"');
    }

    /**
     * @param $command
     * @throws Zend_Exception
     */
    public static function verboseExec($command)
    {
        $output = [];
        exec($command, $output);

        // Final loggind
        $logger = Zend_Registry::get('logger');
        $logger->info($output . PHP_EOL);

        dbg($output);
    }

    /**
     * Migrate current in DB sessions to Redis
     */
    public function migratetoredisAction()
    {
        try {
            if (!class_exists('Redis')) {
                throw new Siberian_Exception(__('php-redis module is required!'));
            }

            $config = Zend_Registry::get('config');
            $dbConfig = $config->resources->db->params;

            $keyPrefix = __get('redis_prefix');
            $endPoint = __get('redis_endpoint');
            $parts = parse_url($endPoint);
            $auth = __get('redis_auth');
            if (!empty($auth)) {
                $endPoint = $endPoint . '?auth=' . $auth;
            }

            $redis = new Redis();
            $redis->connect($parts['host'], $parts['port']);
            if (!empty($auth)) {
                $redis->auth($auth);
            }

            $mysql = new MySQLi($dbConfig->host, $dbConfig->username, $dbConfig->password, $dbConfig->dbname);
            $res = $mysql->query('SELECT * FROM session;');

            while (NULL !== ($row = $res->fetch_array())) {
                if (!empty($row['session_id']) && !empty($row['data'])) {
                    $redis->set($keyPrefix . $row['session_id'], $row['data']);
                }
            }

            $payload = [
                'success' => true,
                'message' => __('All sessions are now migrated to Redis.')
            ];

        } catch (Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }

        $this->_sendJson($payload);
    }

    /**
     * @throws Zend_Exception
     * @throws Zend_Json_Exception
     */
    public function saveAction()
    {

        if ($data = Zend_Json::decode($this->getRequest()->getRawBody())) {

            try {
                $this->_save($data);

                $data = array(
                    "success" => 1,
                    "message" => __("Configuration successfully saved")
                );
            } catch (Exception $e) {
                $data = array(
                    "error" => 1,
                    "message" => $e->getMessage()
                );
            }

            $this->_sendJson($data);

        }

    }

    /**
     * Testing that HTTP Basic Auth is available!
     */
    public function testbasicauthAction()
    {
        $request = $this->getRequest();

        $username = $request->getServer('PHP_AUTH_USER');
        $password = $request->getServer('PHP_AUTH_PW');

        $this->_sendJson([
            'credentials' => $username . $password
        ]);
    }

    /**
     * Testing that HTTP Bearer Auth is available!
     */
    public function testbearerauthAction()
    {
        $request = $this->getRequest();

        $bearer = $request->getHeader("Api-Auth-Bearer");

        $this->_sendJson([
            "credentials" => $bearer
        ]);
    }

    /**
     * E-mail preview
     *
     * @throws Zend_Exception
     * @throws Zend_Layout_Exception
     */
    public function testEmailAction()
    {
        $platformName = __get('platform_name');
        $message = "Mox dicta finierat, multitudo omnis ad, quae imperator voluit, promptior laudato consilio consensit in pacem ea ratione maxime percita, quod norat expeditionibus crebris fortunam eius in malis tantum civilibus vigilasse, cum autem bella moverentur externa, accidisse plerumque luctuosa, icto post haec foedere gentium ritu perfectaque sollemnitate imperator Mediolanum ad hiberna discessit.";

        $layout = new Siberian\Layout();
        $layout = $layout->loadEmail('test', 'test');
        $layout
            ->setContentFor('base', 'email_title', __('Email') . ' - ' . __('Render Test'))
            ->setContentFor('content_email', 'app_name', __('Test App'))
            ->setContentFor('content_email', 'platform_name', $platformName)
            ->setContentFor('content_email', 'message', $message)
            ->setContentFor('footer', 'show_legals', true);

        $content = $layout->render();

        die($content);
    }

}
