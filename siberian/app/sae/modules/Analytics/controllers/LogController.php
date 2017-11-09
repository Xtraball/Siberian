<?php

class Analytics_LogController extends Application_Controller_Default {

    public function getinstalledAction() {
        $where = array(
//            "timestampGMT >= ?" => time(),
            "timestampGMT <= ?" => time()
        );

        $sqlite_request = Analytics_Model_Store::getInstance();
        $app_installed = $sqlite_request->getInstalledApp($where);

//        $sqlite_request->getAdapter()->query("DELETE FROM app_installation;");
        Zend_Debug::dump($app_installed);
        die;
    }

    public function getopenedAction() {
        $sqlite_request = Analytics_Model_Store::getInstance();
        $app_opened= $sqlite_request->getAppLoaded();

//        $sqlite_request->getAdapter()->query("DELETE FROM app_loaded;");
        Zend_Debug::dump($app_opened);
        die;
    }

    public function getpageopenedAction() {
        $sqlite_request = Analytics_Model_Store::getInstance();
        $page_opened = $sqlite_request->getAppPageNavigation();

//        $sqlite_request->getAdapter()->query("DELETE FROM page_navigation;");
        Zend_Debug::dump($page_opened);
        die;
    }

    public function getproductopenedAction() {
        $sqlite_request = Analytics_Model_Store::getInstance();
        $page_opened = $sqlite_request->getAppMcommerceProductNavigation();

//        $sqlite_request->getAdapter()->query("DELETE FROM mcommerce_product_navigation;");
        Zend_Debug::dump($page_opened);
        die;
    }

    public function getproductsoldAction() {
        $sqlite_request = Analytics_Model_Store::getInstance();
        $page_opened = $sqlite_request->getAppMcommerceProductSold();

//        $sqlite_request->getAdapter()->query("DELETE FROM mcommerce_product_sold;");
        Zend_Debug::dump($page_opened);
        die;
    }

}