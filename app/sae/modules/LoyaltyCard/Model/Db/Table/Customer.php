<?php

class LoyaltyCard_Model_Db_Table_Customer extends Core_Model_Db_Table
{
    protected $_name = "loyalty_card_customer";
    protected $_primary = 'customer_card_id';

    public function findAllByOptionValue($value_id, $customer_id) {

        $cards = array();
        $last_card_is_done = false;

        $pcols = array_keys($this->_db->describeTable($this->_name));
        $pcols = array_combine($pcols, $pcols);
        $pcols['number_of_points'] = new Zend_Db_Expr("IFNULL(fcc.number_of_points, 0)");

        $excluded = array('card_id');
        foreach($excluded as $field) {
            unset($pcols[$field]);
        }

        $fcols = array_keys($this->_db->describeTable('loyalty_card'));
        $fcols = array_combine($fcols, $fcols);
        $fcols['max_number_of_points'] = 'number_of_points';
        $excluded = array('id', 'created_at', 'updated_at', 'number_of_points');
        foreach($excluded as $field) {
            unset($fcols[$field]);
        }

        // Récupère la carte de fidélité en cours
        $select = $this->_prepareSelect($value_id, $fcols)
            ->join(array('fcc' => $this->_name), 'fcc.card_id = fc.card_id', $pcols)
            ->where("fcc.customer_id = ?", $customer_id)
            ->where("{$pcols['number_of_points']} < fc.number_of_points OR fcc.is_used = 0")
            ->order("fc.card_id ASC")
            ->order("fcc.customer_card_id ASC")
            ->setIntegrityCheck(false)
        ;

        $tmp_cards = $this->fetchAll($select);
        foreach($tmp_cards as $tmp_card) {
            $cards[$tmp_card->getCustomerCardId()] = $tmp_card;
            $last_card_is_done = $tmp_card->getNumberOfPoints() == $tmp_card->getMaxNumberOfPoints();

        }

        // S'il n'y a pas de carte de fidélité ou si la dernière est terminée, on en crée une nouvelle basée sur la dernière de l'option_value en cours
        if(count($cards) == 0 OR $last_card_is_done) {
            foreach($pcols as $key => $value) $pcols[$key] = new Zend_Db_Expr('null');
            //$pcols['customer_card_id'] = new Zend_Db_Expr('0');
            $pcols['customer_id'] = new Zend_Db_Expr($customer_id);
            $pcols['number_of_points'] = new Zend_Db_Expr('0');
            $pcols['is_used'] = new Zend_Db_Expr('0');
            $select = $this->_prepareSelect($value_id, $fcols);
            $select->joinLeft(array('fcc' => $this->_name), "fc.card_id = fcc.card_id AND fcc.customer_id = $customer_id", $pcols)
                ->where("fc.use_once = 0 OR fcc.customer_id IS NULL")
                ->order('fc.created_at DESC')
                ->limit(1)
            ;

            $select->setIntegrityCheck(false);
            $row = $this->fetchRow($select);

            if($row) {
                $cards[$row->getCustomerCardId()] = $row;
            }
        }

        return $cards;
    }

    public function findLast($value_id, $customer_id) {

        // Prépare les colonnes
        $card = false;
        $pcols = array_keys($this->_db->describeTable($this->_name));
        $pcols = array_combine($pcols, $pcols);
        $pcols['number_of_points'] = new Zend_Db_Expr("IFNULL(fcc.number_of_points, 0)");

        $excluded = array('card_id');
        foreach($excluded as $field) {
            unset($pcols[$field]);
        }

        $fcols = array_keys($this->_db->describeTable('loyalty_card'));
        $fcols = array_combine($fcols, $fcols);
        $fcols['max_number_of_points'] = 'number_of_points';
        $excluded = array('id', 'created_at', 'updated_at', 'number_of_points');
        foreach($excluded as $field) {
            unset($fcols[$field]);
        }

        if($customer_id) {
            // Récupère la carte de fidélité en cours
            $select = $this->_prepareSelect($value_id, $fcols)
                ->join(array('fcc' => $this->_name), 'fcc.card_id = fc.card_id', $pcols)
                ->where("fcc.customer_id = ?", $customer_id)
                ->where("{$pcols['number_of_points']} < fc.number_of_points")
                ->order("fc.card_id ASC")
                ->limit(1)
                ->setIntegrityCheck(false)
            ;

            $card = $this->fetchRow($select);
        }

        // S'il n'y a pas de carte de fidélité ou si la dernière est terminée, on en crée une nouvelle basée sur la dernière du restaurant en cours
        if(!$card) {
            foreach($pcols as $key => $value) $pcols[$key] = new Zend_Db_Expr('null');
            $pcols['customer_card_id'] = new Zend_Db_Expr('0');
            if($customer_id) $pcols['customer_id'] = new Zend_Db_Expr($customer_id);
            $pcols['number_of_points'] = new Zend_Db_Expr('0');
            $pcols['is_used'] = new Zend_Db_Expr('0');

            $select = $this->_prepareSelect($value_id, array_merge($fcols, $pcols));
            $select//->joinLeft(array('fcc' => $this->_name), "fc.card_id = fcc.card_id AND fcc.customer_id = $customer_id", $pcols)
                ->order('fc.created_at DESC')
                ->limit(1)
            ;

            $select->setIntegrityCheck(false);
            $card = $this->fetchRow($select);
        }

        return $card;
    }

