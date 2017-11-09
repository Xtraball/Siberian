<?php
// TODO: Use mssafe_csv in this controller for better CSV
class Analytics_AnalyticsController extends Application_Controller_Default {

    public function getinstallsmetricAction() {

        if ($data = $this->getRequest()->getPost()) {

            try {

                $where = $this->_getWhereForMetrics($data);
                $sqlite_request = Analytics_Model_Analytics::getInstance();
                $app_installed = $sqlite_request->getInstalledApp($where);

                $html = array(
                    'success' => '1',
                    'data' => $app_installed
                );

            } catch (Exception $e) {
                $html = array(
                    'message' => $e->getMessage(),
                    'message_button' => 1,
                    'message_loader' => 1
                );
            }

            $this->getLayout()->setHtml(Zend_Json::encode($html));
        }
    }

    public function getvisitsmetricAction() {
        if ($data = $this->getRequest()->getPost()) {

            try {

                $dateFormat = "%D";
                $where = $this->_getWhereForMetrics($data);

                $app_visits_labels = array();
                $app_visits_data = array();

                //set all range value to zero then...
                $iterationStart = strtotime(date('Y-m-d 12:00:00',$data['date_range']['start']));
                $iterationEnd = strtotime(date('Y-m-d 12:00:00',$data['date_range']['end']));
                for ($i = $iterationStart; $i <= $iterationEnd ; $i += 60*60*24) { 
                    $app_visits_labels[] = "'".strftime($dateFormat,$i)."'";
                    $app_visits_data[] = 0;
                }

                //... replace values with db data
                $sqlite_request = Analytics_Model_Analytics::getInstance();
                $app_visits = $sqlite_request->getLoadedApp($where);
                foreach($app_visits as $app) {
                    $idx = array_search("'".strftime($dateFormat,$app[1])."'",$app_visits_labels);
                    $app_visits_data[$idx] = $app[0];
                }

                $html = array(
                    'success' => '1',
                    'data' => array(
                        'labels' => $app_visits_labels,
                        'metrics' => $app_visits_data
                    )
                );

            } catch (Exception $e) {
                $html = array(
                    'message' => $e->getMessage(),
                    'message_button' => 1,
                    'message_loader' => 1
                );
            }

            $this->getLayout()->setHtml(Zend_Json::encode($html));
        }
    }

    public function gettopinstallsmetricAction() {

        if ($data = $this->getRequest()->getPost()) {

            try {

                $where = $this->_getWhereForMetrics($data);

                $app_ids_name = Zend_Json::decode($data["app_ids_name"]);

                $sqlite_request = Analytics_Model_Analytics::getInstance();
                $top_app_installed = $sqlite_request->getTopInstalledApp($where);
                $result = array();
                if(is_array($top_app_installed)) {
                    foreach($top_app_installed as $uniq_app_installed) {
                        $result[] = array(
                            "name" => $app_ids_name[$uniq_app_installed[0]],
                            "metric" => $uniq_app_installed[1]
                        );
                    }
                }

                $html = array(
                    'success' => '1',
                    'data' => $result
                );

            } catch (Exception $e) {
                $html = array(
                    'message' => $e->getMessage(),
                    'message_button' => 1,
                    'message_loader' => 1
                );
            }

            $this->getLayout()->setHtml(Zend_Json::encode($html));
        }
    }

    public function gettopvisitsmetricAction() {

        if ($data = $this->getRequest()->getPost()) {

            try {

                $where = $this->_getWhereForMetrics($data);

                $app_ids_name = Zend_Json::decode($data["app_ids_name"]);

                $sqlite_request = Analytics_Model_Analytics::getInstance();
                $top_app_loaded = $sqlite_request->getTopLoadedApp($where);
                $result = array();
                if(is_array($top_app_loaded)) {
                    foreach($top_app_loaded as $uniq_app_loaded) {
                        $result[] = array(
                            "name" => $app_ids_name[$uniq_app_loaded[0]],
                            "metric" => $uniq_app_loaded[1]
                        );
                    }
                }

                $html = array(
                    'success' => '1',
                    'data' => $result
                );

            } catch (Exception $e) {
                $html = array(
                    'message' => $e->getMessage(),
                    'message_button' => 1,
                    'message_loader' => 1
                );
            }

            $this->getLayout()->setHtml(Zend_Json::encode($html));
        }
    }

