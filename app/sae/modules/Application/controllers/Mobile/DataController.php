<?php

class Application_Mobile_DataController extends Application_Controller_Mobile_Default {

    public function findallAction() {

        $pages = $this->getApplication()->getOptions();
        $paths = array();

        foreach ($pages as $page) {

            try{
                $model = $page->getModel();
                $object = new $model();

                if (!$page->isActive() OR (!$page->getIsAjax() AND $page->getObject()->getLink())) {
                    continue;
                }

                if(!$object->isCachable()) {
                    continue;
                }

                if(!$object->getTable()) {
                    $feature = $page->getObject();
                    $paths = array_merge($paths, $feature->getFeaturePaths($page));
                } else {
                    $features = $object->findAll(array("value_id" => $page->getId()));

                    foreach ($features as $feature) {
                        $paths = array_merge($paths, $feature->getFeaturePaths($page));
                    }
                }
            } catch(Exception $e) {
                # Silent not working modules
            }

        }

        $paths = array_merge($paths, $this->getApplication()->getAllPictos());
        $paths = array_unique($paths);

        foreach($paths as $key => $path) {
            if(stripos($path, "http") === false) {
                $paths[$key] = $this->getRequest()->getBaseUrl() . $path;
            }
        }

        $this->_sendHtml($paths);
    }

}
