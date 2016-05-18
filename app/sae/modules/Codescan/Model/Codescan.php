<?php
class Codescan_Model_Codescan extends Core_Model_Default {

	protected $_is_cachable = false;
	
    public function __construct($params = array()) {
        parent::__construct($params);
        return $this;
    }

}