    public function getfeaturevisitsmetricAction() {

        if ($data = $this->getRequest()->getPost()) {

            try {

                $where = $this->_getWhereForMetrics($data);

                $sqlite_request = Analytics_Model_Analytics::getInstance();
                $top_app_loaded = $sqlite_request->getFeatureVisitsApp($where);
                $result = array();
                if(is_array($top_app_loaded)) {
                    foreach($top_app_loaded as $uniq_app_loaded) {
                        $result[] = array(
                            "name" => __($uniq_app_loaded[0]),
                            "metric" => $uniq_app_loaded[1]
                        );
                    }
                }

                $html = array(
                    'success' => '1',
                    'data' => $result
                );

            } catch (Exception $e) {
                $html = array(
                    'message' => $e->getMessage(),
                    'message_button' => 1,
                    'message_loader' => 1
                );
            }

            $this->getLayout()->setHtml(Zend_Json::encode($html));
        }
    }

    public function getlocalizationmetricAction() {

        if ($data = $this->getRequest()->getPost()) {

            try {

                $where = $this->_getWhereForMetrics($data);
                $sqlite_request = Analytics_Model_Analytics::getInstance();
                $localizations = $sqlite_request->getLocalizationApp($where);
                $result = array();
                if(is_array($localizations)) {
                    foreach($localizations as $uniq_local) {
                        $result[] = array(
                            "lat" => $uniq_local[0],
                            "lng" => $uniq_local[1]
                        );
                    }
                }

                $html = array(
                    'success' => '1',
                    'markers' => $result
                );

            } catch (Exception $e) {
                $html = array(
                    'message' => $e->getMessage(),
                    'message_button' => 1,
                    'message_loader' => 1
                );
            }

            $this->getLayout()->setHtml(Zend_Json::encode($html));
        }
    }

    public function gettimespendappAction() {

        if ($data = $this->getRequest()->getPost()) {

            try {

                $where = $this->_getWhereForMetrics($data);
                $sqlite_request = Analytics_Model_Analytics::getInstance();
                $time_spend = $sqlite_request->getTimeSpendApp($where);

                $result = array();

                if(is_array($time_spend)) {
                    $labels = array();
                    $metrics = array();
                    foreach($time_spend as $time) {
                        $labels[] = $time["range"];
                        $metrics[] = $time["visits"];
                    }
                }
                $result["labels"] = $labels;
                $result["metrics"] = $metrics;

                $html = array(
                    'success' => '1',
                    'time_spend' => $result
                );

            } catch (Exception $e) {
                $html = array(
                    'message' => $e->getMessage(),
                    'message_button' => 1,
                    'message_loader' => 1
                );
            }

            $this->getLayout()->setHtml(Zend_Json::encode($html));
        }
    }

    public function getdiscountmetricAction() {

        if ($data = $this->getRequest()->getParams()) {

            try {

                $where = $this->_getWhereForMetrics($data);
                $sqlite_request = Analytics_Model_Analytics::getInstance();
                $discounts = $sqlite_request->getDiscountApp($where);

                $html = array(
                    'success' => '1',
                    'data' => $discounts
                );

            } catch (Exception $e) {
                $html = array(
                    'message' => $e->getMessage(),
                    'message_button' => 1,
                    'message_loader' => 1
                );
            }

            $this->getLayout()->setHtml(Zend_Json::encode($html));
        }
    }

