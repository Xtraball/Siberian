<?php
/** Set environment to current one. */
$le_env = System_Model_Config::getValueFor("letsencrypt_env");

$ssl_certificates_model = new System_Model_SslCertificates();
$all_certifs = $ssl_certificates_model->findAll();

foreach($all_certifs as $certif) {
    $env = $certif->getEnvironment();
    if(empty($env)) {
        $certif
            ->setEnvironment($le_env)
            ->setRenewDate(time_to_date(time()+10, "YYYY-MM-dd HH:mm:ss"))
            ->save()
        ;
    }
    
}