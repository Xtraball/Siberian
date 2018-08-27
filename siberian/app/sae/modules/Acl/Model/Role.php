<?php

/**
 * Class Acl_Model_Role
 *
 * @method integer getId()
 * @method string getCode()
 * @method string getLabel()
 * @method $this setResources($resources)
 * @method $this setLabel(string $label)
 * @method $this setCode(string $code)
 */
class Acl_Model_Role extends Core_Model_Default
{
    const DEFAULT_ROLE_ID = 1;
    const DEFAULT_ADMIN_ROLE_CODE = "admin_default_role_id";

    /**
     * Acl_Model_Role constructor.
     * @param array $params
     * @throws Zend_Exception
     */
    public function __construct($params = [])
    {
        parent::__construct($params);
        $this->_db_table = 'Acl_Model_Db_Table_Acl_Role';
        return $this;
    }

    /**
     * @return $this
     */
    public function save()
    {
        parent::save();

        if ($resources = $this->getResources()) {
            $this->getTable()->saveResources($this->getId(), $resources);
        }

        return $this;
    }

    /**
     * @param $roleId
     * @return $this
     */
    public function getRoleById($roleId)
    {
        $role = $this->getTable()->findByRoleId($roleId);
        if ($role) {
            $this->setData($role->getData())->setId($roleId);
        }
        return $this;
    }

    /**
     * @return mixed
     */
    public function findDefaultRoleId()
    {
        return __get(self::DEFAULT_ADMIN_ROLE_CODE);
    }

    /**
     * @return bool
     */
    public function isDefaultRole()
    {
        if ($this->getId()) {
            $data = $this->findDefaultRoleId();
            if ($data == $this->getId()) {
                return true;
            }
        }
        return false;
    }
}