    public function getsalescategorymetricAction() {

        if ($data = $this->getRequest()->getParams()) {

            try {

                $where = $this->_getWhereForMetrics($data);
                $dl_mode = false;
                if($data["full"]) {
                    $dl_mode = true;
                }
                $sqlite_request = Analytics_Model_Analytics::getInstance();
                $sales_categories = $sqlite_request->getSalesByCategoriesApp($where);
                $result = array(
                    "metrics" => array(),
                    "labels" => array()
                );

                $csv_string = "Name;Value\n";
                if(is_array($sales_categories)) {
                    foreach($sales_categories as $uniq_category) {
                        $result["labels"][] = $uniq_category["categ_name"];
                        $result["metrics"][] = $uniq_category["metric"];
                        if($dl_mode) {
                            $csv_string .= $uniq_category["categ_name"].";".$uniq_category["metric"]."\n";
                        }
                    }
                }

                if($dl_mode) {
                    $filename = "analytic_sales_by_category_".date("Ymd").".csv";
                    header('Content-Type: application/csv');
                    header('Content-Disposition: attachment; filename="'.$filename.'"');

                    echo $csv_string;
                    exit();
                }

                $html = array(
                    'success' => '1',
                    'data' => $result
                );

            } catch (Exception $e) {
                $html = array(
                    'message' => $e->getMessage(),
                    'message_button' => 1,
                    'message_loader' => 1
                );
            }

            $this->getLayout()->setHtml(Zend_Json::encode($html));
        }
    }

    public function getsalespaymentmetricAction() {

        if ($data = $this->getRequest()->getPost()) {

            try {

                $where = $this->_getWhereForMetrics($data);
                $sqlite_request = Analytics_Model_Analytics::getInstance();
                $sales_payments = $sqlite_request->getSalesByPaymentMethodsApp($where);
                $result = array(
                    "metrics" => array(),
                    "labels" => array()
                );

                $payments = new Mcommerce_Model_Payment_Method();
                $payments = $payments->findAll()->toArray();

                $paymentsById = array();
                foreach ($payments as $payment) {
                    $paymentsById[$payment['method_id']] = $payment['name'];
                }

                if(is_array($sales_payments)) {
                    foreach($sales_payments as $uniq_payment) {
                        $result["labels"][] = $paymentsById[$uniq_payment["payment_id"]];
                        $result["metrics"][] = $uniq_payment["metric"];
                    }
                }

                $html = array(
                    'success' => '1',
                    'data' => $result
                );

            } catch (Exception $e) {
                $html = array(
                    'message' => $e->getMessage(),
                    'message_button' => 1,
                    'message_loader' => 1
                );
            }

            $this->getLayout()->setHtml(Zend_Json::encode($html));
        }
    }

    public function getsalesstoremetricAction() {

        if ($data = $this->getRequest()->getPost()) {

            try {

                $where = $this->_getWhereForMetrics($data);
                $sqlite_request = Analytics_Model_Analytics::getInstance();
                $sales_stores = $sqlite_request->getSalesByStoreApp($where);
                $result = array(
                    "metrics" => array(),
                    "labels" => array()
                );

                $stores = new Mcommerce_Model_Store();
                $stores = $stores->findAll()->toArray();

                $storesById = array();
                foreach ($stores as $store) {
                    $storesById[$store['store_id']] = $store['name'];
                }

                if(is_array($sales_stores)) {
                    foreach($sales_stores as $uniq_store) {
                        $result["labels"][] = $storesById[$uniq_store["store_id"]];
                        $result["metrics"][] = $uniq_store["metric"];
                    }
                }

                $html = array(
                    'success' => '1',
                    'data' => $result
                );

            } catch (Exception $e) {
                $html = array(
                    'message' => $e->getMessage(),
                    'message_button' => 1,
                    'message_loader' => 1
                );
            }

            $this->getLayout()->setHtml(Zend_Json::encode($html));
        }
    }

