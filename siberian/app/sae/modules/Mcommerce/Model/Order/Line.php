<?php

class Mcommerce_Model_Order_Line extends Core_Model_Default {

    /**
     * @var array Options de la ligne
     */
    protected $_options;

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Mcommerce_Model_Db_Table_Order_Line';
        return $this;
    }

    public function getQty() {
        $qty = 0;
        if($this->getData('qty')) {
            $qty = round($this->getData('qty'));
        }

        return $qty;
    }

    public function getChoices() {

        if(!$this->_choices) {
            $this->_choices = @unserialize($this->getData('choices'));
        }

        return $this->_choices;
    }

    public function getOptions() {

        if(!$this->_options) {
            $this->_options = array();
            if($this->getData('options')) {
                $options = @unserialize($this->getData('options'));
                if(is_array($options)) {
                    foreach($options as $option_datas) {
                        $this->_options[] = new Core_Model_Default($option_datas);
                    }
                }
            }
        }

        return $this->_options;
    }

}
