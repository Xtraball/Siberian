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
            'title' => __('Role'),
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

            $role = (new Acl_Model_Role())->find($roleId);
            if ($role->getId()) {
                $data_title = __("Edit %s role", $role->getCode());
                $roleResources = (new Acl_Model_Resource())->findResourcesByRole($roleId);
                foreach ($roleResources as $roleResource) {
                    $resourcesData[] = $roleResource;
                }
            } else {
                $data_title = __('Create a new role');
            }

            $role = [
                'id' => $role->getId(),
                'code' => $role->getCode(),
                'label' => $role->getLabel(),
                'default' => $role->isDefaultRole()
            ];

            $payload = [
                'title' => $data_title,
                'role' => $role
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

            $role
                ->setResources($resourcesData)
                ->setLabel($roleData['label'])
                ->setCode($roleData['code'])
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
