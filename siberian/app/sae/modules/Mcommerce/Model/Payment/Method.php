<?php

/**
 * Class Mcommerce_Model_Payment_Method
 *
 * @method integer getId()
 * @method string getCode()
 * @method string getToken()
 * @method float getTotal()
 * @method string getCurrency()
 */
class Mcommerce_Model_Payment_Method extends Core_Model_Default {

    /**
     * @var $this
     */
    protected $_instance;

    /**
     * Mcommerce_Model_Payment_Method constructor.
     * @param array $params
     */
    public function __construct($params = []) {
        parent::__construct($params);
        $this->_db_table = 'Mcommerce_Model_Db_Table_Payment_Method';
        return $this;
    }

    /**
     * @return Mcommerce_Model_Db_Table_Payment_Method
     */
    public function getTable() {
        return parent::getTable();
    }

    /**
     * @param null $id
     * @return mixed
     */
    public function findByStore($id = null) {
        if (!$id) {
            $id = -1;
        }

        return $this->getTable()->findByStore($id);
    }

    /**
     * @param integer $storeId
     * @param array $methodDatas
     * @return $this
     */
    public function saveStoreDatas($storeId, $methodDatas) {

        $this->getTable()->saveStoreDatas($storeId, $methodDatas);
        foreach ($methodDatas as $methodData) {
            $instance = $this->find($methodData['method_id'])
                ->setStoreId($storeId)->getInstance();
            $methodData['store_id'] = $storeId;
            $instance->setData($methodData)->save();
            $this->_instance = null;
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getName() {
        return __($this->getData('name'));
    }

    /**
     * @return mixed
     */
    public function getInstance() {
        if (!$this->_instance) {
            $class = sprintf('%s_%s', get_class($this), ucfirst(str_replace('_', '', $this->getCode())));
            try {
                if (!@class_exists($class)) {
                    $class = get_class($this) . '_Default';
                }
            } catch (Exception $ex) {
                $class = get_class($this) . '_Default';
            }

            $this->_instance = new $class();
            $this->_instance->setMethod($this);
        }

        return $this->_instance;
    }

    /**
     * @param integer|null $value_id
     * @return mixed
     */
    public function getUrl($value_id = null) {
        return $this->getInstance()->getUrl($value_id);
    }

    /**
     * @param integer|null $value_id
     * @return mixed
     */
    public function getFormUrl($value_id = null) {
        return $this->getInstance()->getFormUrl($value_id);
    }

    /**
     * @return mixed
     */
    public function isOnline() {
        return $this->getInstance()->isOnline();
    }

    /**
     * @return mixed
     */
    public function isCurrencySupported() {
        return true;
        //return $this->getInstance()->isCurrencySupported();
    }

    /**
     * @return mixed
     */
    public function pay() {
        return $this->getInstance()->pay();
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function saveCardAndPay($data) {
        return $this->getInstance()->saveCardAndPay($data);
    }

    /**
     * @param string $chargeData
     * @return mixed
     */
    public function payByCustomerToken($chargeData) {
        return $this->getInstance()->payByCustomerToken($chargeData);
    }

}
