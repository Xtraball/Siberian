<?php

class Application_Mobile_DataController extends Application_Controller_Mobile_Default {

    public function findallAction() {

        $application = $this->getApplication();
        $pages = $application->getOptions();

        $request = $this->getRequest();
        $base_url = $request->getBaseUrl();

        $paths = array();
        $assets = array();

        $paths[] = __path("front/mobile/loadv3");
        $paths[] = __path("front/mobile/touched");
        $paths[] = __path("front/mobile/backgroundimages", array(
            'device_width' => $request->getParam("device_width"),
            'device_height' => $request->getParam("device_height"),
        ));
        $assets[] = $this->getApplication()->getHomepageBackgroundImageUrl();
        $assets[] = $this->getApplication()->getHomepageBackgroundImageUrl("hd");
        $assets[] = $this->getApplication()->getHomepageBackgroundImageUrl("tablet");

        foreach ($pages as $page) {

            try{
                $model = $page->getModel();
                if(class_exists($model)) {
                    $object = new $model();
                } else {
                    throw new Siberian_Exception(__("Application_Mobile_DataController::findall, class: {$model} doesn't exists."));
                }


                if (!$page->isActive() OR (!$page->getIsAjax() AND $page->getObject()->getLink())) {
                    continue;
                }

                if(!$object->getTable() || is_a($object, "Push_Model_Message")) {
                    $feature = $page->getObject();

                    if(!$feature->isCacheable()) continue;
                    
                    $fpaths = $feature->getFeaturePaths($page);
                    if(is_array($fpaths)) $paths = array_merge($paths, $fpaths);

                    $fassets = $feature->getAssetsPaths($page);
                    if(is_array($fassets)) $assets = array_merge($assets, $fassets);
                } else {
                    $features = $object->findAll(array("value_id" => $page->getId()));

                    foreach ($features as $feature) {
                        if(!$feature->isCacheable()) continue;

                        $fpaths = $feature->getFeaturePaths($page);
                        if(is_array($fpaths)) $paths = array_merge($paths, $fpaths);

                        $fassets = $feature->getAssetsPaths($page);
                        if(is_array($fassets)) $assets = array_merge($assets, $fassets);
                    }
                }
            } catch(Exception $e) {
                # Catch not working modules silently.
            }

        }

        foreach($paths as $key => $path) {
            $path = trim($path);
            if(strlen($path) > 0 && strpos($path, "http") !== 0) {
                $path = $base_url . $path;
            }
            $paths[$key] = $path;
        }

        foreach($assets as $key => $path) {
            $path = trim($path);
            if(strlen($path) > 0 && strpos($path, "http") !== 0) {
                $path = $this->clean_url($base_url . $path);
            }
            $assets[$key] = $path;
        }

        sort($paths);
        sort($assets);

        $paths = array_values(array_filter(array_values(array_unique(array_values($paths)))));
        $assets = array_values(array_filter(array_values(array_unique(array_values($assets)))));

        $this->_sendJson(array(
            "paths" => is_array($paths) ? $paths : array(),
            "assets" => is_array($assets) ? $assets : array()
        ));
    }


}
