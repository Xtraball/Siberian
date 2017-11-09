<?php

class Backoffice_Advanced_ToolsController extends System_Controller_Backoffice_Default {

    public function loadAction() {

        $html = array(
            "title" => __("Advanced")." > ".__("Tools"),
            "icon" => "fa-file-code-o",
        );

        $this->_sendJson($html);

    }

    public function runtestAction() {

        $data = Siberian_Tools_Integrity::checkIntegrity();

        $this->_sendJson($data);

    }

    public function restoreappsAction() {

        $payload = [];

        try {
            $var_apps = Core_Model_Directory::getBasePathTo('var/apps');

            if(version_compare(Siberian_Version::VERSION, '4.12.6', '<')) {
                throw new Exception(__('To be able to restore Apps, you Siberian version must be >= 4.12.6 !'));
            }

            $version = Siberian_Version::VERSION;

            $browser = 'https://github.com/Xtraball/SiberianCMS/raw/v' . $version . '/var/apps/browser.tgz';
            $android = 'https://github.com/Xtraball/SiberianCMS/raw/v' . $version . '/var/apps/ionic/android.tgz';
            $ios_noads = 'https://github.com/Xtraball/SiberianCMS/raw/v' . $version . '/var/apps/ionic/ios-noads.tgz';
            $ios = 'https://github.com/Xtraball/SiberianCMS/raw/v' . $version . '/var/apps/ionic/ios.tgz';
            $previewer = 'https://github.com/Xtraball/SiberianCMS/raw/v' . $version . '/var/apps/ionic/previewer.tgz';

            // Clean-up before run!
            chdir($var_apps . '/ionic');
            exec('rm -f ./android.tgz');
            exec('rm -f ./ios-noads.tgz');
            exec('rm -f ./ios.tgz');
            exec('rm -f ./previewer.tgz');
            exec('rm -f ../browser.tgz');

            // Download archives from GitHub
            chdir($var_apps);
            exec('wget ' . $browser);
            chdir($var_apps . '/ionic');
            exec('wget ' . $android);
            exec('wget ' . $ios_noads);
            exec('wget ' . $ios);
            exec('wget ' . $previewer);

            if (!file_exists('./android.tgz') ||
                !file_exists('./ios-noads.tgz') ||
                !file_exists('./ios.tgz') ||
                !file_exists('./previewer.tgz') ||
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
            exec('rm -Rf ./ios-noads');
            exec('tar xzf ios-noads.tgz');
            exec('rm -Rf ./ios');
            exec('tar xzf ios.tgz');
            exec('rm -Rf ./previewer');
            exec('tar xzf previewer.tgz');

            // Clean-up after work!
            chdir($var_apps . '/ionic');
            exec('rm -f ./android.tgz');
            exec('rm -f ./ios-noads.tgz');
            exec('rm -f ./ios.tgz');
            exec('rm -f ./previewer.tgz');
            exec('rm -f ../browser.tgz');

            $payload = [
                'success' => true,
                'message' => __('Sources are successfully restored.')
            ];

        } catch (Exception $e) {

            $payload = [
                'success' => false,
                'message' => __('An error occured during the request with the following message: %s.',
                    $e->getMessage())
            ];

        }

        $this->_sendJson($payload);
    }

    public function saveAction() {

        if($data = Zend_Json::decode($this->getRequest()->getRawBody())) {

            try {
                $this->_save($data);

                $data = array(
                    "success" => 1,
                    "message" => __("Configuration successfully saved")
                );
            } catch(Exception $e) {
                $data = array(
                    "error" => 1,
                    "message" => $e->getMessage()
                );
            }

            $this->_sendJson($data);

        }

    }

}
