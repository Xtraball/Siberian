<?php

class LoyaltyCard_Model_Customer extends Core_Model_Default
{

    const TYPE_VALIDATE_POINT = 1;
    const TYPE_CLOSE_CARD = 2;

    public function __construct($datas = array()) {
        parent::__construct($datas);
        $this->_db_table = 'LoyaltyCard_Model_Db_Table_Customer';
    }

    public static function getMessageCardIsLocked() {
        return parent::_('We are sorry, your card is temporarily blocked');
    }

    public function findAllByOptionValue($value_id, $customer_id) {
        $tmp_cards = $this->getTable()->findAllByOptionValue($value_id, $customer_id);
        $cards = array();
        $remove_cards = false;

        if(!empty($tmp_cards)) {

            foreach($tmp_cards as $tmp_card) {
                $card = new self;
                $card->setData($tmp_card->getData());
                $is_locked = false;
                if(!is_null($card->getLastError())) {
                    $now = $this->formatDate(null, 'y-MM-dd HH:mm:ss');
                    $date = new Zend_Date();
                    $date->setDate($card->getLastError(), "y-MM-dd HH:mm:ss");

                    $last_error = $date->addDay(1)->toString('y-MM-dd HH:mm:ss');
                    $is_locked = ($last_error > $now && $card->getNumberOfError() >= 3);
                    if(!$last_error > $now) $card->setNumberOfError(0);
                }

                $card->setIsLocked($is_locked)->setId($card->getCustomerCardId());

                // Si la carte est bloquée, on ne conserve que celle là, on supprime les autres et on stop le traitement
                if($is_locked) {
                    $cards = array($card);
                    break;
                }
                else {
                    $cards[] = $card;
                }
            }

        }

        return $cards;
    }

    public function findLast($value_id, $customer_id) {

        $row = $this->getTable()->findLast($value_id, $customer_id);

        if($row) {

            $this->setData($row->getData())
                ->setId($row->getCustomerCardId())
            ;

            $is_locked = false;
            if(!is_null($this->getLastError())) {
                $now = $this->formatDate(null, 'y-MM-dd HH:mm:ss');
                $date = new Zend_Date($this->getLastError());
                $last_error = $date->addDay(1)->toString('y-MM-dd HH:mm:ss');
                $is_locked = ($last_error > $now && $this->getNumberOfError() >= 3);
                if(!$last_error > $now) $this->setNumberOfError(0);
            }

            $this->setIsLocked($is_locked);

        }

        return $this;

    }

    public function createCard($value_id, $customer_id) {

        $row = $this->getTable()->createCard($value_id, $customer_id);

        if($row) {

            $this->setData($row->getData())
                ->setIsLocked(false)
                ->setId($row->getCustomerCardId())
            ;

        }

        return $this;

    }

    public function findAll($value_id, $customer_id) {
        return $this->getTable()->findAll($value_id, $customer_id);
    }

    public function createLog($password_id, $nbr, $created_at = null) {
        $log = new LoyaltyCard_Model_Customer_Log();
        $log->setCardId($this->getCardId())
            ->setCustomerId($this->getCustomerId())
            ->setPasswordId($password_id)
            ->setNumberOfPoints($nbr)
        ;
        if($created_at) {
            $log->setCreatedAt($created_at);
        }
        $log->save();

        return $this;
    }

    public function addError() {

        $now = $this->formatDate(null, 'y-MM-dd HH:mm:ss');
        $date = new Zend_Date($this->getLastError());
        $last_error = $date->addDay(1)->toString('y-MM-dd HH:mm:ss');
        if($last_error < $now) $nbr = 1;
        else $nbr = (int) $this->getNumberOfError() + 1;

        $last_error = $this->formatDate(null, 'y-MM-dd HH:mm:ss');
        $this->setNumberOfError($nbr)->setLastError($last_error)->save();
        return $this;
    }

    public function save() {

        if($this->getCustomerCardId() == 0) $this->setCustomerCardId(null)->setId(null);
        parent::save();
    }

}