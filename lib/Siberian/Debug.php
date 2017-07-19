<?php

/**
 * Class Siberian_Debug
 *
 * Siberian debugger class, enabling MySQL profiler and various logger.
 *
 */
class Siberian_Debug {

    /**
     * @var boolean
     */
    public static $render = false;

    /**
     * @var DebugBar\StandardDebugBar
     */
    public static $debugBar;

    /**
     * @var DebugBar\JavascriptRenderer
     */
    public static $debugBarRenderer;

    /**
     * @var DebugBar\OpenHandler
     */
    public static $openHandler;

    /**
     * @var string
     */
    public static $streamPath = "var/tmp/debugstream";

    /**
     * @var array
     */
    public static $config;

    /**
     * Initialize debug renderer in function of the env/debug
     */
    public static function init() {
        self::$config = Zend_Registry::get('_config');

        if(isset(self::$config["debug"]) && self::$config["debug"]) {
            self::$debugBar = new DebugBar\StandardDebugBar();

            self::$debugBar->setStorage(new DebugBar\Storage\FileStorage(Core_Model_Directory::getBasePathTo(self::$streamPath)));
            self::$debugBar->addCollector(new Siberian_Debug_Collector_Sql());

            self::$debugBarRenderer = self::$debugBar->getJavascriptRenderer();

            self::$debugBarRenderer->setOpenHandlerUrl('/debug.php');

            self::$render = true;
        }
    }

    /**
     * Alias/Shortcut for development mode.
     *
     * @return bool
     */
    public static function isDevelopment() {
        return (APPLICATION_ENV === "development");
    }

    /**
     * @param $resource
     * @return mixed
     */
    public static function setProfiler($resource) {
        if(self::$render) {
            $resource->setProfiler(array(
                "class" => "Siberian_Db_Profiler",
                "enabled" => true,
            ));
        }

        return $resource;
    }

    /**
     * Stream Ajax handler
     */
    public static function handle() {
        self::$openHandler = new DebugBar\OpenHandler(self::$debugBar);
        self::$openHandler->handle();
    }

    /**
     * @return string
     */
    public static function renderHead() {
        if(self::$render) {
            return self::$debugBarRenderer->renderHead();
        }
        return "";
    }

    /**
     * @return string
     */
    public static function render() {
        if(self::$render) {
            return self::$debugBarRenderer->render();
        }
        return "";
    }

    /**
     * Send data in ajax queries
     */
    public static function sendDataInHeaders() {
        if(self::$render) {
            self::$debugBar->sendDataInHeaders(true);
        }
    }

    /**
     * @return array
     */
    public static function getDataAsHeaders() {
        if(self::$render) {
            return self::$debugBar->getDataAsHeaders();
        }
    }

    /**
     * @param $message
     */
    public static function message($message, $label = "info", $isString = true) {
        if(self::$render) {
            self::$debugBar["messages"]->addMessage($message, $label, $isString);
        }
    }

    /**
     * @param $message
     */
    public static function addProfile($profile) {
        if(self::$render) {
            self::$debugBar["sql"]->addProfile($profile);
        }
    }

    /**
     * @param Exception $e
     */
    public static function addException(Exception $e) {
        if(self::$render) {
            self::$debugBar["exceptions"]->addException($e);
        }
    }

}