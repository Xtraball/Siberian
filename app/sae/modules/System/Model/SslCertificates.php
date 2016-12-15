<?php

class System_Model_SslCertificates extends Core_Model_Default {

    const SOURCE_CUSTOMER = "customer";
    const SOURCE_LETSENCRYPT = "letsencrypt";

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'System_Model_Db_Table_SslCertificates';
        return $this;
    }

}
