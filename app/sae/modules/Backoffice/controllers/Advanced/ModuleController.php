<?php

class Backoffice_Advanced_ModuleController extends Backoffice_Controller_Default {

    public function loadAction() {

        $html = array(
            "title" => __("Advanced")." > ".__("Modules"),
            "icon" => "fa-sliders",
        );

        $this->_sendHtml($html);

    }

    public function findallAction() {

        $module = new Installer_Model_Installer_Module();
        $core_modules = $module->findAll(array("can_uninstall = ?" => 0), array("name ASC"));
        $installed_modules = $module->findAll(array("can_uninstall = ?" => 1), array("name ASC"));

        $data = array(
            "core_modules" => array(),
            "modules" => array(),
            "layouts" => array(),
            "icons" => array(),
        );

        foreach($core_modules as $core_module) {
            $data["core_modules"][] = array(
                "id" => $core_module->getId(),
                "name" => __($core_module->getData("name")),
                "version" => $core_module->getData("version"),
                "actions" => Siberian_Module::getActions($core_module->getData("name")),
                "created_at" => $core_module->getFormattedCreatedAt(),
                "updated_at" => $core_module->getFormattedUpdatedAt(),
            );
        }

        foreach($installed_modules as $installed_module) {
            switch($installed_module->getData("type")) {
                case "layout":
                    $type = "layouts";
                    break;
                case "icons":
                    $type = "icons";
                    break;
                default: case "module":
                    $type = "modules";
                    break;

            }
            $data[$type][] = array(
                "id" => $installed_module->getId(),
                "name" => __($installed_module->getData("name")),
                "version" => $installed_module->getData("version"),
                "actions" => Siberian_Module::getActions($installed_module->getData("name")),
                "created_at" => $installed_module->getFormattedCreatedAt(),
                "updated_at" => $installed_module->getFormattedUpdatedAt(),
            );
        }

        $this->_sendHtml($data);

    }

    public function executeAction($module, $action) {

    }

}
