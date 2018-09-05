<?php

/**
 * Class Acl_Model_Role
 *
 * @method integer getId()
 * @method string getCode()
 * @method string getLabel()
 * @method integer getParentId()
 * @method boolean getIsSelfAssignable()
 * @method $this setResources($resources)
 * @method $this setLabel(string $label)
 * @method $this setCode(string $code)
 * @method $this setParentId(integer $parentId)
 * @method $this setIsSelfAssignable(boolean $isSelfAssignable)
 * @method Acl_Model_Role[] findAll($values = [], $order = null, $params = [])
 */
class Acl_Model_Role extends Core_Model_Default
{
    const DEFAULT_ROLE_ID = 1;
    const DEFAULT_ADMIN_ROLE_CODE = "admin_default_role_id";

    /**
     * prevent recursive loops!
     *
     * @var int
     */
    private $loopFailSafe = 0;

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
     * @param $role
     * @return array
     */
    public function getChilds($role)
    {
        $allChilds = [];
        $this->_recursiveChilds ($allChilds, $role);

        return $allChilds;
    }

    /**
     * @param $allChilds
     * @param $role
     */
    public function _recursiveChilds (&$allChilds, $role)
    {
        $this->loopFailSafe++;
        if ($this->loopFailSafe > 20) {
            return;
        }

        $childs = $this->getTable()->findAll(['parent_id = ?' => $role->getId()]);
        foreach ($childs as $child) {
            $this->_recursiveChilds($allChilds, $child);
            $allChilds[] = $this->_asArray($child);
        }
    }

    /**
     * @param $role
     * @return array
     */
    public function _asArray ($role)
    {
        return [
            'value' => $role->getId(),
            'label' => $role->getLabel(),
        ];
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
