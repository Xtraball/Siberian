<?php

class Application_Mobile_DataController extends Application_Controller_Mobile_Default {

    public function findallAction() {

        $pages = $this->getApplication()->getOptions();
        $paths = array();
        $assets = array();

        $paths[] = __path("front/mobile/load");
        $paths[] = __path("front/mobile/backgroundimage", array("value_id" => "home"));
        $assets[] = $this->getApplication()->getHomepageBackgroundImageUrl();
        $assets[] = $this->getApplication()->getHomepageBackgroundImageUrl("hd");
        $assets[] = $this->getApplication()->getHomepageBackgroundImageUrl("tablet");

        foreach ($pages as $page) {

            try{
                $model = $page->getModel();
                $object = new $model();

                if (!$page->isActive() OR (!$page->getIsAjax() AND $page->getObject()->getLink())) {
                    continue;
                }

                $paths[] = $page->getPath("front/mobile/backgroundimage", array("value_id" => $page->getId()));
                if($page->hasBackgroundImage() AND $page->getBackgroundImage() != "no-image" AND trim($page->getBackgroundImage()) != "") {
                    $assets[] = $page->getBackgroundImageUrl();
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
                die(var_dump("err", $e)); # Silent not working modules
            }

        }

        $app = $this->getApplication();

        $assets = array_values(array_merge($assets, $app->getAllPictos()));
        $assets[] = Template_Model_Design::getCssPath($app);

        foreach($paths as $key => $path) {
            $path = trim($path);
            if(strlen($path) > 0 && strpos($path, "http") !== 0) {
                $path = $this->getRequest()->getBaseUrl() . $path;
            }
            $paths[$key] = $path;
        }

        foreach($assets as $key => $path) {
            $path = trim($path);
            if(strlen($path) > 0 && strpos($path, "http") !== 0) {
                $path = $this->clean_url($this->getRequest()->getBaseUrl() . $path);
            }
            $assets[$key] = $path;
        }

        sort($paths);
        sort($assets);

        $paths = array_values(array_filter(array_values(array_unique(array_values($paths)))));
        $assets = array_values(array_filter(array_values(array_unique(array_values($assets)))));

        $this->_sendHtml(array(
            "paths" => is_array($paths) ? $paths : array(),
            "assets" => is_array($assets) ? $assets : array()
        ));}


}
