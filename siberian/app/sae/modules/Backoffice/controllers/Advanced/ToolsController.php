<?php

/**
 * Class Backoffice_Advanced_ToolsController
 */
class Backoffice_Advanced_ToolsController extends System_Controller_Backoffice_Default
{

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

    public function runtestAction()
    {
        $data = Siberian_Tools_Integrity::checkIntegrity();

        $data['messageEmpty'] = __('Everything seems ok.');

        $this->_sendJson($data);
    }

    public function restoreappsAction()
    {
        try {
            $var_apps = Core_Model_Directory::getBasePathTo('var/apps');

            $version = Siberian_Version::VERSION;

            // Check if release exists
            $releaseUrl = 'https://github.com/Xtraball/Siberian/tree/v' . $version;
            Siberian_Request::get($releaseUrl);
            if (Siberian_Request::$statusCode == '404') {
                throw new Exception(__('There is not corresponding release to restore from, process aborted!'));
            }

            $browser = 'https://github.com/Xtraball/Siberian/raw/v' . $version . '/siberian/var/apps/browser.tgz';
            $android = 'https://github.com/Xtraball/Siberian/raw/v' . $version . '/siberian/var/apps/ionic/android.tgz';
            $ios = 'https://github.com/Xtraball/Siberian/raw/v' . $version . '/siberian/var/apps/ionic/ios.tgz';

            // Clean-up before run!
            chdir($var_apps . '/ionic');
            exec('rm -f ./android.tgz');
            exec('rm -f ./ios.tgz');
            exec('rm -f ../browser.tgz');

            // Download archives from GitHub
            chdir($var_apps);
            exec('wget ' . $browser);
            chdir($var_apps . '/ionic');
            exec('wget ' . $android);
            exec('wget ' . $ios);

            if (!file_exists('./android.tgz') ||
                !file_exists('./ios.tgz') ||
                !file_exists('../browser.tgz')) {
                throw new Exception(__('Something went wrong while restoring files, process aborted!'));
            }

            // Clean-up & Extract!
            chdir($var_apps);
            exec('rm -Rf ./browser ./overview');
            exec('tar xzf browser.tgz');
            exec('cp -rp ./browser ./overview');
            chdir($var_apps . '/ionic');
            exec('rm -Rf ./android');
            exec('tar xzf android.tgz');
            exec('rm -Rf ./ios');
            exec('tar xzf ios.tgz');

            // Clean-up after work!
            chdir($var_apps . '/ionic');
            exec('rm -f ./android.tgz');
            exec('rm -f ./ios.tgz');
            exec('rm -f ../browser.tgz');

            $payload = [
                'success' => true,
                'message' => __('Sources are successfully restored.')
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

            $keyPrefix = System_Model_Config::getValueFor('redis_prefix');
            $endPoint = System_Model_Config::getValueFor('redis_endpoint');
            $parts = parse_url($endPoint);
            $auth = System_Model_Config::getValueFor('redis_auth');
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
            ->setContentFor('footer', 'show_legals', true)
        ;

        $content = $layout->render();

        die($content);
    }

}
