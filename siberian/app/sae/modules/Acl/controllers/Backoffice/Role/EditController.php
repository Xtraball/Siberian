<?php

/**
 * Class Acl_Backoffice_Role_EditController
 */
class Acl_Backoffice_Role_EditController extends Backoffice_Controller_Default
{
    /**
     *
     */
    public function loadAction()
    {
        $payload = [
            'title' => sprintf('%s > %s > %s',
                __('Manage'),
                __('Editor access'),
                __('Role')
            ),
            'icon' => 'fa-lock',
        ];

        $this->_sendJson($payload);
    }

    /**
     *
     */
    public function findAction()
    {
        try {
            $request = $this->getRequest();
            $resourcesData = [];
            $roleId = $request->getParam('role_id', null);

            $defaultRole = __get(Acl_Model_Role::DEFAULT_ADMIN_ROLE_CODE);

            $role = (new Acl_Model_Role())->find($roleId);
            if ($role->getId()) {
                $data_title = __("Edit %s role", $role->getCode());
                $roleResources = (new Acl_Model_Resource())->findResourcesByRole($roleId);
                foreach ($roleResources as $roleResource) {
                    $resourcesData[] = $roleResource;
                }
            } else {
                $data_title = __('Create a new role');
                $role->setParentId(1);
            }

            $allParents = (new Acl_Model_Role())->findAll(['role_id != ?' => $roleId]);
            $parentsData = [];
            foreach($allParents as $allParent) {
                $default = ($defaultRole == $allParent->getId()) ?
                    ' (' . __('Default role for all new users') . ')' : '';

                if ($role->getId() &&
                    !$this->isChild($role, $allParent)) {
                    continue;
                }

                $parentsData[] = [
                    'value' => $allParent->getId(),
                    'label' => sprintf(
                        "%s - %s%s",
                        $allParent->getLabel(),
                        $allParent->getCode(),
                        $default),
                ];
            }

            $role = [
                'id' => $role->getId(),
                'code' => $role->getCode(),
                'label' => $role->getLabel(),
                'parent_id' => $role->getParentId(),
                'is_self_assignable' => (boolean) $role->getIsSelfAssignable(),
                'default' => $role->isDefaultRole()
            ];

            $payload = [
                'title' => $data_title,
                'role' => $role,
                'parents' => $parentsData,
            ];

            $payload['resources'] = (new Acl_Model_Resource())->getHierarchicalResources($resourcesData);

        } catch (\Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }

    /**
     * @param $role
     * @param $possibleParent
     * @return bool
     * @throws Zend_Exception
     */
    private function isChild($role, $possibleParent)
    {
        $currentId = $role->getId();
        $parentId = $possibleParent->getParentId();
        while ($parentId != null) {
            $parent = (new Acl_Model_Role())->find($parentId);
            // If we find the current role in the ancestors we will reject this node as a parent!
            if ($parent->getId() == $currentId) {
                return false;
            }
            $parentId = $parent->getParentId();
        }
        return true;
    }

    /**
     *
     */
    public function getresourcehierarchicalAction()
    {
        try {
            $payload = (new Acl_Model_Resource())->getHierarchicalResources();
        } catch (\Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }

    /**
     *
     */
    public function saveAction()
    {
        try {
            $request = $this->getRequest();
            $params = $request->getBodyParams();

            if (empty($params)) {
                throw new \Siberian\Exception(__('Missing params'));
            }

            $role = new Acl_Model_Role();
            if (empty($params['role']) ||
                !is_array($params['role'])) {
                throw new Exception(__("An error occurred while saving. Please try again later."));
            }

            $roleData = $params['role'];
            $resourcesData = !empty($params['resources']) ? $params['resources'] : [];

            if (isset($roleData["id"])) {
                $role->find($roleData["id"]);
            }

            $resourcesData = (new Acl_Model_Resource())
                ->flattenedResources($resourcesData);

            $parentId = $roleData['parent_id'];
            // Ensure loop-free parent ids!
            if ($role->getId() &&
                $role->getId() == $parentId) {
                $parentId = 1;
            }

            if ($role->getId()) {
                // Check if the parent is valid and won't break the tree.
                $this->checkHierarchy($parentId, $role->getId());
            }

            $role
                ->setResources($resourcesData)
                ->setLabel($roleData['label'])
                ->setCode($roleData['code'])
                ->setParentId($parentId)
                ->setIsSelfAssignable(filter_var($roleData['is_self_assignable'], FILTER_VALIDATE_BOOLEAN))
                ->save();

            $defaultRoleId = __get(Acl_Model_Role::DEFAULT_ADMIN_ROLE_CODE);
            $newDefaultRoleId = null;

            if ($defaultRoleId == $role->getId() &&
                !$roleData['default']) {
                $newDefaultRoleId = Acl_Model_Role::DEFAULT_ROLE_ID;
            } else if ($roleData['default']) {
                if (__getConfig('is_demo')) {
                    // Demo version
                    throw new \Siberian\Exception(__('This is a demo version, you are not allowed to change the default role.'));
                }

                $newDefaultRoleId = $role->getId();
            }

            if (!empty($newDefaultRoleId)) {
                __set(Acl_Model_Role::DEFAULT_ADMIN_ROLE_CODE, $newDefaultRoleId);
            }

            $payload = [
                'success' => true,
                'message' => __('Your role has been successfully saved')
            ];

        } catch (Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }

        $this->_sendJson($payload);
    }

    /**
     * @param $parentId
     * @param $roleId
     * @throws Zend_Exception
     * @throws \Siberian\Exception
     */
    private function checkHierarchy($parentId, $roleId)
    {
        while ($parentId != null) {
            $role = (new Acl_Model_Role())->find($parentId);
            if ($role->getId() &&
                $role->getId() != $roleId) {
                $parentId = $role->getParentId();
            } else {
                throw new \Siberian\Exception(__("You can't assign a role to this child role, this will break the hierarchy!"));
            }
        }
    }

    /**
     * @throws Zend_Exception
     */
    public function deleteAction()
    {
        try {
            $request = $this->getRequest();
            $roleId = $request->getParam('role_id', false);

            if ($roleId === false) {
                throw new \Siberian\Exception(__('Missing params'));
            }

            $role = (new Acl_Model_Role())->find($roleId);
            $role->delete();

            $payload = [
                'success' => true,
                'message' => __('Your role has been successfully deleted')
            ];

        } catch (\Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }
}