    public function getsalesbyproductmetricAction() {

        if ($data = $this->getRequest()->getParams()) {

            try {

                $where = $this->_getWhereForMetrics($data);
                $dl_mode = false;
                if($data["full"]) {
                    $dl_mode = true;
                }

                $sqlite_request = Analytics_Model_Analytics::getInstance();
                $sales_by_product = $sqlite_request->getProductSalesApp($where, $dl_mode);

                if($dl_mode) {
                    $csv_string = "Name;Value\n";
                    if(is_array($sales_by_product)) {
                        foreach($sales_by_product as $uniq_product) {
                            if($dl_mode) {
                                $csv_string .= $uniq_product["name"].";".$uniq_product["metric"]."\n";
                            }
                        }
                    }

                    $filename = "analytic_sales_by_products_".date("Ymd").".csv";
                    header('Content-Type: application/csv');
                    header('Content-Disposition: attachment; filename="'.$filename.'"');

                    echo $csv_string;
                    exit();
                }

                $html = array(
                    'success' => '1',
                    'data' => $sales_by_product
                );

            } catch (Exception $e) {
                $html = array(
                    'message' => $e->getMessage(),
                    'message_button' => 1,
                    'message_loader' => 1
                );
            }

            $this->getLayout()->setHtml(Zend_Json::encode($html));
        }
    }

    public function getvisitsbyproductmetricAction() {

        if ($data = $this->getRequest()->getParams()) {

            try {

                $where = $this->_getWhereForMetrics($data);
                $dl_mode = false;
                if($data["full"]) {
                    $dl_mode = true;
                }
                $sqlite_request = Analytics_Model_Analytics::getInstance();
                $visits_by_product = $sqlite_request->getProductVisitsApp($where);

                if($dl_mode) {
                    $csv_string = "Name;Value\n";
                    if(is_array($visits_by_product)) {
                        foreach($visits_by_product as $uniq_product) {
                            if($dl_mode) {
                                $csv_string .= $uniq_product["name"].";".$uniq_product["metric"]."\n";
                            }
                        }
                    }

                    $filename = "analytic_visits_by_products_".date("Ymd").".csv";
                    header('Content-Type: application/csv');
                    header('Content-Disposition: attachment; filename="'.$filename.'"');

                    echo $csv_string;
                    exit();
                }

                $html = array(
                    'success' => '1',
                    'data' => $visits_by_product
                );

            } catch (Exception $e) {
                $html = array(
                    'message' => $e->getMessage(),
                    'message_button' => 1,
                    'message_loader' => 1
                );
            }

            $this->getLayout()->setHtml(Zend_Json::encode($html));
        }
    }

    public function gettotalvisitsmcommercemetricAction() {

        if ($data = $this->getRequest()->getParams()) {

            try {

                $where = $this->_getWhereForMetrics($data);

                $sqlite_request = Analytics_Model_Analytics::getInstance();
                $visits_total = $sqlite_request->getTotalMcommerceVisitsApp($where);

                $html = array(
                    'success' => '1',
                    'data' => $visits_total
                );

            } catch (Exception $e) {
                $html = array(
                    'message' => $e->getMessage(),
                    'message_button' => 1,
                    'message_loader' => 1
                );
            }

            $this->getLayout()->setHtml(Zend_Json::encode($html));
        }
    }

    public function getaveragecartmcommercemetricAction() {

        if ($data = $this->getRequest()->getParams()) {

            try {

                $where = $this->_getWhereForMetrics($data);

                $sqlite_request = Analytics_Model_Analytics::getInstance();
                $average = $sqlite_request->getAverageCartApp($where);

                $html = array(
                    'success' => '1',
                    'data' => $average
                );

            } catch (Exception $e) {
                $html = array(
                    'message' => $e->getMessage(),
                    'message_button' => 1,
                    'message_loader' => 1
                );
            }

            $this->getLayout()->setHtml(Zend_Json::encode($html));
        }
    }

