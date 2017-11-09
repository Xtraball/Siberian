<?php

class Analytics_Model_Aggregate {

    private $_sqliteAdapter = null;

    // START Singleton stuff
    static private $_instance = null;

    private function __construct() {
        $this->_sqliteAdapter = Siberian_Wrapper_Sqlite::getInstance();
        $this->_sqliteAdapter->setDbPath(Core_Model_Directory::getBasePathTo("metrics/siberiancms.db"));
    }

    public static function getInstance() {
        if(!isset(self::$_instance)) {
            self::$_instance = new Analytics_Model_Aggregate();
        }
        return self::$_instance;
    }
    // END Singleton stuff

    public function getAdapter() {
        return $this->_sqliteAdapter;
    }

    public function run($aggregationDate) {
        $dateFrom = date("Y-m-d",$aggregationDate);
        $timestampFrom = strtotime($dateFrom);

        $dateTo = date("Y-m-d",$aggregationDate + (60 * 60 * 24));
        $timestampTo = strtotime($dateTo);

        $timestampStore = strtotime($dateFrom.' 12:00:00');

        $timestampRange = array(
            'from' => $timestampFrom,
            'to' => $timestampTo,
            'dayTimestamp' => $timestampStore
        );

        try {
            $mcommerceAnalytics = Analytics_Model_Aggregate_Mcommerce::getInstance();
            $discountAnalytics = Analytics_Model_Aggregate_Discount::getInstance();
            $loyalityCardAnalytics = Analytics_Model_Aggregate_Loyaltycard::getInstance();
            $result =
                $this->_aggregateInstallation($timestampRange) &&
                $this->_aggregateLoaded($timestampRange) &&
                $this->_aggregateNavigation($timestampRange) &&
                $this->_aggregateLocalization($timestampRange) &&
                $mcommerceAnalytics->run($timestampRange) &&
                $discountAnalytics->run($timestampRange) &&
                $loyalityCardAnalytics->run($timestampRange);
        } catch (Exception $e) {
            if(APPLICATION_ENV === "development") {
                Zend_Debug::dump($e);
            }
            return false;
        }

        if($result) {
            echo "OK";
        } else {
            echo "NOK";
        }
    }

