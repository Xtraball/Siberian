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

            $version = Version::VERSION;

            // Check if release exists
            $releaseUrl = 'https://github.com/Xtraball/Siberian/tree/v' . $version;
            Request::get($releaseUrl);
            if (Request::$statusCode == '404') {
                throw new Exception(__('There is not corresponding release to restore from, process aborted!'));
            }

            $browser = 'https://github.com/Xtraball/Siberian/raw/v' . $version . '/siberian/var/apps/browser.tgz';
            $android = 'https://github.com/Xtraball/Siberian/raw/v' . $version . '/siberian/var/apps/ionic/android.tgz';
            $ios = 'https://github.com/Xtraball/Siberian/raw/v' . $version . '/siberian/var/apps/ionic/ios.tgz';

            // Clean-up before run!
            chdir($varApps . '/ionic');
            exec('rm -f ./android.tgz');
            exec('rm -f ./ios.tgz');
            exec('rm -f ../browser.tgz');

            // Download archives from GitHub
            chdir($varApps);
            exec('wget ' . $browser);
            chdir($varApps . '/ionic');
            exec('wget ' . $android);
            exec('wget ' . $ios);

            if (!file_exists('./android.tgz') ||
                !file_exists('./ios.tgz') ||
                !file_exists('../browser.tgz')) {
                throw new Exception(__('Something went wrong while restoring files, process aborted!'));
            }

            // Clean-up & Extract!
            chdir($varApps);
            exec('rm -Rf ./browser ./overview');
            exec('tar pxzf browser.tgz');
            exec('cp -rp ./browser ./overview');
            chdir($varApps . '/ionic');
            exec('rm -Rf ./android');
            exec('tar pxzf android.tgz');
            exec('rm -Rf ./ios');
            exec('tar pxzf ios.tgz');

            // Clean-up after work!
            chdir($varApps . '/ionic');
            exec('rm -f ./android.tgz');
            exec('rm -f ./ios.tgz');
            exec('rm -f ../browser.tgz');

            // Ensure all folders are writable
            chdir($varApps);
            $writable = [
                '/browser',
                '/overview',
                '/ionic/android',
                '/ionic/ios',
            ];

            // CHMOD recursive
            foreach ($writable as $folder) {
                $tmpPath = path($varApps . $folder);
                exec('chmod -R 777 "' . $tmpPath . '"');
            }

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