    public function getloyaltymetricAction() {

        if ($data = $this->getRequest()->getPost()) {

            try {

                $where = $this->_getWhereForMetrics($data);
                $sqlite_request = Analytics_Model_Analytics::getInstance();
                $loyalties = $sqlite_request->getLoyaltyApp($where);

                $html = array(
                    'success' => '1',
                    'data' => $loyalties
                );

            } catch (Exception $e) {
                $html = array(
                    'message' => $e->getMessage(),
                    'message_button' => 1,
                    'message_loader' => 1
                );
            }

            $this->getLayout()->setHtml(Zend_Json::encode($html));
        }
    }

    public function getdldiscountAction() {

        if ($data = $this->getRequest()->getParams()) {

            try {
                $discount = new Promotion_Model_Promotion();
                $discount = $discount->find($data["discount_id"]);

                $csv_string = "Name;Email;Validation date\n";
                if ($discount->getId()) {
                    $customers = new Promotion_Model_Promotion();
                    $customers = $customers->getUsedPromotions(date("Y-m-d h:m:s", $data["start"]),date("Y-m-d h:m:s",$data["end"]));
                    foreach ($customers as $customer) {
                        $csv_string .= $customer->getCustomerName().";".$customer->getCustomerMail().";".$customer->getUsedAt()."\n";
                    }
                }

                $filename = "analytic_visits_by_products_".date("Ymd").".csv";
                header('Content-Type: application/csv');
                header('Content-Disposition: attachment; filename="'.$filename.'"');

                echo $csv_string;
                exit();
            } catch (Exception $e) {
                if(APPLICATION_ENV === "development") {
                    Zend_Debug::dump($e);
                }
                return false;
            }
        }
    }

    public function getdlloyaltyAction() {
        if ($data = $this->getRequest()->getParams()) {

            try {

                $start_date = date("Y-m-d h:m:s", $data["start"]);
                $end_date = date("Y-m-d h:m:s", $data["end"]);

                $loyalties = new LoyaltyCard_Model_Customer_Log();
                $lines = $loyalties->getDlAnalytics($data["card_id"], $start_date, $end_date);
                $csv_string = "Customer Name;Customer Email;Employee name;Number of points;Date;Reward\n";
                foreach($lines as $loyalty) {
                    $csv_string .= $loyalty->getCustomerName() . ";" . $loyalty->getEmail() . ";" . $loyalty->getEmployeeName() . ";" . $loyalty->getNumberOfPoints() . ";" . $loyalty->getCreatedAt() . ";No\n";
                }

                //Add rewards
                $lines = $loyalties->getDlRewards($data["card_id"], $start_date, $end_date);
                foreach($lines as $loyalty) {
                    $csv_string .= $loyalty->getCustomerName() . ";" . $loyalty->getEmail() . ";" . $loyalty->getEmployeeName() . ";" . $loyalty->getNumberOfPoints() . ";" . $loyalty->getUsedAt() . ";".$loyalty->getAdvantage()."\n";
                }
                $filename = "analytic_validations_by_users_".date("Ymd").".csv";
                header('Content-Type: application/csv');
                header('Content-Disposition: attachment; filename="'.$filename.'"');

                echo $csv_string;
                exit();
            } catch (Exception $e) {
                if(APPLICATION_ENV === "development") {
                    Zend_Debug::dump($e);
                }
                return false;
            }
        }
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

        if($loyalty_id = $data["card_id"]) {
            $where["cardId = ?"] = $loyalty_id;
        }

        if($date_range = $data["date_range"]) {
            $start = Zend_Json::decode($date_range["start"]);
            $end = Zend_Json::decode($date_range["end"]);
            $where["timestampGMT >= ?"] = $start;
            $where["timestampGMT <= ?"] = $end;
        }

        //Date range for DL mode
        if($date_start = $data["start"] AND $date_end = $data["end"]) {
            $where["timestampGMT >= ?"] = $date_start;
            $where["timestampGMT <= ?"] = $date_end;
        }

        return $where;
    }

}