    private function _aggregateInstallation($aggregationDate) {
        $from = $aggregationDate['from'];
        $to = $aggregationDate['to'];
        $timestampGMT = $aggregationDate['dayTimestamp'];

        //get day information for wanted metrics
        $res_query = $this->_sqliteAdapter->query("SELECT appId, OS
            FROM app_installation
            WHERE timestampGMT > $from
            AND timestampGMT < $to
        ");
        $aggregatedData = array();
        if(is_array($res_query)) {
            //calcul metrics
            foreach ($res_query as $row) {
                if(count($row) !== 2) continue;
                if(!array_key_exists((string)$row[0], $aggregatedData)) {
                    $aggregatedData[(string)$row[0]] = array(
                        "ios_install" => 0,
                        "android_install" => 0
                    );
                }
                if($row[1] === 'iOS') {
                    $aggregatedData[(string)$row[0]]['ios_install'] += 1;
                }
                if($row[1] === 'Android') {
                    $aggregatedData[(string)$row[0]]['android_install']+= 1;
                }
            }
        }

        //store new metrics
        foreach ($aggregatedData as $appId => $row) {
            $ios_install = $row['ios_install'];
            $android_install = $row['android_install'];

            $res_query = $this->_sqliteAdapter->query("SELECT id from app_installation_daily
                WHERE appId = $appId
                AND timestampGMT = $timestampGMT");

            //adding new metrics
            if(!is_array($res_query)) {
                $res_query = $this->_sqliteAdapter->query("INSERT into app_installation_daily
                    ('appId', 'ios_install', 'android_install', 'timestampGMT') VALUES
                    ($appId,$ios_install,$android_install,$timestampGMT)");

                if(!$res_query) {
                    throw new Exception("Cannot insert into sqlite");
                }
            //updating current metric
            } else {
                $id = $res_query[0][0];
                $res_query = $this->_sqliteAdapter->query("UPDATE app_installation_daily
                    SET ios_install = $ios_install, android_install = $android_install WHERE id = $id");
                if(!$res_query) {
                    throw new Exception("Cannot update into sqlite");
                }
            }
        }

        return true;
    }

    private function _aggregateLoaded($aggregationDate) {
        $from = $aggregationDate['from'];
        $to = $aggregationDate['to'];
        $timestampGMT = $aggregationDate['dayTimestamp'];

        //get day information for wanted metrics
        $res_query = $this->_sqliteAdapter->query("SELECT appId, startTimestampGMT, endTimestampGMT
            FROM app_loaded
            WHERE startTimestampGMT > $from
            AND startTimestampGMT < $to
        ");
        $aggregatedData = array();
        if(is_array($res_query)) {
            //calcul metrics
            foreach ($res_query as $row) {
                if(count($row) !== 3) continue;
                $timeSpent = $row[2] - $row[1];
                $rangeCategory = -1;
                switch (true) {
                    case $timeSpent < 20:
                        $rangeCategory = Analytics_Model_Analytics::TIME_RANGE_0_20;
                        break;
                    case $timeSpent < 40:
                        $rangeCategory = Analytics_Model_Analytics::TIME_RANGE_20_40;
                        break;
                    case $timeSpent < 60:
                        $rangeCategory = Analytics_Model_Analytics::TIME_RANGE_40_60;
                        break;
                    case $timeSpent < 120:
                        $rangeCategory = Analytics_Model_Analytics::TIME_RANGE_1MIN_2MIN;
                        break;
                    case $timeSpent < 300:
                        $rangeCategory = Analytics_Model_Analytics::TIME_RANGE_2MIN_5MIN;
                        break;
                    default:
                        $rangeCategory = Analytics_Model_Analytics::TIME_RANGE_5MIN_PLUS;
                }
                if(!array_key_exists($row[0], $aggregatedData)) {
                    $aggregatedData[$row[0]] = array();
                    $aggregatedData[$row[0]][Analytics_Model_Analytics::TIME_RANGE_0_20] = 0;
                    $aggregatedData[$row[0]][Analytics_Model_Analytics::TIME_RANGE_20_40] = 0;
                    $aggregatedData[$row[0]][Analytics_Model_Analytics::TIME_RANGE_40_60] = 0;
                    $aggregatedData[$row[0]][Analytics_Model_Analytics::TIME_RANGE_1MIN_2MIN] = 0;
                    $aggregatedData[$row[0]][Analytics_Model_Analytics::TIME_RANGE_2MIN_5MIN] = 0;
                    $aggregatedData[$row[0]][Analytics_Model_Analytics::TIME_RANGE_5MIN_PLUS] = 0;
                }
                $aggregatedData[$row[0]][$rangeCategory] += 1;
            }
        }

        //store new metrics
        foreach ($aggregatedData as $appId => $visitPerRange) {
            foreach ($visitPerRange as $range => $visits) {

                $res_query = $this->_sqliteAdapter->query("SELECT id from app_loaded_daily
                    WHERE appId = $appId
                    AND time_spend = $range
                    AND timestampGMT = $timestampGMT");

                //adding new metrics
                if(!is_array($res_query)) {
                    $res_query = $this->_sqliteAdapter->query("INSERT into app_loaded_daily
                        ('appId', 'visits', 'time_spend', 'timestampGMT') VALUES
                        ($appId, $visits, $range, $timestampGMT)");
                    if(!$res_query) {
                        throw new Exception("Cannot insert into sqlite");
                    }
                //updating current metric
                } else {
                    $id = $res_query[0][0];
                    $res_query = $this->_sqliteAdapter->query("UPDATE app_loaded_daily SET visits = $visits WHERE id = $id");
                    if(!$res_query) {
                        throw new Exception("Cannot update into sqlite");
                    }
                }
            }
        }
        return true;
    }

    private function _aggregateNavigation($aggregationDate) {
        $from = $aggregationDate['from'];
        $to = $aggregationDate['to'];
        $timestampGMT = $aggregationDate['dayTimestamp'];

        //get feature info
        $modelOption = new Application_Model_Option_Value();
        $allFeatures = array();
        foreach ($modelOption->findAll() as $feature) {
            // print_r($feature);
            $allFeatures[$feature->getId()] = array(
                "appId" => $feature->getAppId(),
                "featureName" => $feature->getTabbarName(),
            );
        }

        //get day information for wanted metrics
        $res_query = $this->_sqliteAdapter->query("SELECT featureId
            FROM page_navigation
            WHERE timestampGMT > $from
            AND timestampGMT < $to
        ");
        $aggregatedData = array();
        if(is_array($res_query)) {
            //calcul metrics
            foreach ($res_query as $row) {
                if(count($row) !== 1) continue;
                $featureId = $row[0];
                $appId = $allFeatures[$featureId]['appId'];
                //default val for app
                if(!array_key_exists($appId, $aggregatedData)) {
                    $aggregatedData[$appId] = array();
                }
                if(!array_key_exists($featureId, $aggregatedData[$appId])) {
                    $aggregatedData[$appId][$featureId] = 0;
                }
                $aggregatedData[$appId][$featureId]+= 1;
            }
        }

        //store new metrics
        foreach ($aggregatedData as $appId => $featureData) {
            if(!is_numeric($appId)) continue;
            foreach ($featureData as $featureId => $visits) {
                $res_query = $this->_sqliteAdapter->query("SELECT id from app_navigation_daily
                    WHERE appId = $appId
                    AND feature_id = $featureId
                    AND timestampGMT=  $timestampGMT");

                $featureName = $allFeatures[$featureId]['featureName'];

                //adding new metrics
                if(!is_array($res_query)) {
                    $res_query = $this->_sqliteAdapter->query("INSERT into app_navigation_daily
                        ('appId', 'feature_id', 'featureName', 'timestampGMT', 'visits') VALUES
                        ($appId,$featureId,'$featureName',$timestampGMT,$visits)");

                    if(!$res_query) {
                        throw new Exception("Cannot insert into sqlite");
                    }
                //updating current metric
                } else {
                    $id = $res_query[0][0];
                    $res_query = $this->_sqliteAdapter->query("UPDATE app_navigation_daily
                        SET visits = $visits WHERE id = $id");
                    if(!$res_query) {
                        throw new Exception("Cannot update into sqlite");
                    }
                }
            }
        }
        return true;
    }

    private function _aggregateLocalization($aggregationDate) {
        $from = $aggregationDate['from'];
        $to = $aggregationDate['to'];
        $timestampGMT = $aggregationDate['dayTimestamp'];

        //get day information for wanted metrics
        $res_query = $this->_sqliteAdapter->query("SELECT appId, latitude, longitude
            FROM app_installation
            WHERE timestampGMT > $from
            AND timestampGMT < $to
        ");

        $aggregatedData = array();
        if(is_array($res_query)) {
            //calcul metrics
            foreach ($res_query as $row) {
                if(count($row) !== 3) continue;
                $appId = $row[0];
                $lat = $row[1];
                $lon = $row[2];
                if(!array_key_exists($appId, $aggregatedData)) {
                    $aggregatedData[$appId] = array();
                }
                $aggregatedData[$appId][] = array(
                    "latitude" => $lat,
                    "longitude" => $lon
                );
            }
        }

        //store new metrics
        $res_query = $this->_sqliteAdapter->query("DELETE from app_localization_daily WHERE timestampGMT = $timestampGMT");
        if($res_query) {
            foreach ($aggregatedData as $appId => $positions) {
                foreach ($positions as $position) {
                    $latitude = $position['latitude'];
                    $longitude = $position['longitude'];

                    //adding new metrics
                    $res_query = $this->_sqliteAdapter->query("INSERT into app_localization_daily
                        ('appId', 'latitude', 'longitude', 'timestampGMT') VALUES
                        ($appId,'$latitude','$longitude',$timestampGMT)");

                    if(!$res_query) {
                        throw new Exception("Cannot insert into sqlite");
                    }
                }
            }
        } else {
            throw new Exception("Cannot delete rows in sqlite analytics db.");
        }

        return true;
    }
}
