<?php

/**
 * @see Zend_Session
 */
require_once 'Zend/Session.php';

/**
 * @see Zend_Config
 */
require_once 'Zend/Config.php';

/**
 * @see Zend_Session_SaveHandler_Interface
 */
require_once 'Zend/Session/SaveHandler/Interface.php';

/**
 * @see Zend_Session_SaveHandler_Exception
 */
require_once 'Zend/Session/SaveHandler/Exception.php';

/**
 * Redis save handler for Zend_Session
 *
 * @author Ivan Shumkov
 * @package Rediska
 * @subpackage ZendFrameworkIntegration
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class Zend_Session_SaveHandler_Redis implements Zend_Session_SaveHandler_Interface {
    /**
     * Sessions set
     *
     * @var Redis connection
     */
    protected $_redis;

    /**
     * Session name
     *
     * @var string
     */
    protected $_sessionName;

    /**
     * Configuration
     *
     * @var array
     */
    protected $_options = [
        'keyPrefix' => 'PHPREDIS_SESSIONS:',
        'endpoint' => 'tcp://127.0.0.1:6379',
        'host' => '127.0.0.1',
        'port' => '6379',
        'auth' => false,
        'lifetime' => null,
    ];

    /**
     * Exception class name for options
     *
     * @var string
     */
    protected $_optionsException = 'Zend_Session_SaveHandler_Exception';

    /**
     * Construct save handler
     *
     * @param Zend_Config|array $config
     */
    public function __construct($config = [])
    {
        if ($config instanceof Zend_Config) {
            $config = $config->toArray();
        }

        if (isset($config['keyPrefix'])) {
            $this->_options['keyPrefix'] = $config['keyPrefix'];
        }

        if (isset($config['endpoint'])) {
            $this->_options['endpoint'] = $config['endpoint'];

            $parts = parse_url($this->_options['endpoint']);
            $this->_options['host'] = $parts['host'];
            $this->_options['port'] = $parts['port'];
        }

        if (isset($config['auth']) && !empty($config['auth'])) {
            $this->_options['auth'] = $config['auth'];
        }

        $this->_redis = new Redis();
    }

    /**
     * Destructor
     *
     * @return void
     */
    public function __destruct()
    {
        Zend_Session::writeClose();
    }

    /**
     * Open Session
     *
     * @param string $save_path
     * @param string $name
     * @return boolean
     */
    public function open($save_path, $name)
    {
        $this->_redis->connect($this->_options['host'], $this->_options['port']);
        if ($this->_options['auth'] !== false) {
            $this->_redis->auth($this->_options['auth']);
        }

        $this->_sessionName = $name;

        return true;
    }

    /**
     * Close session
     *
     * @return boolean
     */
    public function close()
    {
        $this->_redis->close();

        return true;
    }

    /**
     * Read session data
     *
     * @param string $id
     * @return string
     */
    public function read($id)
    {
        return $this->_redis->get($this->_options['keyPrefix'] . $id);
    }

    /**
     * Write session data
     *
     * @param string $id
     * @param string $data
     * @return boolean
     */
    public function write($id, $data)
    {
        try {
            return $this->_redis->set($this->_options['keyPrefix'] . $id, $data);
        } catch (RedisException $e) {
            //
        }

        return true;
    }

    /**
     * Destroy session
     *
     * @param string $id
     * @return boolean
     */
    public function destroy($id)
    {
        try {
            $this->_redis->delete($this->_options['keyPrefix'] . $id);
        } catch(RedisException $e) {
            //
        }

        return true;
    }

    /**
     * Garbage Collection
     *
     * @param int $maxlifetime
     */
    public function gc($maxlifetime) {}

}