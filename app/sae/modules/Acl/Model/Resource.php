<?php

class Acl_Model_Resource extends Core_Model_Default {

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Acl_Model_Db_Table_Acl_Resource';
        return $this;
    }

    public function getResources() {
        return $this->getTable()->getResourceCodes();
    }

    public function getDeniedResources($role_id = null) {
        return $this->getTable()->findResourcesByRole($role_id);
    }

    public function findResourcesByRole($role_id) {
        return $this->getTable()->findResourcesByRole($role_id);
    }

    public function getHierarchicalResources($denied_resources = null) {

        $parent_resources = $this->findAll();
        $resources = array();
        $parents = array();
        $children = array();

        foreach($parent_resources as $key => $resource) {
            if($resource->getParentId()) {
                $children[] = $resource;
            } else {
                $parents[] = $resource;
            }
        }

        foreach($parents as $parent) {
             $tmp = $this->_buildHierarchicalResources(null, $parent, $children, $denied_resources);
             $resources = array_merge($resources, $tmp);
        }

        return $resources;

    }

    public function getUrls($resources = null) {
        $urls = array();
        if(!empty($resources)) {
            $urls_codes = $this->getTable()->getUrlByCode($resources);
            foreach ($urls_codes as $url) {
                $urls[$url->getData("url")] = $url->getCode();
            }
        }
        return $urls;
    }

    public function flattenedResources($resources, $built_resources = array()) {

        foreach($resources as $resource) {
            if(!empty($resource["children"])) {
                $built_resources += $this->flattenedResources($resource["children"], $built_resources);
                unset($resource["children"]);
            }
            $built_resources[] = $resource;
        }

        return $built_resources;
    }

    public function getFirstEditorResourceAllowed($admin) {
        $editor = $this->getTable()->findByCode("editor");
        if($editor->getResourceId()) {
            $children = $this->getTable()->findByParentId($editor->getResourceId());
            $first = false;
            $i = 0;
            $acl = new Acl_Model_Acl();
            $acl = $acl->prepare($admin->getAdmin());

            $url = 'application/customization_design_style/edit';
            while($i < count($children) AND !$first) {
                if($acl->isAllowed($children[$i]->getCode())) {
                    $first = true;
                    $url = $children[$i]->getData("url");
                }
                $i++;
            }
            return $url;
        } else {
            return 'application/customization_design_style/edit';
        }
    }

    protected function _buildHierarchicalResources($parent, $resource, $children, $denied_resources) {
        
        $resources = array();
        $child_resources = array();
        $resources[$resource->getId()] = $this->__formatResource($parent, $resource, $denied_resources);

        foreach($children as $key => $child) {

            if($child->getParentId() == $resource->getId()) {

                unset($children[$key]);

                $tmp = $this->_buildHierarchicalResources($resource, $child, $children, $denied_resources);

                if(!empty($tmp)) {
                    $child_resources = array_merge($child_resources, $tmp);
                }
            }

        }

        if(!empty($child_resources)) {
            $resources[$resource->getId()]["children"] = $child_resources;
        }

        return $resources;
    }

    private function __formatResource($parent, $resource, $denied_resources) {

        $data = array(
            "id" => $resource->getid(),
            "code" => $resource->getCode(),
            "label" => $resource->getLabel()
        );

        if($parent instanceof self) {
            $data["parent_id"] = $parent->getId();
        }
        
        if(is_array($denied_resources)) {
            $data["is_allowed"] = !in_array($resource->getCode(), $denied_resources);
        }

        return $data;

    }

}