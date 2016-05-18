<?php

class Application_Mobile_DataController extends Application_Controller_Mobile_Default {

    public function findallAction() {

        $pages = $this->getApplication()->getOptions();
        $paths = array();

        try {
            foreach ($pages as $page) {

                if (!$page->isActive() OR (!$page->getIsAjax() AND $page->getObject()->getLink())) continue;

                $model = $page->getModel();
                $object = new $model();

                if(!$object->isCachable()) continue;

                if(!$object->getTable()) {
                    $feature = $page->getObject();
                    $paths = array_merge($paths, $feature->getFeaturePaths($page));
                } else {
                    $features = $object->findAll(array("value_id" => $page->getId()));

                    foreach ($features as $feature) {
                        $paths = array_merge($paths, $feature->getFeaturePaths($page));
                    }
                }

            }

            $paths = array_merge($paths, $this->getApplication()->getAllPictos());
            $paths = array_unique($paths);

            foreach($paths as $key => $path) {
                if(stripos($path, "http") === false) {
                    $paths[$key] = $this->getRequest()->getBaseUrl() . $path;
                }
            }

        } catch(Exception $e) {
            die();
        }

        $this->_sendHtml($paths);
    }

}
