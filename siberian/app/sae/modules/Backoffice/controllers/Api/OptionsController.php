<?php

class Backoffice_Api_OptionsController extends Api_Controller_Default  {

    /**
     * @var string
     */
    public $namespace = "backoffice";

    /**
     * @var array
     */
    public $secured_actions = array(
        "manifest",
        "clearcache",
        "cleartmp",
        "clearlogs",
    );

    /**
     * Rebuilds manifest
     */
    public function manifestAction() {
        $protocol = isset($_SERVER['HTTPS']) ? 'https://' : 'http://';
        Siberian_Autoupdater::configure(
            $protocol.$this->getRequest()->getHttpHost()
        );

        $this->_sendJson(array(
            "success" => 1,
            "message" => __("Manifest rebuilt with succes."),
        ));
    }

    /**
     * Clear cache
     */
    public function clearcacheAction() {
        Siberian_Cache::__clearCache();

        $this->_sendJson(array(
            "success" => 1,
            "message" => __("Cache cleared."),
        ));
    }

    /**
     * Clear var/tmp
     */
    public function cleartmpAction() {
        Siberian_Cache::__clearTmp();
        Application_Model_SourceQueue::clearPaths();
        Application_Model_ApkQueue::clearPaths();

        $this->_sendJson(array(
            "success" => 1,
            "message" => __("Temp cleared."),
        ));
    }

    /**
     * Clear logs
     */
    public function clearlogsAction() {
        Siberian_Cache::__clearLog();

        $this->_sendJson(array(
            "success" => 1,
            "message" => __("Logs cleared."),
        ));
    }
}
