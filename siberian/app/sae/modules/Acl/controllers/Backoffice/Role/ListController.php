<?php

class Acl_Backoffice_Role_ListController extends Backoffice_Controller_Default
{

    public function loadAction() {

        $html = array(
            "title" => $this->_("Roles"),
            "icon" => "fa-lock",
        );

        $this->_sendHtml($html);

    }

    public function findallAction() {
        $role = new Acl_Model_Role();
        $roles = $role->findAll();

        $default_role = $role->findDefaultRoleId();

        $data = array();
        foreach($roles as $role) {
            $is_default_role = false;
            if($role->getId() == $default_role) {
                $is_default_role = true;
            }

            $data[] = array(
                "id" => $role->getId(),
                "code" => $role->getCode(),
                "label" => $role->getLabel(),
                "default" => $is_default_role
            );
        }

        $this->_sendHtml($data);
    }
}