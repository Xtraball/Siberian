<?php

class Acl_Model_Acl extends Core_Model_Default {

    /**
     * Admin's rôle
     *
     * @type string
     */
    private $__role;

    private $__role_obj;

    /**
     * ACL
     *
     * @type Zend_Acl
     */
    private $__acl;

    /**
     * Available Resources
     *
     * @type array
     */
    private $__resources = array();

    /**
     * Resources Labels
     *
     * @type array
     */
    private $__resource_labels = array();

    /**
     * Link between URLs & Resources
     *
     * @type array
     */
    private $__urls = array();

    /**
     * Prepare the ACL for a given admin
     * 
     * @params $admin Admin_Model_Admin
     * @return Acl_Model_Acl
     */
    public function prepare($admin) {

        if(!is_null($admin->getRoleId())) {
            $role = new Acl_Model_Role();
            $this->__role_obj = $role->getRoleById($admin->getRoleId());
            $this->__role = $this->__role_obj->getCode();
            $this->__build();
        }

        return $this;
    }

    /**
     * Test if the user can access a given resource
     * 
     * @params $resource string
     * @return bool
     */
    public function isAllowed($resource, $value_id = null) {

        if(is_array($resource)) {
            $resources = array(
                // sprintf("%s/*", $resource["module"]),
                sprintf("%s/%s/*", $resource["module"], $resource["controller"]),
                sprintf("%s/%s/%s", $resource["module"], $resource["controller"], $resource["action"])
            );
            //TEMP : bypassing ACL for cms feature because of inbox dependencies
            if(in_array("cms/application_page/editpost", $resources)) return true;
            if(in_array("cms/application_page/addblock", $resources)) return true;
            $resource = null;
        } else {
            $resources = array($resource);
        }

        foreach($resources as $res) {
            if(isset($this->__urls[$res])) {
                $resource = $this->__urls[$res];
                break;
            }
        }

        if(!empty($resource) AND !in_array($resource,$this->__acl->getResources())) {
            return true;
        }

        return is_null($resource) ? true : $this->__acl->isAllowed($this->__role, $resource, $value_id);
    }

    /**
     * Deny some extra resources
     *
     * @params $resources array
     * @return bool
     */
    public function denyResources($resources, $add_privileges = false) {
        $acl = $this->__acl;
        $resource_obj = new Acl_Model_Resource();
        $urls = $resource_obj->getUrls($resources);
        $this->__urls = array_merge($this->__urls, $urls);

        foreach($resources as $key => $code) {
            $acl->deny($this->__role, $code, $add_privileges ? $key : null);
        }

        return $this;
    }

    /**
     * Build the ACLs
     * 
     * @return Acl_Model_Acl
     */
    private function __build() {

        $this->__acl = new Zend_Acl();

        $role = new Zend_Acl_Role($this->__role);

        $this->__buildResources();

        $this->__acl->addRole($role);
        $this->__acl->allow($role);

        $resource = new Acl_Model_Resource();
        $denied_resources = $resource->getDeniedResources($this->__role_obj->getRoleId());

        if(!empty($denied_resources)) {
            $this->__acl->deny($role, $denied_resources, null);
        }

        return $this;

    }

    /**
     * Build the resources, updates the labels and the URLs
     * 
     * @return array
     */
    private function __buildResources() {

        if(empty($this->__resources)) {

            $resource = new Acl_Model_Resource();
            $this->__resources = $resource->getResources();

            $inserted_resources = array();

            foreach($this->__resources as $resource) {

                if(!in_array($resource, $inserted_resources)) {
                    $inserted_resources[] = $resource;
                    $this->__acl->addResource(new Zend_Acl_Resource($resource));
                } else {
                    # try to fix the duplicate
                    $resource_model = new Acl_Model_Resource();
                    $resource_model->find($resource, "code");
                    $resource_model->delete();
                }
            }

            $resource = new Acl_Model_Resource();
            $this->__urls = $resource->getUrls($this->__resources);
        }

        return $this->__resources;

    }
}
