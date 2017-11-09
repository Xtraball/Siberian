<?php

class Analytics_Model_Aggregate_Mcommerce {

    private $_sqliteAdapter = null;

    private $_orders = array();
    private $_appIdByMcommerceId = null;
    private $_appIdByProduct = null;
    private $_productsById = array();
    private $_categoryNameCategoryId = array();
    private $_categoryIdsProductId = array();

    // START Singleton stuff
    static private $_instance = null;

    private function __construct() {
        $this->_sqliteAdapter = Siberian_Wrapper_Sqlite::getInstance();
        $this->_sqliteAdapter->setDbPath(Core_Model_Directory::getBasePathTo("metrics/siberiancms.db"));
    }

    public static function getInstance() {
        if(!isset(self::$_instance)) {
            self::$_instance = new Analytics_Model_Aggregate_Mcommerce();
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
            $this->_orders = $this->_getOrders($timestampRange);

            //set a correspondance array for mcommerceId and appId
            $modelMcommerce = new Mcommerce_Model_Mcommerce();
            $this->_appIdByMcommerceId = $modelMcommerce->getAppIdByMcommerceId();

            $productModel = new Catalog_Model_Product();

            //set a correspondance array for productId and appId
            $this->_appIdByProduct = $productModel->getAppIdByProduct();

            //get product names
            $products = $productModel->findAll();
            foreach ($products as $product) {
                $this->_categoryIdsProductId[$product->getProductId()] = $product->getCategoryIds();
                $this->_productsById[$product->getProductId()] = $product->getName();
            }

            //get product names
            $categoryModel = new Folder_Model_Category();
            $categories = $categoryModel->findAll()->toArray();
            foreach ($categories as $category) {
                $this->_categoryNameCategoryId[$category['category_id']] = $category['title'];
            }

            return (
                $this->_aggregateMcommerceVisitProduct($timestampRange) &&
                $this->_aggregateMcommercePaymentMethod($timestampRange) &&
                $this->_aggregateMcommerceSalePerStore($timestampRange) &&
                $this->_aggregateMcommerceSalePerCategory($timestampRange) &&
                $this->_aggregateMcommerceProductSaleCount($timestampRange) &&
                $this->_aggregateMcommerceAverageSale($timestampRange)
            );
        } catch (Exception $e) {
            if(APPLICATION_ENV === "development") {
                Zend_Debug::dump($e);
            }
            return false;
        }
    }

    public function _getOrders($aggregationDate) {
        $from = $aggregationDate['from'];
        $to = $aggregationDate['to'];

        //get day information for wanted metrics
        $modelOrder = new Mcommerce_Model_Order();
        return $modelOrder->findAll(array(
            'status_id IN (?)' =>  array(2,3),
            'created_at > ?' => date('Y-m-d 00:00:00',$from),
            'created_at < ?' => date('Y-m-d 00:00:00',$to)
        ))->toArray();

    }

