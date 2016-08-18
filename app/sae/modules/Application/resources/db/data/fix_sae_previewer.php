<?php

# TG-509 Temporary fix for SAE
if(Siberian_Version::is("SAE")) {
    $admin = new Admin_Model_Admin();
    $admins = $admin->findAll();

    $application = new Application_Model_Application();
    $application->find(1);

    if($application) {
        foreach($admins as $admin) {
            $application->addAdmin($admin);
        }
    }
}