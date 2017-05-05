<?php

class Analytics_Model_Aggregate_Loyaltycard {

    private $_sqliteAdapter = null;

    private $_appIdByLoyaltyCardId = null;
    private $_loyaltycardLogs = null;
    private $_loyaltyCardNameById = array();

    // START Singleton stuff
    static private $_instance = null;

    private function __construct() {
        $this->_sqliteAdapter = Siberian_Wrapper_Sqlite::getInstance();
        $this->_sqliteAdapter->setDbPath(Core_Model_Directory::getBasePathTo("metrics/siberiancms.db"));
    }

    public static function getInstance() {
        if(!isset(self::$_instance)) {
            self::$_instance = new Analytics_Model_Aggregate_Loyaltycard();
        }
        return self::$_instance;
    }
    // END Singleton stuff

    public function getAdapter() {
        return $this->_sqliteAdapter;
    }

    public function run($timestampRange) {
        try {
            //set all period orders
            // $this->_orders = $this->_getOrders($timestampRange);

            //set a correspondance array for loyaltycardid and appId
            $modelLoyaltycard = new LoyaltyCard_Model_LoyaltyCard();
            $this->_appIdByLoyaltyCardId = $modelLoyaltycard->getAppIdByLoyaltycardId();
            $this->_loyaltycardLogs = $this->_getLoyaltyCardLog($timestampRange);

            $loyaltyCards = $modelLoyaltycard->findAll();
            foreach ($loyaltyCards as $card) {
                $this->_loyaltyCardNameById[$card->getId()] = $card->getName();
            }

            return (
                $this->_aggregateLoyaltycardPointPerCard($timestampRange) &&
                $this->_aggregateLoyaltycardPointAveragePerUser($timestampRange) &&
                $this->_aggregateLoyaltycardPointAverageAllUsers($timestampRange) &&
                $this->_aggregateLoyaltycardRewardUsed($timestampRange) &&
                $this->_aggregateLoyaltycardPointPerCardAndPerUser($timestampRange)
            );
        } catch (Exception $e) {
            if(APPLICATION_ENV === "development") {
                Zend_Debug::dump($e);
            }
            return false;
        }
    }

    private function _getLoyaltyCardLog($aggregationDate) {
        $from = $aggregationDate['from'];
        $to = $aggregationDate['to'];

        //get day information for wanted metrics
        $modelLoyaltycardCustomerLog = new LoyaltyCard_Model_Customer_Log();
        return $modelLoyaltycardCustomerLog->findAll(array(
            'created_at > ?' => date('Y-m-d 00:00:00',$from),
            'created_at < ?' => date('Y-m-d 00:00:00',$to)
        ))->toArray();
    }