    private function _aggregateMcommerceVisitProduct($aggregationDate) {
        $timestampGMT = $aggregationDate['dayTimestamp'];
        $from = $aggregationDate['from'];
        $to = $aggregationDate['to'];

        //get day information for wanted metrics
        $res_query = $this->_sqliteAdapter->query("SELECT productId
            FROM mcommerce_product_navigation
            WHERE timestampGMT > $from
            AND timestampGMT < $to
        ");

        $aggregatedData = array();
        if(is_array($res_query)) {
            //calcul metrics
            foreach ($res_query as $row) {
                if(count($row) !== 1) continue;
                $productId = $row[0];
                $appId = $this->_appIdByProduct[$productId]['app_id'];
                if(!array_key_exists($appId, $aggregatedData)) {
                    $aggregatedData[$appId] = array();
                }
                if(!array_key_exists($productId, $aggregatedData[$appId])) {
                    $aggregatedData[$appId][$productId] = 0;
                }
                $aggregatedData[$appId][$productId] += 1;
            }
        }

        foreach ($aggregatedData as $appId => $products) {
            foreach ($products as $productId => $visits) {

                $res_query = $this->_sqliteAdapter->query("SELECT id from mcommerce_product_visit_daily
                    WHERE appId = $appId
                    AND productId = $productId
                    AND timestampGMT = $timestampGMT");

                //adding new metrics
                if(!is_array($res_query)) {
                    $productName = $this->_productsById[$productId];
                    $res_query = $this->_sqliteAdapter->query("INSERT into mcommerce_product_visit_daily
                        ('appId', 'visits', 'productId', 'productName', 'timestampGMT') VALUES
                        ($appId, $visits, $productId, '$productName', $timestampGMT)");
                    if(!$res_query) {
                        throw new Exception("Cannot insert into sqlite");
                    }
                //updating current metric
                } else {
                    $id = $res_query[0][0];
                    $res_query = $this->_sqliteAdapter->query("UPDATE mcommerce_product_visit_daily SET visits = $visits WHERE id = $id");
                    if(!$res_query) {
                        throw new Exception("Cannot update into sqlite");
                    }
                }
            }
        }

        return true;
    }

    private function _aggregateMcommercePaymentMethod($aggregationDate) {
        $timestampGMT = $aggregationDate['dayTimestamp'];

        $aggregatedData = array();
        //calcul metrics
        foreach ($this->_orders as $order) {
            $appId = $this->_appIdByMcommerceId[$order['mcommerce_id']]['app_id'];
            $payment_method_id = $order['payment_method_id'];
            if(!array_key_exists($appId, $aggregatedData)) {
                $aggregatedData[$appId] = array();
            }
            if(!array_key_exists($payment_method_id, $aggregatedData[$appId])) {
                $aggregatedData[$appId][$payment_method_id] = 0;
            }
            $aggregatedData[$appId][$payment_method_id] += 1;
        }

        foreach ($aggregatedData as $appId => $paymentMethod) {
            foreach ($paymentMethod as $paymentMethodId => $occurency) {

                $res_query = $this->_sqliteAdapter->query("SELECT id from mcommerce_payment_method_daily
                    WHERE appId = $appId
                    AND paymentMethodId = $paymentMethodId
                    AND timestampGMT = $timestampGMT");

                //adding new metrics
                if(!is_array($res_query)) {
                    $res_query = $this->_sqliteAdapter->query("INSERT into mcommerce_payment_method_daily
                        ('appId', 'occurency', 'paymentMethodId', 'timestampGMT') VALUES
                        ($appId, $occurency, $paymentMethodId, $timestampGMT)");
                    if(!$res_query) {
                        throw new Exception("Cannot insert into sqlite");
                    }
                //updating current metric
                } else {
                    $id = $res_query[0][0];
                    $res_query = $this->_sqliteAdapter->query("UPDATE mcommerce_payment_method_daily SET occurency = $occurency WHERE id = $id");
                    if(!$res_query) {
                        throw new Exception("Cannot update into sqlite");
                    }
                }
            }
        }
        return true;
    }

    private function _aggregateMcommerceSalePerStore($aggregationDate) {
        $timestampGMT = $aggregationDate['dayTimestamp'];

        $aggregatedData = array();
        //calcul metrics
        foreach ($this->_orders as $order) {
            $appId = $this->_appIdByMcommerceId[$order['mcommerce_id']]['app_id'];
            $storeId = $order['store_id'];
            if(!array_key_exists($appId, $aggregatedData)) {
                $aggregatedData[$appId] = array();
            }
            if(!array_key_exists($storeId, $aggregatedData[$appId])) {
                $aggregatedData[$appId][$storeId] = 0;
            }
            $aggregatedData[$appId][$storeId] += 1;
        }

        foreach ($aggregatedData as $appId => $store) {
            foreach ($store as $storeId => $occurency) {

                $res_query = $this->_sqliteAdapter->query("SELECT id from mcommerce_sales_per_store_daily
                    WHERE appId = $appId
                    AND storeId = $storeId
                    AND timestampGMT = $timestampGMT");

                //adding new metrics
                if(!is_array($res_query)) {
                    $res_query = $this->_sqliteAdapter->query("INSERT into mcommerce_sales_per_store_daily
                        ('appId', 'occurency', 'storeId', 'timestampGMT') VALUES
                        ($appId, $occurency, $storeId, $timestampGMT)");
                    if(!$res_query) {
                        throw new Exception("Cannot insert into sqlite");
                    }
                //updating current metric
                } else {
                    $id = $res_query[0][0];
                    $res_query = $this->_sqliteAdapter->query("UPDATE mcommerce_sales_per_store_daily SET occurency = $occurency WHERE id = $id");
                    if(!$res_query) {
                        throw new Exception("Cannot update into sqlite");
                    }
                }
            }
        }
        return true;
    }

    private function _aggregateMcommerceSalePerCategory($aggregationDate) {
        $timestampGMT = $aggregationDate['dayTimestamp'];

        $aggregatedData = array();
        //calcul metrics
        foreach ($this->_orders as $order) {
            $appId = $this->_appIdByMcommerceId[$order['mcommerce_id']]['app_id'];
            if(!array_key_exists($appId, $aggregatedData)) {
                $aggregatedData[$appId] = array();
            }

            $modelCart = new Mcommerce_Model_Cart();
            $cartId = $order['cart_id'];
            $cart = $modelCart->find($cartId);


            foreach($cart->getLines()->toArray() as $cart_line) {
                foreach ($this->_categoryIdsProductId[$cart_line['product_id']] as $categoryId) {
                    if(!array_key_exists($categoryId, $aggregatedData[$appId])) {
                        $aggregatedData[$appId][$categoryId] = 0;
                    }
                    $aggregatedData[$appId][$categoryId] += $cart_line['qty'];
                }
            }
        }

        foreach ($aggregatedData as $appId => $category) {
            foreach ($category as $categoryId => $occurency) {

                $categoryName = $this->_categoryNameCategoryId[$categoryId];

                $res_query = $this->_sqliteAdapter->query("SELECT id from mcommerce_sales_per_category_daily
                    WHERE appId = $appId
                    AND categoryId = $categoryId
                    AND timestampGMT = $timestampGMT");

                //adding new metrics
                if(!is_array($res_query)) {
                    $res_query = $this->_sqliteAdapter->query("INSERT into mcommerce_sales_per_category_daily
                        ('appId', 'occurency', 'categoryId', 'categoryName', 'timestampGMT') VALUES
                        ($appId, $occurency, $categoryId, '$categoryName', $timestampGMT)");
                    if(!$res_query) {
                        throw new Exception("Cannot insert into sqlite");
                    }
                //updating current metric
                } else {
                    $id = $res_query[0][0];
                    $res_query = $this->_sqliteAdapter->query("UPDATE mcommerce_sales_per_category_daily SET occurency = $occurency WHERE id = $id");
                    if(!$res_query) {
                        throw new Exception("Cannot update into sqlite");
                    }
                }
            }
        }

        return true;
    }

    private function _aggregateMcommerceProductSaleCount($aggregationDate) {
        $timestampGMT = $aggregationDate['dayTimestamp'];

        $aggregatedData = array();
        //calcul metrics
        foreach ($this->_orders as $order) {
            $appId = $this->_appIdByMcommerceId[$order['mcommerce_id']]['app_id'];
            if(!array_key_exists($appId, $aggregatedData)) {
                $aggregatedData[$appId] = array();
            }

            $modelCart = new Mcommerce_Model_Cart();
            $cartId = $order['cart_id'];
            $cart = $modelCart->find($cartId);

            foreach($cart->getLines()->toArray() as $cart_line) {
                $productId = $cart_line['product_id'];
                if(!array_key_exists($productId, $aggregatedData[$appId])) {
                    $aggregatedData[$appId][$productId] = 0;
                }
                $aggregatedData[$appId][$productId] += $cart_line['qty'];
            }
        }

        foreach ($aggregatedData as $appId => $product) {
            foreach ($product as $productId => $total) {

                $productName = $this->_productsById[$productId];

                $res_query = $this->_sqliteAdapter->query("SELECT id from mcommerce_product_sale_count_daily
                    WHERE appId = $appId
                    AND productId = $productId
                    AND timestampGMT = $timestampGMT");

                //adding new metrics
                if(!is_array($res_query)) {
                    $res_query = $this->_sqliteAdapter->query("INSERT into mcommerce_product_sale_count_daily
                        ('appId', 'total', 'productId', 'timestampGMT', 'productName') VALUES
                        ($appId, $total, $productId, $timestampGMT, '$productName')");
                    if(!$res_query) {
                        throw new Exception("Cannot insert into sqlite");
                    }
                //updating current metric
                } else {
                    $id = $res_query[0][0];
                    $res_query = $this->_sqliteAdapter->query("UPDATE mcommerce_product_sale_count_daily SET total = $total WHERE id = $id");
                    if(!$res_query) {
                        throw new Exception("Cannot update into sqlite");
                    }
                }
            }
        }
        return true;
    }

    private function _aggregateMcommerceAverageSale($aggregationDate) {
        $timestampGMT = $aggregationDate['dayTimestamp'];

        $aggregatedData = array();
        //calcul metrics
        foreach ($this->_orders as $order) {
            $appId = $this->_appIdByMcommerceId[$order['mcommerce_id']]['app_id'];
            if(!array_key_exists($appId, $aggregatedData)) {
                $aggregatedData[$appId] = array(
                    "average" => 0,
                    "cart" => array()
                );
            }

            $aggregatedData[$appId]['cart'][] = $order['total'];
        }

        //calcul average
        foreach($aggregatedData as $appId => $row) {
            $aggregatedData[$appId]['average'] =
                round(
                    array_sum($aggregatedData[$appId]['cart'])/
                    count($aggregatedData[$appId]['cart'])
                );
        }

        foreach ($aggregatedData as $appId => $cartCalculation) {
            $average = $cartCalculation['average'];
            $res_query = $this->_sqliteAdapter->query("SELECT id from mcommerce_cart_average_daily
                WHERE appId = $appId
                AND timestampGMT = $timestampGMT");

            //adding new metrics
            if(!is_array($res_query)) {
                $res_query = $this->_sqliteAdapter->query("INSERT into mcommerce_cart_average_daily
                    ('appId', 'average', 'timestampGMT') VALUES
                    ($appId, $average, $timestampGMT)");
                if(!$res_query) {
                    throw new Exception("Cannot insert into sqlite");
                }
            //updating current metric
            } else {
                $id = $res_query[0][0];
                $res_query = $this->_sqliteAdapter->query("UPDATE mcommerce_cart_average_daily SET average = $average WHERE id = $id");
                if(!$res_query) {
                    throw new Exception("Cannot update into sqlite");
                }
            }
        }
        return true;
    }
}