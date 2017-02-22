<?php

class Acl_Backoffice_Role_EditController extends Backoffice_Controller_Default
{

    public function loadAction() {

        $html = array(
            "title" => $this->_("Role"),
            "icon" => "fa-lock",
        );

        $this->_sendHtml($html);

    }

    public function findAction() {

        $resources_data = array();

        if($this->getRequest()->getParam("role_id")) {

            $role = new Acl_Model_Role();
            $role->find($this->getRequest()->getParam("role_id"));

            $resource = new Acl_Model_Resource();
            $role_resources = $resource->findResourcesByRole($this->getRequest()->getParam("role_id"));

            foreach($role_resources as $role_resource) {
                $resources_data[] = $role_resource;
            }

            $data_title = $this->_("Edit %s role", $role->getCode());

            $role = array(
                "id" => $role->getId(),
                "code" => $role->getCode(),
                "label" => $role->getLabel(),
                "default" => $role->isDefaultRole()
            );

        } else {
            $data_title = $this->_("Create a new role");
            $role = array(
                "code" => "",
                "label" => ""
            );
        }

        $data = array(
            "title" => $data_title,
            "role" => $role
        );

        $resource = new Acl_Model_Resource();
        $data["resources"] = $resource->getHierarchicalResources($resources_data);

        $this->_sendHtml($data);
    }

    public function getresourcehierarchicalAction() {
        $resource = new Acl_Model_Resource();
        $hierarchical_resources = $resource->getHierarchicalResources();
        $this->_sendHtml($hierarchical_resources);
    }

    public function saveAction() {

        if($param = Zend_Json::decode($this->getRequest()->getRawBody())) {

            try {

                $role = new Acl_Model_Role();
                if(empty($param["role"]) Or !is_array($param["role"])) {
                    throw new Exception($this->_("An error occurred while saving. Please try again later."));
                }

                $role_data = $param["role"];
                $resources_data = !empty($param["resources"]) ? $param["resources"] : array();

                if (isset($role_data["id"])) {
                    $role->find($role_data["id"]);
                }

                $resource = new Acl_Model_Resource();
                $resources_data = $resource->flattenedResources($resources_data);

                $role->setResources($resources_data)
                    ->setLabel($role_data["label"])
                    ->setCode($role_data["code"])
                    ->save()
                ;

                $config = new System_Model_Config();
                $config->find(Acl_Model_Role::DEFAULT_ADMIN_ROLE_CODE, "code");
                $default_role_id = $config->getValue();
                $new_default_role_id = null;
                
                if($default_role_id == $role->getId() AND !$role_data["default"]) {
                    $new_default_role_id = Acl_Model_Role::DEFAULT_ROLE_ID;
                } else if($role_data["default"]) {
                    $new_default_role_id = $role->getId();
                }

                if(!empty($new_default_role_id)) {
                    $config->setValue($new_default_role_id)
                        ->save()
                    ;
                }

                $data = array(
                    "success" => true,
                    "message" => $this->_("Your role has been successfully saved")
                );

            } catch(Exception $e) {
                $data = array(
                    "error" => true,
                    "message" => $e->getMessage()
                );
            }

            $this->_sendHtml($data);

        }
    }

    public function deleteAction() {
        if($this->getRequest()->getParam("role_id")) {
            $role = new Acl_Model_Role();
            $role->find($this->getRequest()->getParam("role_id"));
            $role->delete();
            $data = array(
                "success" => true,
                "message" => $this->_("Your role has been successfully deleted")
            );
        } else {
            $data = array(
                "error" => true,
                "message" => $this->_("An error occurred while deleting your role. please try again later")
            );
        }
        $this->_sendHtml($data);
    }

}