    public function createCard($value_id, $customer_id) {

        // Prépare les colonnes
        $card = false;
        $pcols = array_keys($this->_db->describeTable($this->_name));
        $pcols = array_combine($pcols, $pcols);
        $excluded = array('card_id');
        foreach($excluded as $field) {
            unset($pcols[$field]);
        }
        foreach($pcols as $key => $value) $pcols[$key] = new Zend_Db_Expr('null');
        $pcols['customer_card_id'] = new Zend_Db_Expr('0');
        $pcols['number_of_points'] = new Zend_Db_Expr("0");
        if($customer_id) $pcols['customer_id'] = new Zend_Db_Expr($customer_id);
        $pcols['is_used'] = new Zend_Db_Expr('0');

        $fcols = array_keys($this->_db->describeTable('loyalty_card'));
        $fcols = array_combine($fcols, $fcols);
        $fcols['max_number_of_points'] = 'number_of_points';
        $excluded = array('id', 'created_at', 'updated_at', 'number_of_points');
        foreach($excluded as $field) {
            unset($fcols[$field]);
        }

        $select = $this->_prepareSelect($value_id, array_merge($fcols, $pcols));
        $select->order('fc.created_at DESC')
            ->limit(1)
        ;

        $select->setIntegrityCheck(false);
        return $this->fetchRow($select);


    }


//    public function findAll($value_id, $customer_id) {
//
//        $select = $this->select()->setIntegrityCheck(false);
//        $pcols = $this->_getPCols();
//        $fcols = $this->_getFCols();
//
//        $select->from(array('fcc' => $this->_name), $pcols)
//            ->join(array('fc' => 'loyalty_card'), "fc.card_id = fcc.card_id", $fcols)
//            ->join(array('aov' => 'application_option_value'), "aov.value_id = fc.value_id", array())
//            ->where('fc.value_id = ?', $value_id)
//            ->where("fcc.customer_id = ?", $customer_id)
//            ->where("fcc.number_of_points > 0")
//        ;
//
//        return $this->fetchAll($select);
//
//    }

    public function addError() {



    }

    protected function _prepareSelect($value_id, $cols = array()) {

        if(empty($cols)) $cols = null;

        $select = $this->select()
            ->from(array('fc' => 'loyalty_card'), $cols)
        ;

        return $select->where($this->_db->quoteInto('fc.value_id = ?', $value_id));

    }

    protected function _getPCols() {

        $pcols = array_keys($this->_db->describeTable($this->_name));
        $pcols = array_combine($pcols, $pcols);
        $pcols['number_of_points'] = new Zend_Db_Expr("IFNULL(fcc.number_of_points, 0)");
        $excluded = array('card_id');
        foreach($excluded as $field) {
            unset($pcols[$field]);
        }
        return $pcols;
    }

    protected function _getFCols() {

        $fcols = array_keys($this->_db->describeTable('loyalty_card'));
        $fcols = array_combine($fcols, $fcols);
        $fcols['max_number_of_points'] = 'number_of_points';
        $excluded = array('id', 'created_at', 'updated_at', 'number_of_points');
        foreach($excluded as $field) {
            unset($fcols[$field]);
        }
        return $fcols;
    }

}
