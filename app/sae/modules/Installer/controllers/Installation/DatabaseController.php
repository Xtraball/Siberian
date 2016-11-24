<?php

class Installer_Installation_DatabaseController extends Installer_Controller_Installation_Default {

    protected $_error;

    public function checkAction() {

        if($datas = $this->getRequest()->getPost()) {

            try {

                if(empty($datas['host']) OR empty($datas['dbname']) OR empty($datas['username']) OR empty($datas['password'])) {
                    throw new Exception($this->_('Please, fill out all fields'));
                }

                $parameters = array(
                    'host'      => $datas['host'],
                    'dbname'    => $datas['dbname'],
                    'username'  => $datas['username'],
                    'password'  => $datas['password']
                );

                if(!$this->_createIni($parameters)) {
                    throw new Exception($this->_error);
                }

                $html = array('success' => 1);

            } catch (Exception $e) {
                $html = array('message' => $e->getMessage());
                $this->getResponse()->setHttpResponseCode(400);
            }

            $this->getLayout()->setHtml(Zend_Json::encode($html));
        }
    }

    public function installAction() {
        try {

            if($module = $this->getRequest()->getParam('name')) {

                $installer = new Installer_Model_Installer();
                $installer->setModuleName($module)
                    ->install()
                ;

                $html = array('success' => 1);

            } else {
                throw new Exception($this->_("No directory provided"));
            }

        } catch(Exception $e) {
            $html = array(
                'message' => $e->getMessage()
            );
        }

        $this->getLayout()->setHtml(Zend_Json::encode($html));
    }

    public function insertdataAction() {
        try {

            if($module = $this->getRequest()->getParam('name')) {

                $installer = new Installer_Model_Installer();
                $installer->setModuleName($module)
                    ->insertData()
                ;

                $html = array('success' => 1);

            } else {
                throw new Exception($this->_("No directory provided"));
            }

        } catch(Exception $e) {
            $html = array(
                'message' => $e->getMessage()
            );
        }

        $this->getLayout()->setHtml(Zend_Json::encode($html));
    }

    /**
     * Run an async XMLHTTPRequest to avoid timeout and/or exec locking
     */
    public function cronschedulerAction() {
        try {

            $file = Core_Model_Directory::getBasePathTo("app/sae/modules/Cron/resources/db/async/scheduler.php");

            $installer = new Installer_Model_Installer_Module();
            $installer->prepare("Cron");
            $installer->_run($file);

            $html = array(
                'success' => 1
            );

        } catch(Exception $e) {
            $html = array(
                'message' => $e->getMessage()
            );
        }

        $this->getLayout()->setHtml(Zend_Json::encode($html));
    }

    protected function _checkConnection(array $params) {

        try {
            $db = Zend_Db::factory('Pdo_Mysql', $params);
            $db->getConnection();
            return true;
        } catch (Zend_Db_Adapter_Exception $e) {
            // perhaps a failed login credential, or perhaps the RDBMS is not running
            $this->_error = $this->_('The database connection failed. Please, check the entered information.');
        } catch (Zend_Exception $e) {
            // perhaps factory() failed to load the specified Adapter class
            $this->_error = $e->getMessage();
        }

        return false;
    }

    protected function _createIni(array $params) {

        if($this->_checkConnection($params)) {

            try {
                $writer = new Zend_Config_Writer_Ini();

                if(!copy(APPLICATION_PATH . '/configs/app.sample.ini', APPLICATION_PATH . '/configs/app.ini')) {
                    throw new Exception("The file /app/configs/app.ini is not writable. Please check the write permissions of the /app/configs folder and try again.");
                }
                $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/app.ini', null, array('skipExtends' => true, 'allowModifications' => true));
                $config->production->resources->db->params->host = $params['host'];
                $config->production->resources->db->params->dbname = $params['dbname'];
                $config->production->resources->db->params->username = $params['username'];
                $config->production->resources->db->params->password = $params['password'];

                $writer->setConfig($config)
                       ->setFilename(APPLICATION_PATH . '/configs/app.ini')
                       ->write();

                return true;
            }
            catch(Exception $e) {
                $this->_error = $e->getMessage();
            }
        }

        return false;
    }

}