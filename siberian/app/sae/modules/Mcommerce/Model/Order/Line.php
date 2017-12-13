<?php

/**
 * Class Mcommerce_Model_Order_Line
 *
 * @method bool getIsRecurrent()
 * @method string getName()
 * @method float getPriceExclTax()
 * @method float getTotalPriceExclTax()
 */
class Mcommerce_Model_Order_Line extends Core_Model_Default {

    /**
     * @var array Options de la ligne
     */
    protected $_options;

    /**
     * Mcommerce_Model_Order_Line constructor.
     * @param array $params
     */
    public function __construct($params = []) {
        parent::__construct($params);
        $this->_db_table = 'Mcommerce_Model_Db_Table_Order_Line';
        return $this;
    }

    /**
     * @return boolean
     */
    public function isRecurrent () {
        return (filter_var($this->getIsRecurrent(), FILTER_VALIDATE_BOOLEAN));
    }

    /**
     * @return float|int
     */
    public function getQty () {
        $qty = 0;
        if ($this->getData('qty')) {
            $qty = round($this->getData('qty'));
        }

        return $qty;
    }

    /**
     * @return mixed
     */
    public function getChoices () {
        if (!$this->_choices) {
            $this->_choices = @unserialize($this->getData('choices'));
        }

        return $this->_choices;
    }

    /**
     * @return array
     */
    public function getOptions () {
        if (!$this->_options) {
            $this->_options = [];
            if ($this->getData('options')) {
                $options = @unserialize($this->getData('options'));
                if (is_array($options)) {
                    foreach($options as $option_datas) {
                        $this->_options[] = new Core_Model_Default($option_datas);
                    }
                }
            }
        }

        return $this->_options;
    }

}
