<?php
//------------------------------------------------------------------
//
// /!\ THIS CONTROLLER IS HERE FOR TEST AND DEV PURPOSE ONLY /!\
//
// TO BE DELETED
//
//------------------------------------------------------------------
class Analytics_TestController extends Application_Controller_Default {

    public function zobAction() {

        $this->_sqliteAdapter = Siberian_Wrapper_Sqlite::getInstance();
        $this->_sqliteAdapter->setDbPath(Core_Model_Directory::getBasePathTo("metrics/siberiancms.db"));

        $this->_sqliteAdapter->query(
            "INSERT INTO app_installation_daily ('appId','ios_install','android_install','timestampGMT') VALUES ('11','2','5',".time().")"
        );
        $this->_sqliteAdapter->query(
            "INSERT INTO app_installation_daily ('appId','ios_install','android_install','timestampGMT') VALUES ('2','20','5',".time().")"
        );
        $this->_sqliteAdapter->query(
            "INSERT INTO app_installation_daily ('appId','ios_install','android_install','timestampGMT') VALUES ('11','20','15',".time().")"
        );
        $this->_sqliteAdapter->query(
            "INSERT INTO app_installation_daily ('appId','ios_install','android_install','timestampGMT') VALUES ('8','35','2',".time().")"
        );
        $this->_sqliteAdapter->query(
            "INSERT INTO app_installation_daily ('appId','ios_install','android_install','timestampGMT') VALUES ('9','2','5',".time().")"
        );
        $this->_sqliteAdapter->query(
            "INSERT INTO app_installation_daily ('appId','ios_install','android_install','timestampGMT') VALUES ('10','2','51',".time().")"
        );
        $this->_sqliteAdapter->query(
            "INSERT INTO app_loaded_daily ('appId','visits','time_spend','timestampGMT') VALUES ('10','211','51',".(time()-1).")"
        );
        $this->_sqliteAdapter->query(
            "INSERT INTO app_loaded_daily ('appId','visits','time_spend','timestampGMT') VALUES ('8','150','16',".(time()-2).")"
        );
        $this->_sqliteAdapter->query(
            "INSERT INTO app_loaded_daily ('appId','visits','time_spend','timestampGMT') VALUES ('10','125','41',".(time()-3).")"
        );
        $this->_sqliteAdapter->query(
            "INSERT INTO app_loaded_daily ('appId','visits','time_spend','timestampGMT') VALUES ('11','254','781',".(time()-4).")"
        );
        $this->_sqliteAdapter->query(
            "INSERT INTO app_loaded_daily ('appId','visits','time_spend','timestampGMT') VALUES ('9','562','58',".(time()-5).")"
        );


        die;
    }

    public function zob2Action() {

        $this->_sqliteAdapter = Siberian_Wrapper_Sqlite::getInstance();
        $this->_sqliteAdapter->setDbPath(Core_Model_Directory::getBasePathTo("metrics/siberiancms.db"));

        $this->_sqliteAdapter->query(
            "INSERT INTO app_navigation_daily ('appId','visits','feature_id','timestampGMT') VALUES ('11','2','208',".time().")"
        );
        $this->_sqliteAdapter->query(
            "INSERT INTO app_navigation_daily ('appId','visits','feature_id','timestampGMT') VALUES ('11','21','209',".time().")"
        );
        $this->_sqliteAdapter->query(
            "INSERT INTO app_navigation_daily ('appId','visits','feature_id','timestampGMT') VALUES ('11','12','210',".time().")"
        );
        $this->_sqliteAdapter->query(
            "INSERT INTO app_navigation_daily ('appId','visits','feature_id','timestampGMT') VALUES ('11','41','211',".time().")"
        );
        $this->_sqliteAdapter->query(
            "INSERT INTO app_navigation_daily ('appId','visits','feature_id','timestampGMT') VALUES ('11','13','214',".time().")"
        );

        $this->_sqliteAdapter->query(
            "INSERT INTO app_navigation_daily ('appId','visits','feature_id','timestampGMT') VALUES ('11','2','208',1466266979)"
        );
        $this->_sqliteAdapter->query(
            "INSERT INTO app_navigation_daily ('appId','visits','feature_id','timestampGMT') VALUES ('11','21','209',1466266979)"
        );
        $this->_sqliteAdapter->query(
            "INSERT INTO app_navigation_daily ('appId','visits','feature_id','timestampGMT') VALUES ('11','12','210',1466266979)"
        );
        $this->_sqliteAdapter->query(
            "INSERT INTO app_navigation_daily ('appId','visits','feature_id','timestampGMT') VALUES ('11','41','211',1466266979)"
        );
        $this->_sqliteAdapter->query(
            "INSERT INTO app_navigation_daily ('appId','visits','feature_id','timestampGMT') VALUES ('11','13','214',".time().")"
        );

        die;
    }

