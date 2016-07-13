<?php

class Analytics_Model_Analytics {

    const TIME_RANGE_0_20 = 0;
    const TIME_RANGE_20_40 = 1;
    const TIME_RANGE_40_60 = 2;
    const TIME_RANGE_1MIN_2MIN = 3;
    const TIME_RANGE_2MIN_5MIN = 4;
    const TIME_RANGE_5MIN_PLUS = 5;

    // START Singleton stuff
    static private $_instance = null;

    private $_sqliteAdapter = null;
    private $_default_period = null;
    private $_time_range_labels = array();

    private function __construct() {
        $this->_sqliteAdapter = Siberian_Wrapper_Sqlite::getInstance();
        $this->_sqliteAdapter->setDbPath(Core_Model_Directory::getBasePathTo("metrics/siberiancms.db"));
        $dateFrom = date("Y-m-d",time());
        $this->_default_period = strtotime($dateFrom);

        $this->_time_range_labels = array(
            self::TIME_RANGE_0_20 => "0-20s",
            self::TIME_RANGE_20_40 => "20-40s",
            self::TIME_RANGE_40_60 => "40-60s",
            self::TIME_RANGE_1MIN_2MIN => "1-2 min.",
            self::TIME_RANGE_2MIN_5MIN => "2-5 min.",
            self::TIME_RANGE_5MIN_PLUS => "5+ min."
        );
    }

    public static function getInstance() {
        if(!isset(self::$_instance)) {
            self::$_instance = new Analytics_Model_Analytics();
        }
        return self::$_instance;
    }
    // END Singleton stuff

    public function getAdapter() {
        return $this->_sqliteAdapter;
    }

