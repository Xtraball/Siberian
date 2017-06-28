<?php

class LoyaltyCard_Model_Db_Table_Customer_Log extends Core_Model_Db_Table
{
    protected $_name = "loyalty_card_customer_log";
    protected $_primary = 'log_id';

    public function getConsumedPoints($start, $end) {

        $select = $this->select()
            ->from($this->_name, array('count' => new Zend_Db_Expr('SUM(number_of_points)')))
        ;

        if(!is_null($start)) $select->where('created_at >= ?', $start);
        if(!is_null($end)) $select->where('created_at <= ?', $end);

        return (int) $this->_db->fetchOne($select);

    }

    public function countCustomer($start, $end) {

        $select = $this->select()
            ->from($this->_name, array('count' => new Zend_Db_Expr('COUNT(customer_id)')))
            ->group('customer_id')
        ;
        if(!is_null($start)) $select->where('created_at >= ?', $start);
        if(!is_null($end)) $select->where('created_at <= ?', $end);

        return (int) count($this->_db->fetchAll($select));

    }

    public function getLoyalCustomers($start, $end) {

        $select = $this->select()
            ->from($this->_name, array('customer_id', 'count' => new Zend_Db_Expr('COUNT(customer_id)')))
            ->having('count > 1')
            ->group('customer_id')
        ;

        if(!is_null($start)) $select->where('created_at >= ?', $start);
        if(!is_null($end)) $select->where('created_at <= ?', $end);

        return (int) count($this->_db->fetchAll($select));

    }

    public function getBestCustomers($start, $end, $viewAll, $offset = 0) {

        $select = $this->select()
            ->from(array('log' => $this->_name), array('customer_id' => 'customer_id', 'number_of_points' => new Zend_Db_Expr('SUM(log.number_of_points)')))
            ->joinLeft(array('c' => 'customer'), 'c.customer_id = log.customer_id')
            ->group('log.customer_id')
            ->order('number_of_points DESC')
            ->limit(LoyaltyCard_Model_Customer_Log::DISPLAYED_PER_PAGE, $offset)
            ->setIntegrityCheck(false)
        ;

        if(!is_null($start)) $select->where('log.created_at >= ?', $start);
        if(!is_null($end)) $select->where('log.created_at <= ?', $end);

        if(!$viewAll) {
            $select->where('c.show_in_social_gaming = 1');
        }

        $this->_modelClass = 'Customer_Model_Customer';

        return $this->fetchAll($select);

    }

    public function getEmployeesSummary($card_id, $start_at, $end_at) {

        $select = $this->_db->select()
            ->from(array('main' => $this->_name), array('count' => new Zend_Db_Expr('SUM(main.number_of_points)'), 'employee_id', 'customer_id'))
            ->join(array('c' => 'customer'), 'c.customer_id = main.customer_id', array())
            ->join(array('re' => 'admin_pos_employee'), 're.employee_id = main.employee_id', array('name'))
            ->where('main.card_id = ?', $card_id)
            ->where('main.created_at >= ?', $start_at)
            ->where('main.created_at <= ?', $end_at)
            ->group('main.employee_id')
            ->group('main.customer_id')
            ->order('count DESC')
        ;

        return $this->_db->fetchAll($select);

    }

    public function checkAll($start_at, $end_at) {

        $select = $this->_db->select()
            ->from(array('main' => $this->_name), array('count' => new Zend_Db_Expr('COUNT(main.customer_id)'), 'customer_id', 'pos_id'))
            ->join(array('c' => 'customer'), 'c.customer_id = main.customer_id', array('name' => new Zend_Db_Expr('CONCAT(c.firstname, " ", c.lastname)')))
            ->join(array('r' => 'pos'), 'r.pos_id = main.pos_id', array('pos_email' => 'email', 'pos_name' => 'name'))
            ->join(array('re' => 'admin_pos_employee'), 're.employee_id = main.employee_id', array('employee_id' => 'employee_id', 'employee_name' => 'name'))
            ->where('main.created_at >= ?', $start_at)
            ->where('main.created_at <= ?', $end_at)
            ->group('main.pos_id')
            ->group('main.employee_id')
            ->group('main.customer_id')
            ->having('count > 1')
            ->order('count DESC')
        ;

        return $this->_db->fetchAll($select);
    }

    public function getFinishedCards($admin_id, $start_at, $end_at) {

        $select = $this->_db->select()->from(array('fc' => 'loyalty_card'));

        $select->join(array('fcc' => 'loyalty_card_customer'), 'fcc.card_id = fc.card_id', array('pos_id', 'used_at'))
            ->join(array('c' => 'customer'), 'c.customer_id = fcc.customer_id', array('customer_name' => new Zend_Db_Expr('CONCAT(c.firstname, " ", c.lastname)')))
            ->join(array('re' => 'admin_pos_employee'), 're.employee_id = fcc.validate_by', array('employee_name' => 're.name'))
            ->where('fc.admin_id = ?', $admin_id)
            ->where('fcc.used_at >= ?', $start_at)
            ->where('fcc.used_at <= ?', $end_at)
            ->order('fcc.used_at DESC')
        ;

        return $this->_db->fetchAll($select);

    }

    public function getDlAnalytics($card_id, $start_date, $end_date) {
        $select = $this->select()
            ->from(array('main' => $this->_name))
            ->joinLeft(array('c' => 'customer'), 'c.customer_id = main.customer_id', array('customer_name' => new Zend_Db_Expr('CONCAT(c.firstname, " ", c.lastname)'), 'email'))
            ->joinLeft(array('lp' => 'loyalty_card_password'), 'main.password_id = lp.password_id', array('employee_name' => 'name'))
            ->where('main.created_at >= ?', $start_date)
            ->where('main.created_at <= ?', $end_date)
            ->where('main.card_id = ?', $card_id)
            ->setIntegrityCheck(false)
        ;

        return $this->fetchAll($select);
    }

    public function getDlRewards($card_id, $start_date, $end_date) {
        $select = $this->select()
            ->from(array('main' => 'loyalty_card_customer'))
            ->joinLeft(array('c' => 'customer'), 'c.customer_id = main.customer_id', array('customer_name' => new Zend_Db_Expr('CONCAT(c.firstname, " ", c.lastname)'), 'email'))
            ->joinLeft(array('lp' => 'loyalty_card_password'), 'main.validate_by = lp.password_id', array('employee_name' => 'name'))
            ->where('main.created_at >= ?', $start_date)
            ->where('main.created_at <= ?', $end_date)
            ->where('main.card_id = ?', $card_id)
            ->wwhere('main.validate_by is NOT null')
            ->setIntegrityCheck(false)
        ;

        return $this->fetchAll($select);
    }

}