<?php

class System_Model_SslCertificates extends Core_Model_Default {

    const SOURCE_CUSTOMER = "customer";
    const SOURCE_LETSENCRYPT = "letsencrypt";

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'System_Model_Db_Table_SslCertificates';
        return $this;
    }

    /**
     * Extract certificate information
     *
     * @return array
     */
    public function extractInformation() {

        try {

            $certificate = openssl_x509_parse(file_get_contents($this->getCertificate()));

            if(!isset($certificate["issuer"]) && !isset($certificate["issuer"]["CN"])) {
                throw new Siberian_Exception(__("Unable to parse certificate."));
            }

            $data = array(
                "issuer"        => $certificate["issuer"]["CN"],
                "valid_until"   => datetime_to_format(date("Y-m-d H:i:s", $certificate["validTo_time_t"])),
                "is_valid"      => ($certificate["validTo_time_t"] > time())
            );

        } catch(Exception $e) {

            $data = array(
                "issuer"        => "-",
                "valid_until"   => "-",
                "is_valid"      => false,
                "message"       => $e->getMessage()
            );
        }

        return $data;
    }

}
