<?php

class Analytics_Model_Aggregate_Discount {

    private $_sqliteAdapter = null;

    // START Singleton stuff
    static private $_instance = null;

    private function __construct() {
        $this->_sqliteAdapter = Siberian_Wrapper_Sqlite::getInstance();
        $this->_sqliteAdapter->setDbPath(Core_Model_Directory::getBasePathTo("metrics/siberiancms.db"));
    }

    public static function getInstance() {
        if(!isset(self::$_instance)) {
            self::$_instance = new Analytics_Model_Aggregate_Discount();
        }
        return self::$_instance;
    }
    // END Singleton stuff

    public function run($timestampRange) {
        try {
            return (
                $this->_aggregateValidationPerDiscount($timestampRange)
            );
        } catch (Exception $e) {
            if(APPLICATION_ENV === "development") {
                Zend_Debug::dump($e);
            }
            return false;
        }
    }

    private function _aggregateValidationPerDiscount($aggregationDate) {
        $timestampGMT = $aggregationDate['dayTimestamp'];
        $from = $aggregationDate['from'];
        $to = $aggregationDate['to'];

        //get appId by discount
        $modelPromotion = new Promotion_Model_Promotion();
        $promotions = $modelPromotion->findAll();
        $promotionTitleById = array();
        foreach ($promotions as $promotion) {
            $promotionTitleById[$promotion->getId()] = $promotion->getTitle();
        }
        $appIdByPromotionId = $modelPromotion->getAppIdByPromotionId();

        //get day information for wanted metrics
        $modelPromotionUsed = new Promotion_Model_Customer();
        $discountValidated = $modelPromotionUsed->findAll(array(
            'created_at > ?' => date('Y-m-d 00:00:00',$from),
            'created_at < ?' => date('Y-m-d 00:00:00',$to)
        ))->toArray();

        $aggregatedData = array();
        //calcul metrics
        foreach ($discountValidated as $row) {
            $promotionId = $row['promotion_id'];
            $appId = $appIdByPromotionId[$promotionId]['app_id'];
            if(!array_key_exists($appId, $aggregatedData)) {
                $aggregatedData[$appId] = array();
            }
            if(!array_key_exists($promotionId, $aggregatedData[$appId])) {
                $aggregatedData[$appId][$promotionId] = 0;
            }
            $aggregatedData[$appId][$promotionId] += 1;
        }

        foreach ($aggregatedData as $appId => $promotion) {
            foreach ($promotion as $promotionId => $total) {
                $promotionName = $promotionTitleById[$promotionId];
                $res_query = $this->_sqliteAdapter->query("SELECT id from discount_count_validation_daily
                    WHERE appId = $appId
                    AND promotionId = $promotionId
                    AND timestampGMT = $timestampGMT");

                //adding new metrics
                if(!is_array($res_query)) {
                    $res_query = $this->_sqliteAdapter->query("INSERT into discount_count_validation_daily
                        ('appId', 'promotionId', 'promotionName', 'total', 'timestampGMT') VALUES
                        ($appId, $promotionId, '$promotionName', $total, $timestampGMT)");
                    if(!$res_query) {
                        throw new Exception("Cannot insert into sqlite");
                    }
                //updating current metric
                } else {
                    $id = $res_query[0][0];
                    $res_query = $this->_sqliteAdapter->query("UPDATE discount_count_validation_daily SET total = $total WHERE id = $id");
                    if(!$res_query) {
                        throw new Exception("Cannot update into sqlite");
                    }
                }
            }
        }
        return true;
    }
}