    public function getInstalledApp($where = null) {

        $where_query = $this->_getWhereForMetrics($where);

        // Get installed app
        $res_query = $this->_sqliteAdapter->query("
			SELECT sum(ios_install),sum(android_install)
			FROM app_installation_daily
			$where_query
		");

        $result = array(
            $res_query[0][0] ? $res_query[0][0] : "0",
            $res_query[0][1] ? $res_query[0][1] : "0"
        );

        return $result;
    }

    public function getTopInstalledApp($where = null) {

        $where_query = $this->_getWhereForMetrics($where);

        // Get installed app
        $res_query = $this->_sqliteAdapter->query("
			SELECT appId, sum(ios_install) + sum(android_install)
			FROM app_installation_daily
			$where_query
			GROUP BY appId
			ORDER BY sum(ios_install) + sum(android_install) DESC
			LIMIT 0,5
		");
        $result = $res_query;

        return $result;
    }

    public function getLoadedApp($where = null) {

        $where_query = $this->_getWhereForMetrics($where);

        // Get installed app
        $res_query = $this->_sqliteAdapter->query("
			SELECT sum(visits), timestampGMT
			FROM app_loaded_daily
			$where_query
			GROUP BY timestampGMT
		");

        $result = $res_query;

        return $result;
    }

    public function getTopLoadedApp($where = null) {

        $where_query = $this->_getWhereForMetrics($where);

        // Get installed app
        $res_query = $this->_sqliteAdapter->query("
			SELECT appId, sum(visits)
			FROM app_loaded_daily
			$where_query
			GROUP BY appId
			ORDER BY sum(visits) DESC
			LIMIT 0,5
		");
        $result = $res_query;

        return $result;
    }

    public function getFeatureVisitsApp($where = null) {

        $where_query = $this->_getWhereForMetrics($where);

        // Get feature visits for app
        $res_query = $this->_sqliteAdapter->query("
			SELECT featureName, sum(visits)
			FROM app_navigation_daily
			$where_query
			GROUP BY feature_id
			ORDER BY sum(visits) DESC
		");

        $result = $res_query;

        return $result;
    }

    public function getLocalizationApp($where = null) {

        $where_query = $this->_getWhereForMetrics($where);

        // Get localizations for app
        $res_query = $this->_sqliteAdapter->query("
			SELECT latitude, longitude
			FROM app_localization_daily
			$where_query
		");

        $result = $res_query;

        return $result;
    }

    public function getTimeSpendApp($where = null) {

        $where_query = $this->_getWhereForMetrics($where);

        // Get time spend for app
        $res_query = $this->_sqliteAdapter->query("
			SELECT SUM(visits), time_spend
			FROM app_loaded_daily
			$where_query
			GROUP BY time_spend
		");

        $result = array();

        if(is_array($res_query)) {
            foreach($res_query as $uniq_time) {
                $result[] = array(
                    "visits" => $uniq_time[0],
                    "range" => $this->_time_range_labels[$uniq_time[1]]
                );
            }
        }

        return $result;
    }

    public function getProductVisitsApp($where = null, $full_mode  = false) {

        $limit = "";
        if(!$full_mode) {
            $limit = "LIMIT 0,20";
        }

        $where_query = $this->_getWhereForMetrics($where);
        // Get product visits for app
        $res_query = $this->_sqliteAdapter->query("
			SELECT SUM(visits), productName
			FROM mcommerce_product_visit_daily
			$where_query
			GROUP BY productId
			ORDER BY SUM(visits) DESC
			$limit
		");

        $result = array();

        if(is_array($res_query)) {
            foreach($res_query as $uniq_product) {
                $result[] = array(
                    "metric" => $uniq_product[0],
                    "name" => $uniq_product[1]
                );
            }
        }

        return $result;
    }

    public function getTotalMcommerceVisitsApp($where = null) {

        $where_query = $this->_getWhereForMetrics($where);

        // Get total visits for app
        $res_query = $this->_sqliteAdapter->query("
			SELECT SUM(visits)
			FROM mcommerce_product_visit_daily
			$where_query
		");

        $result = array();

        if(is_array($res_query) AND $res_query[0][0]) {
            $result = $res_query;
        }

        return $result;
    }

    public function getAverageCartApp($where = null) {

        $where_query = $this->_getWhereForMetrics($where);

        // Get total visits for app
        $res_query = $this->_sqliteAdapter->query("
			SELECT AVG(average)
			FROM mcommerce_cart_average_daily
			$where_query
		");

        $result = array();

        if(is_array($res_query) AND $res_query[0][0]) {
            $result = $res_query;
        }

        return $result;
    }

    public function getSalesByCategoriesApp($where = null) {

        $where_query = $this->_getWhereForMetrics($where);

        // Get sales by categories for app
        $res_query = $this->_sqliteAdapter->query("
			SELECT SUM(occurency), categoryName
			FROM mcommerce_sales_per_category_daily
			$where_query
			GROUP BY categoryId
		");

        $result = array();

        if(is_array($res_query)) {
            foreach($res_query as $uniq_product) {
                $result[] = array(
                    "metric" => $uniq_product[0],
                    "categ_name" => $uniq_product[1]
                );
            }
        }

        return $result;
    }

    public function getSalesByPaymentMethodsApp($where = null) {

        $where_query = $this->_getWhereForMetrics($where);

        // Get sales by payment methods for app
        $res_query = $this->_sqliteAdapter->query("
			SELECT SUM(occurency), paymentMethodId
			FROM mcommerce_payment_method_daily
			$where_query
			GROUP BY paymentMethodId
		");

        $result = array();

        if(is_array($res_query)) {
            foreach($res_query as $uniq_payment) {
                $result[] = array(
                    "metric" => $uniq_payment[0],
                    "payment_id" => $uniq_payment[1]
                );
            }
        }

        return $result;
    }

    public function getSalesByStoreApp($where = null) {
        $where_query = $this->_getWhereForMetrics($where);

        // Get sales by stores for app
        $res_query = $this->_sqliteAdapter->query("
			SELECT SUM(occurency), storeId
			FROM mcommerce_sales_per_store_daily
			$where_query
			GROUP BY storeId
		");

        $result = array();

        if(is_array($res_query)) {
            foreach($res_query as $uniq_store) {
                $result[] = array(
                    "metric" => $uniq_store[0],
                    "store_id" => $uniq_store[1]
                );
            }
        }

        return $result;
    }

    public function getProductSalesApp($where = null, $full_mode = false) {

        $where_query = $this->_getWhereForMetrics($where);

        $limit = "";
        if(!$full_mode) {
            $limit = "LIMIT 0,20";
        }

        // Get product sales for app
        $res_query = $this->_sqliteAdapter->query("
			SELECT SUM(total), productName
			FROM mcommerce_product_sale_count_daily
			$where_query
			GROUP BY productId
			ORDER BY SUM(total) DESC
			$limit
		");

        $result = array();

        if(is_array($res_query)) {
            foreach($res_query as $uniq_product) {
                $result[] = array(
                    "metric" => $uniq_product[0],
                    "name" => $uniq_product[1]
                );
            }
        }

        return $result;
    }

    public function getDiscountApp($where = null) {

        $where_query = $this->_getWhereForMetrics($where);

        // Get discount validations for app
        $res_query = $this->_sqliteAdapter->query("
			SELECT SUM(total), promotionId, promotionName
			FROM discount_count_validation_daily
			$where_query
			GROUP BY promotionId
			ORDER BY SUM(total) DESC
		");

        $result = array();

        if(is_array($res_query)) {
            foreach($res_query as $uniq_discount) {
                $result[] = array(
                    "metric" => $uniq_discount[0],
                    "name" => $uniq_discount[2],
                    "id" => $uniq_discount[1]
                );
            }
        }

        return $result;
    }

    public function getLoyaltyApp($where) {

        $where_query = $this->_getWhereForMetrics($where);

        // Get loyalty card validations for app
        $res_query = $this->_sqliteAdapter->query("
			SELECT SUM(validation), SUM(rewardUsed), AVG(averageActifUser), AVG(averageAllUser), cardId, cardName
			FROM loyalty_card_daily
			$where_query
			GROUP BY cardId
			ORDER BY SUM(validation) DESC
		");

        $result = array();

        if(is_array($res_query)) {
            foreach($res_query as $uniq_loyalty) {
                $result[] = array(
                    "validation" => $uniq_loyalty[0],
                    "reward_used" => $uniq_loyalty[1],
                    "average_actif_user" => round($uniq_loyalty[2]),
                    "average_all_user" => round($uniq_loyalty[3]),
                    "id" => $uniq_loyalty[4],
                    "name" => $uniq_loyalty[5]
                );
            }
        }

        return $result;
    }

    public function getLoyaltyUsersApp($where = null) {

        $where_query = $this->_getWhereForMetrics($where);
        // Get loyalty card validations for app
        $res_query = $this->_sqliteAdapter->query("
			SELECT SUM(validation), customerId
			FROM loyalty_card_validation_per_user_daily
			$where_query
			GROUP BY customerId
			ORDER BY SUM(validation) DESC
		");

        $result = array();

        if(is_array($res_query)) {
            foreach($res_query as $uniq_loyalty) {
                $result[] = array(
                    "validation" => $uniq_loyalty[0],
                    "customer_id" => $uniq_loyalty[1]
                );
            }
        }

        return $result;
    }

    private function _getWhereForMetrics($data) {
        $where_array = array();

        if($data) {
            foreach ($data as $field => $value) {
                $where_array[] = str_ireplace("?", $value, $field);
            }
            $where_query = "WHERE " . implode(" AND ", $where_array);
        } else {
            $where_query = "WHERE timestampGMT >= ".$this->_default_period;
        }

        return $where_query;
    }

}
