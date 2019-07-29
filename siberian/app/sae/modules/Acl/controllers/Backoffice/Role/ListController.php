<?php

/**
 * Class Acl_Backoffice_Role_ListController
 */
class Acl_Backoffice_Role_ListController extends Backoffice_Controller_Default
{
    /**
     * Loop fail-safe incremental counter!
     *
     * @var int
     */
    private $preventLoops = 0;

    /**
     * 
     */
    public function loadAction()
    {
        $payload = [
            'title' => sprintf('%s > %s > %s',
                __('Manage'),
                __('Editor access'),
                __('Roles')
            ),
            'icon' => 'fa-lock',
        ];

        $this->_sendJson($payload);
    }

    /**
     * @throws Zend_Exception
     */
    public function findallAction()
    {
        $rootRole = (new Acl_Model_Role())->find(1);
        $defaultRole = __get(Acl_Model_Role::DEFAULT_ADMIN_ROLE_CODE);

        $currentParent = null;
        $fetchChildsRecursively = null;

        $rootRoleData = $this->prepareRole($rootRole, $defaultRole);
        $rootRoleData['childs'] = $this->fetchChildsRecursively($rootRoleData, $defaultRole);

        $this->_sendJson([$rootRoleData]);
    }

    /**
     * @param $role
     * @param $defaultRole
     * @return mixed
     * @throws Zend_Exception
     */
    function fetchChildsRecursively ($role, $defaultRole)
    {
        // Prevent loops in recursive methods!
        $this->preventLoops++;
        if ($this->preventLoops > 1000) {
            return [];
        }

        $childs = (new Acl_Model_Role())->findAll(['parent_id = ?' => $role['id']]);

        $preparedChilds = [];
        foreach ($childs as $child) {
            $tempChild = $this->prepareRole($child, $defaultRole);
            $tempChild['childs'] = $this->fetchChildsRecursively($tempChild, $defaultRole);

            $preparedChilds[] = $tempChild;
        }

        return $preparedChilds;
    }

    /**
     * @param Acl_Model_Role $role
     * @param $defaultRole
     * @return array
     */
    private function prepareRole($role, $defaultRole)
    {
        $isDefaultRole = false;
        if ($role->getId() == $defaultRole) {
            $isDefaultRole = true;
        }

        $roleData = [
            'id' => (integer) $role->getId(),
            'code' => (string) $role->getCode(),
            'label' => (string) $role->getLabel(),
            'is_self_assignable' => (boolean) $role->getIsSelfAssignable(),
            'default' => (boolean) $isDefaultRole
        ];

        return $roleData;
    }
}