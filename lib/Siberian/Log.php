<?php

/**
 * Class Siberian_Log
 */
class Siberian_Log extends Zend_Log {

    protected $_filename = null;

    public function sendException($message, $prefix = "error_", $exception = true, $level = Zend_Log::CRIT, $keep_filename = false) {

        $this->_writers = array();
        if(!$keep_filename || is_null($this->_filename)) {
            $this->_filename = sprintf("%s_%s.log", $prefix, uniqid());
        }
        $writer = new Zend_Log_Writer_Stream(Core_Model_Directory::getBasePathTo("/var/log/{$this->_filename}"));
        $this->addWriter($writer);
        switch($level) {
            case Zend_Log::INFO:
                parent::info($message);
                break;
            case Zend_Log::CRIT:
            default:
                parent::crit($message);
                break;

        }

        if($exception) {

            if (APPLICATION_ENV == "production") {

                # Submit bug report automatically. (Only when in production)
                ob_start();
                phpinfo();
                $phpinfo = ob_get_clean();

                # system config
                $system_model = new System_Model_Config();
                $entries = $system_model->findAll();
                $_config = array();
                foreach($entries as $entry) {
                    $_config[] = array(
                        "code" => $entry->getCode(),
                        "label" => $entry->getLabel(),
                        "value" => $entry->getValue(),
                    );
                }

                # error file content
                $path = Core_Model_Directory::getBasePathTo("/var/log/{$this->_filename}");
                $_error = file_get_contents($path);

                $bug_report = array(
                    "secret"    => Core_Model_Secret::SECRET,
                    "data"      => array(
                        "servername"    => $_SERVER["SERVER_NAME"],
                        "config"        => $_config,
                        "type"          => Siberian_Version::TYPE,
                        "version"       => Siberian_Version::VERSION,
                        "raw_error"     => base64_encode($_error),
                        "phpinfo"       => base64_encode($phpinfo)
                    )
                );

                $request = new Siberian_Request();
                $request->post(sprintf("http://stats.xtraball.com/errors.php?type=%s", Siberian_Version::TYPE), $bug_report);

                header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
                header("Location: /errors/500.php?log={$this->_filename}");
            } else {
                header("Content-Type: text/plain");
                echo "Error log: {$this->_filename}" . PHP_EOL . PHP_EOL;
                echo "Stack Trace:" . PHP_EOL . PHP_EOL;
                print_r($message);
            }

            die;
        }

        return $this;
    }

    public function info($message, $name = "info", $keep_filename = true) {
        $this->sendException($message, $name, false, Zend_Log::INFO, $keep_filename);
    }

    public function getFilename() {
        return $this->_filename;
    }

}
