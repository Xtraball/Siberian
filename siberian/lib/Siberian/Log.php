<?php

namespace Siberian;

use Zend_Log;
use Zend_Log_Writer_Stream;
use System_Model_Config;
use Core_Model_Secret;

/**
 * Class Log
 * @package Siberian
 */
class Log extends Zend_Log
{
    /**
     * @var string
     */
    protected $_filename = null;

    /**
     * @param $message
     * @param string $prefix
     * @param bool $exception
     * @param int $level
     * @param bool $keep_filename
     * @return $this
     * @throws \Zend_Log_Exception
     */
    public function sendException($message, $prefix = "error_", $exception = true, $level = Zend_Log::CRIT,
                                  $keep_filename = false)
    {
        $this->_writers = [];
        if (!$keep_filename || is_null($this->_filename)) {
            $this->_filename = sprintf("%s_%s.log", $prefix, uniqid());
        }
        $writer = new Zend_Log_Writer_Stream(path("/var/log/{$this->_filename}"));
        $this->addWriter($writer);
        switch ($level) {
            case Zend_Log::INFO:
                parent::info($message);
                break;
            case Zend_Log::CRIT:
            default:
                parent::crit($message);
                break;

        }

        if ($exception) {
            if (APPLICATION_ENV === "production") {
                try {
                    # Submit bug report automatically. (Only when in production)
                    ob_start();
                    phpinfo();
                    $phpinfo = ob_get_clean();

                    # system config
                    $system_model = new System_Model_Config();
                    $entries = $system_model->findAll();
                    $_config = [];
                    foreach ($entries as $entry) {
                        $_config[] = [
                            "code" => $entry->getCode(),
                            "label" => $entry->getLabel(),
                            "value" => $entry->getValue(),
                        ];
                    }

                    # error file content
                    $path = path("/var/log/{$this->_filename}");
                    $_error = file_get_contents($path);

                    $bug_report = [
                        "secret" => Core_Model_Secret::SECRET,
                        "data" => [
                            "servername" => $_SERVER["SERVER_NAME"],
                            "config" => $_config,
                            "type" => Version::TYPE,
                            "version" => Version::VERSION,
                            "raw_error" => base64_encode($_error),
                            "phpinfo" => base64_encode($phpinfo)
                        ]
                    ];

                    $request = new Request();
                    $request->post(sprintf("https://stats.xtraball.com/errors.php?type=%s", Version::TYPE), $bug_report);
                } catch (\Exception $e) {
                    // Nope!
                }

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

    /**
     * @param $message
     * @param string $name
     * @param bool $keep_filename
     * @throws \Zend_Log_Exception
     */
    public function info($message, $name = "info", $keep_filename = true)
    {
        $this->sendException($message, $name, false, Zend_Log::INFO, $keep_filename);
    }

    /**
     * @return string
     */
    public function getFilename()
    {
        return $this->_filename;
    }
}
