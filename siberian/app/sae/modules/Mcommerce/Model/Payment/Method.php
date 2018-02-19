<?php

/**
 * Class Mcommerce_Model_Payment_Method
 *
 * @method integer getId()
 * @method string getCode()
 * @method string getToken()
 * @method float getTotal()
 * @method string getCurrency()
 * @method Mcommerce_Model_Cart getCart()
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
     * Transfer down params to method!
     *
     * @param $params
     * @return $this
     */
    public function setParams($params) {
        $this->getInstance()->setParams($params);

        return $this;
    }

    /**
     * @return mixed
     */
    public function isCurrencySupported() {
        return $this->getInstance()->isCurrencySupported();
    }

    /**
     * @return mixed
     */
    public function currencySupportApp() {
        return $this->getInstance()->currencySupportApp();
    }

    /**
     * @return mixed
     */
    public function currencyShortName() {
        return $this->getInstance()->currencyShortName();
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

    /**
     * @param $valueId
     * @return array
     */
    public function getFormUris ($valueId) {
        return [
            'url' => null,
            'form_url' => null
        ];
    }
}