    public function zobiAction() {
        $this->_sqliteAdapter = Siberian_Wrapper_Sqlite::getInstance();
        $this->_sqliteAdapter->setDbPath(Core_Model_Directory::getBasePathTo("metrics/siberiancms.db"));

        $res_query = $this->_sqliteAdapter->query(
            "DELETE FROM app_loaded_daily"
        );
        Zend_Debug::dump($res_query);
        die(__METHOD__ . " L:" . __LINE__);
    }

    public function createAction() {
        $this->_sqliteAdapter = Siberian_Wrapper_Sqlite::getInstance();
        $this->_sqliteAdapter->setDbPath(Core_Model_Directory::getBasePathTo("metrics/siberiancms.db"));

        $res_query = $this->_sqliteAdapter->query("DROP TABLE mcommerce_product_visit_daily");
        $res_query = $this->_sqliteAdapter->query(
            "CREATE TABLE mcommerce_product_visit_daily (
                id    INTEGER PRIMARY KEY AUTOINCREMENT,
                appId INTEGER NOT NULL,
                productId INTEGER NOT NULL,
                productName   TEXT NOT NULL,
                timestampGMT  INTEGER NOT NULL,
                visits    INTEGER NOT NULL
            );"
        );
        Zend_Debug::dump($res_query);
        die(__METHOD__ . " L:" . __LINE__);
    }

    public function xyzAction(){
        $this->_sqliteAdapter = Siberian_Wrapper_Sqlite::getInstance();
        $this->_sqliteAdapter->setDbPath(Core_Model_Directory::getBasePathTo("metrics/siberiancms.db"));
//        $this->_sqliteAdapter->query(
//            "INSERT INTO app_localization_daily ('appId','latitude','longitude','timestampGMT') VALUES ('11',43.686233,1.388225,1466266979)"
//        );
//        $this->_sqliteAdapter->query(
//            "INSERT INTO app_localization_daily ('appId','latitude','longitude','timestampGMT') VALUES ('11',43.688715,1.362801,1466266979)"
//        );
//        $this->_sqliteAdapter->query(
//            "INSERT INTO app_localization_daily ('appId','latitude','longitude','timestampGMT') VALUES ('11',43.705687,1.374131,1466266979)"
//        );
//        $this->_sqliteAdapter->query(
//            "INSERT INTO app_localization_daily ('appId','latitude','longitude','timestampGMT') VALUES ('11',43.760407,1.379565,".time().")"
//        );
//        $this->_sqliteAdapter->query(
//            "INSERT INTO app_localization_daily ('appId','latitude','longitude','timestampGMT') VALUES ('11',43.762962,1.347911,".time().")"
//        );
//        $this->_sqliteAdapter->query(
//            "INSERT INTO app_localization_daily ('appId','latitude','longitude','timestampGMT') VALUES ('11',43.973894,2.224415,1466266979)"
//        );
//        $this->_sqliteAdapter->query(
//            "INSERT INTO app_localization_daily ('appId','latitude','longitude','timestampGMT') VALUES ('11',45.039591,29.417633,".time().")"
//        );

        $this->_sqliteAdapter->query(
            "INSERT INTO app_loaded_daily (appId,visits,time_spend,timestampGMT) VALUES (11,1,0,1466589600);"
        );
        $this->_sqliteAdapter->query(
            "INSERT INTO app_loaded_daily (appId,visits,time_spend,timestampGMT) VALUES (11,0,1,1466589600);"
        );
        $this->_sqliteAdapter->query(
            "INSERT INTO app_loaded_daily (appId,visits,time_spend,timestampGMT) VALUES (11,0,2,1466589600);"
        );
        $this->_sqliteAdapter->query(
            "INSERT INTO app_loaded_daily (appId,visits,time_spend,timestampGMT) VALUES (11,0,3,1466589600);"
        );
        $this->_sqliteAdapter->query(
            "INSERT INTO app_loaded_daily (appId,visits,time_spend,timestampGMT) VALUES (11,0,4,1466589600);"
        );
        $this->_sqliteAdapter->query(
            "INSERT INTO app_loaded_daily (appId,visits,time_spend,timestampGMT) VALUES (11,9,5,1466589600);"
        );
        $this->_sqliteAdapter->query(
            "INSERT INTO app_loaded_daily (appId,visits,time_spend,timestampGMT) VALUES (4,0,0,1466589600);"
        );
        $this->_sqliteAdapter->query(
            "INSERT INTO app_loaded_daily (appId,visits,time_spend,timestampGMT) VALUES (4,0,1,1466589600);"
        );
        $this->_sqliteAdapter->query(
            "INSERT INTO app_loaded_daily (appId,visits,time_spend,timestampGMT) VALUES (4,0,2,1466589600);"
        );
        $this->_sqliteAdapter->query(
            "INSERT INTO app_loaded_daily (appId,visits,time_spend,timestampGMT) VALUES (4,0,3,1466589600);"
        );
        $this->_sqliteAdapter->query(
            "INSERT INTO app_loaded_daily (appId,visits,time_spend,timestampGMT) VALUES (4,0,4,1466589600);"
        );
        $this->_sqliteAdapter->query(
            "INSERT INTO app_loaded_daily (appId,visits,time_spend,timestampGMT) VALUES (4,15,5,1466589600);"
        );


        $this->_sqliteAdapter->query(
            "INSERT INTO app_loaded_daily (appId,visits,time_spend,timestampGMT) VALUES (11,1,0,1466266979);"
        );
        $this->_sqliteAdapter->query(
            "INSERT INTO app_loaded_daily (appId,visits,time_spend,timestampGMT) VALUES (11,0,1,1466266979);"
        );
        $this->_sqliteAdapter->query(
            "INSERT INTO app_loaded_daily (appId,visits,time_spend,timestampGMT) VALUES (11,0,2,1466266979);"
        );
        $this->_sqliteAdapter->query(
            "INSERT INTO app_loaded_daily (appId,visits,time_spend,timestampGMT) VALUES (11,0,3,1466266979);"
        );
        $this->_sqliteAdapter->query(
            "INSERT INTO app_loaded_daily (appId,visits,time_spend,timestampGMT) VALUES (11,0,4,1466266979);"
        );
        $this->_sqliteAdapter->query(
            "INSERT INTO app_loaded_daily (appId,visits,time_spend,timestampGMT) VALUES (11,9,5,1466266979);"
        );
        $this->_sqliteAdapter->query(
            "INSERT INTO app_loaded_daily (appId,visits,time_spend,timestampGMT) VALUES (4,0,0,1466266979);"
        );
        $this->_sqliteAdapter->query(
            "INSERT INTO app_loaded_daily (appId,visits,time_spend,timestampGMT) VALUES (4,0,1,1466266979);"
        );
        $this->_sqliteAdapter->query(
            "INSERT INTO app_loaded_daily (appId,visits,time_spend,timestampGMT) VALUES (4,0,2,1466266979);"
        );
        $this->_sqliteAdapter->query(
            "INSERT INTO app_loaded_daily (appId,visits,time_spend,timestampGMT) VALUES (4,0,3,1466266979);"
        );
        $this->_sqliteAdapter->query(
            "INSERT INTO app_loaded_daily (appId,visits,time_spend,timestampGMT) VALUES (4,0,4,1466589600);"
        );
        $this->_sqliteAdapter->query(
            "INSERT INTO app_loaded_daily (appId,visits,time_spend,timestampGMT) VALUES (4,15,5,1466589600);"
        );

        die(__METHOD__ . " L:" . __LINE__);
    }

    public function abcAction(){
        $sqlite_request = Analytics_Model_Analytics::getInstance();
        $app_installed = $sqlite_request->getLoadedApp();
        die(__METHOD__ . " L:" . __LINE__);
    }

    private function _getWhereForMetrics($data) {
        $where = array();

        if($ids = $data["app_ids"]) {
            if(is_array(Zend_Json::decode($ids))) {
                $where["appId IN (?)"] = implode(",", Zend_Json::decode($ids));
            } else {
                $where["appId = ?"] = $ids;
            }
        }

        if($date_range = $data["date_range"]) {
            $start = Zend_Json::decode($date_range["start"]);
            $end = Zend_Json::decode($date_range["end"]);
            $end = strtotime("+1 day", $end);
            $where["timestampGMT >= ?"] = $start;
            $where["timestampGMT <= ?"] = $end;
        }

        return $where;
    }

}