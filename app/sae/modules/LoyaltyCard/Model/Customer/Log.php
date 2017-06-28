<?php

class LoyaltyCard_Model_Customer_Log extends Core_Model_Default
{

    const DISPLAYED_PER_PAGE= 15;

    protected $_consumed_points;
    protected $_number_of_customers;

    public function __construct($datas = array()) {
        parent::__construct($datas);
        $this->_db_table = 'LoyaltyCard_Model_Db_Table_Customer_Log';
    }

    public function getConsumedPoints($start = null, $end = null) {

        if(!$this->_consumed_points) {
            $this->_consumed_points = $this->getTable()->getConsumedPoints($start, $end);
        }

        return $this->_consumed_points;
    }

    public function getAveragePerCustomer($start = null, $end = null) {
        $count_customer = $this->countCustomers($start, $end);
        $consumed_points = $this->getConsumedPoints($start, $end);

        return $consumed_points > 0 ? $consumed_points / $count_customer : $count_customer;
    }

    public function countCustomers($start = null, $end = null) {
        if(!$this->_number_of_customers) {
            $this->_number_of_customers = $this->getTable()->countCustomer($start, $end);
        }
        return $this->_number_of_customers;
    }

    public function getLoyalCustomers($start = null, $end = null) {
        $loyal_customer = $this->getTable()->getLoyalCustomers($start, $end);
        $count_customer = $this->countCustomers($start, $end);

        return $loyal_customer > 0 ? round($loyal_customer * 100 / $count_customer) : 0;
    }

    public function getBestCustomers($start = null, $end = null, $viewAll = true, $offset = 0) {
        return $this->getTable()->getBestCustomers($start, $end, $viewAll, $offset);
    }

    public function getEmployeesSummary($card_id, $start_at, $end_at) {

        $datas = $this->getTable()->getEmployeesSummary($card_id, $start_at, $end_at);
        $return = array();

        foreach($datas as $data) {

            if(!isset($return[$data['employee_id']]['count'])) {
                $return[$data['employee_id']] = array(
                    'name' => $data['name'],
                    'count' => 0,
                    'customer_ids' => array()
                );
            }

            $customer = new Customer_Model_Customer();
            $customer->find($data['customer_id'])->setCountPoints($data['count']);

            $return[$data['employee_id']]['count'] += $data['count'];
            $return[$data['employee_id']]['customers'][$data['customer_id']] = $customer;

        }

        return $return;

    }

    public function checkAll($start_at, $end_at) {

        $datas = $this->getTable()->checkAll($start_at, $end_at);
        $return = array();

        foreach($datas as $data) {
            $return[$data['pos_id']]['email'] = $data['pos_email'];
            $return[$data['pos_id']]['name'] = $data['pos_name'];
            $return[$data['pos_id']][$data['employee_name']][$data['customer_id']] = array('name' => $data['name'], 'points' => $data['count']);
        }

        return $return;
    }

    public function getFinishedCards($admin_id, $start_at, $end_at) {
        return $this->getTable()->getFinishedCards($admin_id, $start_at, $end_at);
    }

    public function getDlAnalytics($card_id, $start_date, $end_date) {
        return $this->getTable()->getDlAnalytics($card_id, $start_date, $end_date);
    }

    public function getDlRewards($card_id, $start_date, $end_date) {
        return $this->getTable()->getDlRewards($card_id, $start_date, $end_date);
    }

}