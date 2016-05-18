<?php

class Siberian_Ftp {

    const DEFAULT_PORT = 21;
    const DEFAULT_PATH = "/";

    private $__host;
    private $__username;
    private $__password;
    private $__port;
    private $__path;

    private $__connection;

    protected $_files;
    protected $_directories;

    protected $_errors = array();

    public function __construct($host, $username, $password, $port, $path) {

        $this->__host = $host;
        $this->__username = $username;
        $this->__password = $password;
        $this->__port = $port;
        $this->__path = $path;

        return $this;

    }

    public function open() {

        $is_connected = false;

        $this->close();

        try {

            $this->__connection = ftp_connect($this->__host, $this->__port);
            if($this->__connection) {
                $is_connected = ftp_login($this->__connection, $this->__username, $this->__password);
            }

        } catch(Exception $e) {
            $is_connected = false;
        }


        return $is_connected;

    }

    public function close() {

        if ($this->__connection) {
            try {
                ftp_close($this->__connection);
            } catch(Exception $e) { }
        }

        $this->__connection = null;

        return $this;
    }

    public function checkConnection() {
        $is_connected = $this->open();
        $this->close();
        return $is_connected;
    }

    public function isSiberianDirectory() {

        $this->open();
        $file = $this->listFiles($this->__path."/lib/Siberian/Version.php");
        $this->close();

        return !empty($file) && is_array($file);
    }

    public function listFiles($path) {
        return ftp_nlist($this->__connection, $path);
    }

    public function addDirectory($directory) {
        $this->_directories[] = $directory;
    }
    public function addFile($src, $dst) {
        $this->_files[$src] = $dst;
    }

    public function createDirectory($directory) {

        $is_created = false;

        if($this->open()) {
            $dst = $this->_getPath($directory);
            if (!@ftp_chdir($this->__connection, $dst)) {
                $is_created = @ftp_mkdir($this->__connection, $dst);
                if (!$is_created) {
                    $this->_addError($dst);
                }
                ftp_chmod($this->__connection, 0775, $dst);
            } else {
                $is_created = true;
            }
        }

        return $is_created;

    }

    public function send() {

        if(empty($this->_directories) AND empty($this->_files)) {
            return $this;
        }

        if($this->open()) {

            foreach ($this->_files as $src => $file) {

                $pathinfo = pathinfo($file);
                $dst = $this->_getPath($pathinfo["dirname"]);
                if (!@ftp_chdir($this->__connection, $dst)) {

                    $is_created = @ftp_mkdir($this->__connection, $dst);
                    if (!$is_created) {
                        $this->_addError($dst);
                        continue;
                    }
                    ftp_chmod($this->__connection, 0775, $dst);
                }

                $dst = $this->_getPath($file);

                $is_copied = ftp_put($this->__connection, $dst, $src, FTP_BINARY);
                if (!$is_copied) {
                    $this->_addError($dst);
                }
                ftp_chmod($this->__connection, 0775, $dst);
            }

            $this->close();

            $this->_files = array();
            $this->_directories = array();

        }

        return $this;

    }

    public function getErrors() {
        return $this->_errors;
    }

    protected function _addError($path) {
        if(!in_array($path, $this->_errors)) {
            $this->_errors[] = $path;
        }
        return $this;
    }

    protected function _getPath($path) {

        if(substr($path, 0, 1) != "/") {
            $path = "/".$path;
        }
        if($this->__path != "/") {
            $path = "{$this->__path}{$path}";
        }

        return $path;
    }

}
