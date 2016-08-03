<?php

class Application_Model_Tools {

    /**
     * Android SDK
     * Test if the SDK is correctly installed
     *
     * @return bool TRUE if all the folders are present
     */
    public static function isAndroidSDKInstalled() {
        return true;
        $sdk_path = Core_Model_Directory::getBasePathTo("/var/apps/ionic/tools/android-sdk");

        $required_folders = array(
            $sdk_path . "/build-tools/23.0.2",
            $sdk_path . "/platforms/android-22",
            $sdk_path . "/extras/android/support",
            $sdk_path . "/extras/android/m2repository",
            $sdk_path . "/extras/google/m2repository",
            $sdk_path . "/extras/google/google_play_services",
        );
        foreach($required_folders as $folder) {
            if(!file_exists($folder)) {
                return false;
            }
        }
        return true;
    }

}