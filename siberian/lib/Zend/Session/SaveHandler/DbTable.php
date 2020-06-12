<?php

/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-webat this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Session
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Session
 */
#require_once 'Zend/Session.php';

/**
 * @see Zend_Db_Table_Abstract
 */
#require_once 'Zend/Db/Table/Abstract.php';

/**
 * @see Zend_Db_Table_Row_Abstract
 */
#require_once 'Zend/Db/Table/Row/Abstract.php';

/**
 * @see Zend_Config
 */
#require_once 'Zend/Config.php';

/**
 * Zend_Session_SaveHandler_DbTable
 *
 * @category   Zend
 * @package    Zend_Session
 * @subpackage SaveHandler
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Session_SaveHandler_DbTable
    extends Zend_Db_Table_Abstract
    implements Zend_Session_SaveHandler_Interface
{
    const PRIMARY_ASSIGNMENT                   = 'primaryAssignment';
    const PRIMARY_ASSIGNMENT_SESSION_SAVE_PATH = 'sessionSavePath';
    const PRIMARY_ASSIGNMENT_SESSION_NAME      = 'sessionName';
    const PRIMARY_ASSIGNMENT_SESSION_ID        = 'sessionId';

    const MODIFIED_COLUMN   = 'modifiedColumn';
    const LIFETIME_COLUMN   = 'lifetimeColumn';
    const DATA_COLUMN       = 'dataColumn';

    const LIFETIME          = 'lifetime';
    const OVERRIDE_LIFETIME = 'overrideLifetime';

    const PRIMARY_TYPE_NUM         = 'PRIMARY_TYPE_NUM';
    const PRIMARY_TYPE_PRIMARYNUM  = 'PRIMARY_TYPE_PRIMARYNUM';
    const PRIMARY_TYPE_ASSOC       = 'PRIMARY_TYPE_ASSOC';
    const PRIMARY_TYPE_WHERECLAUSE = 'PRIMARY_TYPE_WHERECLAUSE';

    /**
     * Session table primary key value assignment
     *
     * @var array
     */
    protected $_primaryAssignment = null;

    /**
     * Session table last modification time column
     *
     * @var string
     */
    protected $_modifiedColumn = null;

    /**
     * Session table lifetime column
     *
     * @var string
     */
    protected $_lifetimeColumn = null;

    /**
     * Session table data column
     *
     * @var string
     */
    protected $_dataColumn = null;

    /**
     * Session lifetime
     *
     * @var int
     */
    protected $_lifetime = false;

    /**
     * Whether or not the lifetime of an existing session should be overridden
     *
     * @var boolean
     */
    protected $_overrideLifetime = false;

    /**
     * Session save path
     *
     * @var string
     */
    protected $_sessionSavePath;

    /**
     * Session name
     *
     * @var string
     */
    protected $_sessionName;

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
     * Set session lifetime and optional whether or not the lifetime of an existing session should be overridden
     *
     * $lifetime === false resets lifetime to session.gc_maxlifetime
     *
     * @param int $lifetime
     * @param boolean $overrideLifetime (optional)
     * @return Zend_Session_SaveHandler_DbTable
     */
    public function setLifetime($lifetime, $overrideLifetime = null)
    {
        return $this;
    }

    /**
     * Retrieve session lifetime
     *
     * @return int
     */
    public function getLifetime()
    {
        return $this->_lifetime;
    }

    /**
     * Set whether or not the lifetime of an existing session should be overridden
     *
     * @param boolean $overrideLifetime
     * @return Zend_Session_SaveHandler_DbTable
     */
    public function setOverrideLifetime($overrideLifetime)
    {
        return $this;
    }

    /**
     * Retrieve whether or not the lifetime of an existing session should be overridden
     *
     * @return boolean
     */
    public function getOverrideLifetime()
    {
        return $this->_overrideLifetime;
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
        $this->_sessionSavePath = $save_path;
        $this->_sessionName     = $name;

        return true;
    }

    /**
     * Close session
     *
     * @return boolean
     */
    public function close()
    {
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
        $return = '';

        $row = $this->_db->fetchRow('SELECT * FROM session WHERE session_id = :id', [
            ':id' => $id
        ]);

        if ($row) {
            $return = $row['data'];

            $this->rebuildMobile($id, $return);
        } else {
            $this->destroy($id);
        }

        return $return;
    }

    /**
     * Write session data
     *
     * @param string $id
     * @param string $data
     * @return boolean
     */
    public function write($id, $data): bool
    {
        $time = time();
        $sqlQuery = 'INSERT INTO session (session_id, data, modified) 
        VALUES (:id, :data, :time) 
        ON DUPLICATE KEY UPDATE 
        data = :data, 
        modified = :time;';

        $this->_db->query($sqlQuery, [
            ':id' => $id,
            ':data' => $data,
            ':time' => $time,
        ]);

        $this->rebuildMobile($id, $data);

        return true;
    }

    /**
     * @param $id
     * @param $data
     * @return $this
     */
    public function rebuildMobile($id, $data): self
    {
        // Skip overview session_uuid
        if (array_key_exists('HTTP_REFERER', $_SERVER) &&
            stripos($_SERVER['HTTP_REFERER'], '/overview/') !== false) {
            return $this;
        }
        // Skip webapp session_uuid
        if (array_key_exists('HTTP_REFERER', $_SERVER) &&
            stripos($_SERVER['HTTP_REFERER'], '/browser/') !== false) {
            return $this;
        }

        // Mobile session
        if (0 === stripos($data, 'mobile')) {
            $sess = explode('|', $data)[1];
            $rawData = unserialize($sess);
            if (array_key_exists('object_id', $rawData)) {
                $customerId = $rawData['object_id'];

                // Also update customer table!
                $updateQuery = 'UPDATE customer
                SET session_uuid = :id
                WHERE customer_id = :customer_id;';
                $this->_db->query($updateQuery, [
                    ':id' => $id,
                    ':customer_id' => $customerId
                ]);
            }
        }

        return $this;
    }

    /**
     * Destroy session
     *
     * @param string $id
     * @return boolean
     */
    public function destroy($id)
    {
        $this->_db->query('DELETE FROM session WHERE session_id = :id;', [':id' => $id]);

        return true;
    }

    /**
     * Garbage Collection
     *
     * @param int $maxlifetime
     * @return true
     */
    public function gc($maxlifetime)
    {
        // Nope!
        return true;
    }


    /**
     * Retrieve session lifetime considering Zend_Session_SaveHandler_DbTable::OVERRIDE_LIFETIME
     *
     * @param Zend_Db_Table_Row_Abstract $row
     * @return int
     */
    protected function _getLifetime(Zend_Db_Table_Row_Abstract $row)
    {
        return $this->_lifetime;
    }

    /**
     * Retrieve session expiration time
     *
     * @param Zend_Db_Table_Row_Abstract $row
     * @return int
     */
    protected function _getExpirationTime(Zend_Db_Table_Row_Abstract $row)
    {
        return (int) $row->{$this->_modifiedColumn} + $this->_getLifetime($row);
    }
}