    private function _aggregateLoyaltycardPointPerCard($aggregationDate) {
        $timestampGMT = $aggregationDate['dayTimestamp'];
        $from = $aggregationDate['from'];
        $to = $aggregationDate['to'];

        //calcul metrics
        foreach ($this->_loyaltycardLogs as $cardlog) {
            $cardId = $cardlog['card_id'];
            $appId = $this->_appIdByLoyaltyCardId[$cardId]['app_id'];
            if(!array_key_exists($appId, $aggregatedData)) {
                $aggregatedData[$appId] = array();
            }

            if(!array_key_exists($cardId, $aggregatedData[$appId])) {
                $aggregatedData[$appId][$cardId] = 0;
            }
            $aggregatedData[$appId][$cardId] += $cardlog['number_of_points'];
        }

        foreach ($aggregatedData as $appId => $card) {
            foreach ($card as $cardId => $validation) {

                $cardName = $this->_loyaltyCardNameById[$cardId];

                $res_query = $this->_sqliteAdapter->query("SELECT id from loyalty_card_daily
                    WHERE appId = $appId
                    AND cardId = $cardId
                    AND timestampGMT = $timestampGMT");

                //adding new metrics
                if(!is_array($res_query)) {
                    $res_query = $this->_sqliteAdapter->query("INSERT into loyalty_card_daily
                        ('appId', 'validation', 'cardId', 'cardName', 'timestampGMT') VALUES
                        ($appId, $validation, $cardId, '$cardName', $timestampGMT)");
                    if(!$res_query) {
                        throw new Exception("Cannot insert into sqlite");
                    }
                //updating current metric
                } else {
                    $id = $res_query[0][0];
                    $res_query = $this->_sqliteAdapter->query("UPDATE loyalty_card_daily SET validation = $validation WHERE id = $id");
                    if(!$res_query) {
                        throw new Exception("Cannot update into sqlite");
                    }
                }
            }
        }

        return true;
    }

    private function _aggregateLoyaltycardPointAveragePerUser($aggregationDate) {
        $timestampGMT = $aggregationDate['dayTimestamp'];
        $from = $aggregationDate['from'];
        $to = $aggregationDate['to'];

        $aggregatedData = array();
        //calcul metrics
        foreach ($this->_loyaltycardLogs as $cardlog) {
            $cardId = $cardlog['card_id'];
            $customerId = $cardlog['customer_id'];
            $appId = $this->_appIdByLoyaltyCardId[$cardId]['app_id'];
            if(!array_key_exists($appId, $aggregatedData)) {
                $aggregatedData[$appId] = array();
            }

            if(!array_key_exists($cardId, $aggregatedData[$appId])) {
                $aggregatedData[$appId][$cardId] = array();
            }

            if(!array_key_exists('log', $aggregatedData[$appId][$cardId])) {
                $aggregatedData[$appId][$cardId]['log'] = array();
            }

            if(!array_key_exists($customerId, $aggregatedData[$appId][$cardId]['log'])) {
                $aggregatedData[$appId][$cardId]['log'][$customerId] = 0;
            }

            $aggregatedData[$appId][$cardId]['log'][$customerId] += $cardlog['number_of_points'];
        }

        //calcul sum point
        foreach($aggregatedData as $appId => $card) {
            foreach($card as $cardId => $row) {
                $aggregatedData[$appId][$cardId]['averageActifUser'] =
                    round(
                        array_sum($aggregatedData[$appId][$cardId]['log'])/
                        count($aggregatedData[$appId][$cardId]['log'])
                    );
            }
        }

        foreach ($aggregatedData as $appId => $card) {
            foreach ($card as $cardId => $loyaltyCardstats) {

                $cardName = $this->_loyaltyCardNameById[$cardId];

                $averageActifUser = $loyaltyCardstats['averageActifUser'];
                $res_query = $this->_sqliteAdapter->query("SELECT id from loyalty_card_daily
                    WHERE appId = $appId
                    AND cardId = $cardId
                    AND timestampGMT = $timestampGMT");

                //adding new metrics
                if(!is_array($res_query)) {
                    $res_query = $this->_sqliteAdapter->query("INSERT into loyalty_card_daily
                        ('appId', 'cardId', 'cardName', 'averageActifUser', 'timestampGMT') VALUES
                        ($appId, $cardId,'$cardName',  $averageActifUser, $timestampGMT)");
                    if(!$res_query) {
                        throw new Exception("Cannot insert into sqlite");
                    }
                //updating current metric
                } else {
                    $id = $res_query[0][0];
                    $res_query = $this->_sqliteAdapter->query("UPDATE loyalty_card_daily SET averageActifUser = $averageActifUser WHERE id = $id");
                    if(!$res_query) {
                        throw new Exception("Cannot update into sqlite");
                    }
                }
            }
        }
        return true;
    }

    private function _aggregateLoyaltycardPointAverageAllUsers($aggregationDate) {


        $customerModel = new Customer_Model_Customer();
        $appIdPerCustomerId = $customerModel->getAppIdByCustomerId();
        $aggregatedData = array();

        $timestampGMT = $aggregationDate['dayTimestamp'];
        $from = $aggregationDate['from'];
        $to = $aggregationDate['to'];

        $aggregatedData = array();
        //calcul metrics
        foreach ($this->_loyaltycardLogs as $cardlog) {
            $cardId = $cardlog['card_id'];
            $customerId = $cardlog['customer_id'];
            $appId = $this->_appIdByLoyaltyCardId[$cardId]['app_id'];
            if(!array_key_exists($appId, $aggregatedData)) {
                $aggregatedData[$appId] = array();
            }

            if(!array_key_exists($cardId, $aggregatedData[$appId])) {
                $aggregatedData[$appId][$cardId] = array();
            }

            if(!array_key_exists('log', $aggregatedData[$appId][$cardId])) {
                $aggregatedData[$appId][$cardId]['log'] = array();
            }

            if(!array_key_exists($customerId, $aggregatedData[$appId][$cardId]['log'])) {
                $aggregatedData[$appId][$cardId]['log'][$customerId] = 0;
            }

            $aggregatedData[$appId][$cardId]['log'][$customerId] += $cardlog['number_of_points'];
        }


        //add user without points
        foreach($appIdPerCustomerId as $customer) {
            foreach($aggregatedData as $appId => $card) {
                foreach($card as $cardId => $row) {
                    $customerId = $customer['customer_id'];
                    if(!array_key_exists($customerId, $aggregatedData[$appId][$cardId]['log'])) {
                        $aggregatedData[$appId][$cardId]['log'][$customerId] = 0;
                    }
                }
            }
        }

        //calcul sum point
        foreach($aggregatedData as $appId => $card) {
            foreach($card as $cardId => $row) {
                $aggregatedData[$appId][$cardId]['averageAllUser'] =
                    round(
                        array_sum($aggregatedData[$appId][$cardId]['log'])/
                        count($aggregatedData[$appId][$cardId]['log'])
                    );
                }
        }

        foreach ($aggregatedData as $appId => $card) {
            foreach ($card as $cardId => $loyaltyCardstats) {

                $cardName = $this->_loyaltyCardNameById[$cardId];

                $averageAllUser = $loyaltyCardstats['averageAllUser'];
                $res_query = $this->_sqliteAdapter->query("SELECT id from loyalty_card_daily
                    WHERE appId = $appId
                    AND cardId = $cardId
                    AND timestampGMT = $timestampGMT");

                //adding new metrics
                if(!is_array($res_query)) {
                    $res_query = $this->_sqliteAdapter->query("INSERT into loyalty_card_daily
                        ('appId', 'cardId', 'cardName', 'averageAllUser', 'timestampGMT') VALUES
                        ($appId, $cardId,'$cardName',  $averageAllUser, $timestampGMT)");
                    if(!$res_query) {
                        throw new Exception("Cannot insert into sqlite");
                    }
                //updating current metric
                } else {
                    $id = $res_query[0][0];
                    $res_query = $this->_sqliteAdapter->query("UPDATE loyalty_card_daily SET averageAllUser = $averageAllUser WHERE id = $id");
                    if(!$res_query) {
                        throw new Exception("Cannot update into sqlite");
                    }
                }
            }
        }
        return true;
    }

    private function _aggregateLoyaltycardRewardUsed($aggregationDate) {
        $timestampGMT = $aggregationDate['dayTimestamp'];
        $from = $aggregationDate['from'];
        $to = $aggregationDate['to'];

        $modelLoyaltyCustomer = new LoyaltyCard_Model_Customer();
        $modelLoyaltyRewardUsed = $modelLoyaltyCustomer->findAll(array(
            'is_used = ?' => '1',
            'used_at > ?' => date('Y-m-d 00:00:00',$from),
            'used_at < ?' => date('Y-m-d 00:00:00',$to)
        ))->toArray();

        //calcul metrics
        $aggregatedData = array();
        foreach ($modelLoyaltyRewardUsed as $reward) {
            $cardId = $reward['card_id'];
            $appId = $this->_appIdByLoyaltyCardId[$cardId]['app_id'];
            if(!array_key_exists($appId, $aggregatedData)) {
                $aggregatedData[$appId] = array();
            }

            if(!array_key_exists($cardId, $aggregatedData[$appId])) {
                $aggregatedData[$appId][$cardId] = 0;
            }

            $aggregatedData[$appId][$cardId] += 1;
        }

        foreach ($aggregatedData as $appId => $card) {
            foreach ($card as $cardId => $rewardUsed) {

                $cardName = $this->_loyaltyCardNameById[$cardId];

                $res_query = $this->_sqliteAdapter->query("SELECT id from loyalty_card_daily
                    WHERE appId = $appId
                    AND cardId = $cardId
                    AND timestampGMT = $timestampGMT");

                //adding new metrics
                if(!is_array($res_query)) {
                    $res_query = $this->_sqliteAdapter->query("INSERT into loyalty_card_daily
                        ('appId', 'rewardUsed', 'cardId', 'cardName', 'timestampGMT') VALUES
                        ($appId, $rewardUsed, $cardId,'$cardName',  $timestampGMT)");
                    if(!$res_query) {
                        throw new Exception("Cannot insert into sqlite");
                    }
                //updating current metric
                } else {
                    $id = $res_query[0][0];
                    $res_query = $this->_sqliteAdapter->query("UPDATE loyalty_card_daily SET rewardUsed = $rewardUsed WHERE id = $id");
                    if(!$res_query) {
                        throw new Exception("Cannot update into sqlite");
                    }
                }
            }
        }

        return true;
    }

    private function _aggregateLoyaltycardPointPerCardAndPerUser($aggregationDate) {
        $timestampGMT = $aggregationDate['dayTimestamp'];
        $from = $aggregationDate['from'];
        $to = $aggregationDate['to'];

        //calcul metrics
        foreach ($this->_loyaltycardLogs as $cardlog) {
            $cardId = $cardlog['card_id'];
            $customerId = $cardlog['customer_id'];
            $appId = $this->_appIdByLoyaltyCardId[$cardId]['app_id'];
            if(!array_key_exists($appId, $aggregatedData)) {
                $aggregatedData[$appId] = array();
            }

            if(!array_key_exists($cardId, $aggregatedData[$appId])) {
                $aggregatedData[$appId][$cardId] = array();
            }

            if(!array_key_exists($customerId, $aggregatedData[$appId][$cardId])) {
                $aggregatedData[$appId][$cardId][$customerId] = 0;
            }
            $aggregatedData[$appId][$cardId][$customerId] += $cardlog['number_of_points'];
        }

        foreach ($aggregatedData as $appId => $card) {
            foreach ($card as $cardId => $customer) {
                foreach ($customer as $customerId => $validation) {

                    $cardName = $this->_loyaltyCardNameById[$cardId];

                    $res_query = $this->_sqliteAdapter->query("SELECT id from loyalty_card_validation_per_user_daily
                        WHERE appId = $appId
                        AND cardId = $cardId
                        AND customerId = $customerId
                        AND timestampGMT = $timestampGMT");

                    //adding new metrics
                    if(!is_array($res_query)) {
                        $res_query = $this->_sqliteAdapter->query("INSERT into loyalty_card_validation_per_user_daily
                            ('appId', 'validation', 'cardId', 'customerId', 'timestampGMT') VALUES
                            ($appId, $validation, $cardId, '$customerId', $timestampGMT)");
                        if(!$res_query) {
                            throw new Exception("Cannot insert into sqlite");
                        }
                    //updating current metric
                    } else {
                        $id = $res_query[0][0];
                        $res_query = $this->_sqliteAdapter->query("UPDATE loyalty_card_validation_per_user_daily SET validation = $validation WHERE id = $id");
                        if(!$res_query) {
                            throw new Exception("Cannot update into sqlite");
                        }
                    }
                }
            }
        }

        return true;
    }
}