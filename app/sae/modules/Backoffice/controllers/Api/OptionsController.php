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
