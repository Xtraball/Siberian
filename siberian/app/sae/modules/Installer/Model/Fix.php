<?php
class Installer_Model_Fix extends Installer_Model_Installer_Module {
    public function fix_modules() {
        $modules = $this->findAll();
        $reinstall_list = array();

        foreach ($modules as $module) {
            $data = $module->getData();
            $name = $data["name"];
            if(!empty($name)) {
                $version = $data["version"];
                $this->prepare($name, true);
                $json_version = $this->_packageInfo["version"];
                if(!version_compare($version, $json_version, "=")) {
                    $reinstall_list[$name] = $this->_packageInfo;
                }
            }
        }

        foreach ($reinstall_list as $name => $infos) {
            $version = $infos["version"];

            # Dependencies injector (mainly for installation purpose)
            if(isset($infos["dependencies"]["modules"])) {
                foreach($infos["dependencies"]["modules"] as $depmodule => $depversion) {
                    if(isset($reinstall_list[$depmodule]["version"])) {
                        $realversion = $reinstall_list[$depmodule]["version"];
                        $install = new self();
                        $install->prepare($depmodule)->setVersion($realversion."-fix")->install();
                        $install->insertData();
                        $install->setVersion($realversion)->save();
                    }
                }
            }

            $install = new self();
            $install->prepare($name)->setVersion($version."-fix")->install();
            $install->insertData();
            $install->setVersion($version)->save();
        }
    }
}
