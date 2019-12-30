<?php

use Siberian\ACME\Cert;
use Siberian\File;

/**
 * Class Backoffice_Advanced_ConfigurationController
 */
class Backoffice_Advanced_ConfigurationController extends System_Controller_Backoffice_Default
{
    /**
     * @var array
     */
    public $_codes = [
        'disable_cron',
        'cron_interval',
        'environment',
        'update_channel',
        'cpanel_type',
        'letsencrypt_env',
        'send_statistics',
        'session_handler',
        'redis_endpoint',
        'redis_prefix',
        'redis_auth',
        'apk_build_type',
        'java_home',
        'java_options',
        'gradle_options',
    ];

    /**
     * @var string
     */
    public $_fake_password_key = '__not_safe_not_saved__';

    /**
     *
     */
    public function loadAction()
    {
        $data = [
            'title' => sprintf('%s > %s > %s',
                __('Settings'),
                __('Advanced'),
                __('Configuration')),
            'icon' => 'fa-toggle-on',
        ];

        $this->_sendJson($data);
    }

    /**
     *
     */
    public function findallAction()
    {
        $data = $this->_findconfig();

        $cpanel = Api_Model_Key::findKeysFor("cpanel");
        $plesk = Api_Model_Key::findKeysFor("plesk");
        $vestacp = Api_Model_Key::findKeysFor("vestacp");
        $directadmin = Api_Model_Key::findKeysFor("directadmin");

        $data["cpanel"] = $cpanel->getData();
        $data["plesk"] = $plesk->getData();
        $data["vestacp"] = $vestacp->getData();
        $data["directadmin"] = $directadmin->getData();

        $data["cpanel"]["password"] = $this->_fake_password_key;
        $data["plesk"]["password"] = $this->_fake_password_key;
        $data["vestacp"]["password"] = $this->_fake_password_key;
        $data["directadmin"]["password"] = $this->_fake_password_key;

        $ssl_certificate_model = new System_Model_SslCertificates();
        $certs = $ssl_certificate_model->findAll();

        $result = Siberian_Network::testSsl($this->getRequest()->getHttpHost(), true);
        $data["testssl"] = $result;

        $is_pe = Siberian_Version::is("PE");
        if ($is_pe) {
            $whitelabel_model = new Whitelabel_Model_Editor();
        }

        $data["current_domain"] = $this->getRequest()->getHttpHost();

        $data["certificates"] = [];
        foreach ($certs as $cert) {
            $wls = [];
            if ($is_pe) {
                $whitelabels = $whitelabel_model->findAll(["is_active = ?", "1"]);
                foreach ($whitelabels as $whitelabel) {
                    $wls[] = $whitelabel->getHost();
                }
            }

            $cert_data = [
                "id" => $cert->getId(),
                "whitelabels" => $wls,
                "domains" => Siberian_Json::decode($cert->getDomains()),
                "hostname" => $cert->getHostname(),
                "certificate" => $cert->getCertificate(),
                "chain" => $cert->getChain(),
                "fullchain" => $cert->getFullchain(),
                "last" => $cert->getLast(),
                "private" => $cert->getPrivate(),
                "public" => $cert->getPublic(),
                "source" => __($cert->getSource()),
                "created_at" => datetime_to_format($cert->getCreatedAt()),
                "updated_at" => datetime_to_format($cert->getUpdatedAt()),
                "show_info" => false,
                "more_info" => __("-")
            ];

            $cert_data = array_merge($cert_data, $cert->extractInformation());

            $data["certificates"][] = $cert_data;
        }

        $this->_sendJson($data);
    }

    /**
     *
     */
    public function submitreportAction()
    {
        try {
            $request = $this->getRequest();

            if ($params = Siberian_Json::decode($request->getRawBody())) {

                ob_start();
                phpinfo();
                $phpinfo = ob_get_clean();

                $bug_report = [
                    "secret" => Core_Model_Secret::SECRET,
                    "data" => [
                        "host" => $request->getHttpHost(),
                        "type" => Siberian_Version::TYPE,
                        "version" => Siberian_Version::VERSION,
                        "canal" => System_Model_Config::getValueFor("update_channel"),
                        "message" => base64_encode($params["message"]),
                        "phpinfo" => base64_encode($phpinfo)
                    ]
                ];

                $request = new Siberian_Request();
                $request->post(sprintf("http://stats.xtraball.com/report.php?type=%s", Siberian_Version::TYPE), $bug_report);

                $payload = [
                    "success" => true,
                    "message" => __("Thanks for your report."),
                ];

            } else {
                throw new Siberian_Exception(__("Message is required."));
            }

        } catch (Exception $e) {
            $payload = [
                "error" => true,
                "message" => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }

    /**
     * Save settings
     * &
     * cpanel/plesk credentials
     */
    public function saveAction()
    {
        if ($data = Zend_Json::decode($this->getRequest()->getRawBody())) {

            try {
                if (__getConfig('is_demo')) {
                    throw new Exception(__("This is a demo version, you cannot save these settings."));
                }

                $this->_save($data);

                $message = __("Configuration successfully saved");

                if (isset($data['environment']) && in_array($data['environment']['value'], ['production', 'development'])) {
                    $config_file = Core_Model_Directory::getBasePathTo('config.php');
                    if (is_writable($config_file)) {
                        $contents = file_get_contents($config_file);
                        $contents = preg_replace('/("|\')(development|production)("|\')/im', '"' . $data["environment"]["value"] . '"', $contents);
                        File::putContents($config_file, $contents);
                    } else {
                        $message = __("Configuration partially saved") . "<br />" . __("Error: unable to write Environment in config.php");
                    }
                }

                //Admin panel type & credentials
                $api_provider = new Api_Model_Provider();
                $api_key = new Api_Model_Key();

                $panel_type = $data["cpanel_type"]["value"];
                $panel_api_provider = $api_provider->find($panel_type, "code");
                if ($panel_api_provider->getId()) {
                    $keys = $api_key->findAll(["provider_id = ?" => $panel_api_provider->getId()]);
                    foreach ($keys as $key) {
                        switch ($key->getKey()) {
                            case "host":
                                $key->setValue($data[$panel_type]["host"])->save();
                                break;
                            case "user":
                                $key->setValue($data[$panel_type]["user"])->save();
                                break;
                            case "webspace":
                                $key->setValue($data[$panel_type]["webspace"])->save();
                                break;
                            case "password":
                                if ($data[$panel_type]["password"] != $this->_fake_password_key) {
                                    $key->setValue($data[$panel_type]["password"])->save();
                                }
                                break;
                        }
                    }
                }

                $data = [
                    "success" => 1,
                    "message" => $message,
                ];
            } catch (Exception $e) {
                $data = [
                    "error" => 1,
                    "message" => $e->getMessage(),
                ];
            }

            $this->_sendHtml($data);

        }

    }

    /**
     * Delete a certificate
     */
    public function removecertAction()
    {
        if ($cert_id = $this->getRequest()->getParam("cert_id")) {
            try {

                $ssl_certificate_model = new System_Model_SslCertificates();

                $cert = $ssl_certificate_model->find($cert_id);

                // Prevent some certificates from being un-intentionally removed!
                if (__getConfig('is_demo') && in_array($cert->getHostname(), __getConfig('hostname'))) {
                    throw new Siberian_Exception('This certificate is protected.');
                }

                $cert->delete();

                # Clean-up related CRON Alerts
                $backoffice_notification = new Backoffice_Model_Notification();
                $backoffice_notification::clear("System_Model_SslCertificates", $cert_id);

                $certs = $ssl_certificate_model->findAll();

                $data = [
                    "success" => 1,
                    "message" => __("Successfully removed the certificate."),
                ];

                $data["certificates"] = [];
                foreach ($certs as $cert) {
                    $data["certificates"][] = [
                        "id" => $cert->getId(),
                        "hostname" => $cert->getHostname(),
                        "certificate" => $cert->getCertificate(),
                        "chain" => $cert->getChain(),
                        "fullchain" => $cert->getFullchain(),
                        "last" => $cert->getLast(),
                        "private" => $cert->getPrivate(),
                        "public" => $cert->getPublic(),
                        "source" => __($cert->getSource()),
                        "created_at" => $cert->getFormattedCreatedAt(),
                        "updated_at" => $cert->getFormattedUpdatedAt(),
                        "show_info" => false,
                        "more_info" => __("-"),
                    ];
                }

            } catch (Exception $e) {
                $data = [
                    "error" => 1,
                    "message" => $e->getMessage(),
                ];
            }

            $this->_sendJson($data);
        }
    }

    public function createcertificateAction()
    {

        if ($data = Zend_Json::decode($this->getRequest()->getRawBody())) {

            try {
                if (empty($data["hostname"]) || empty($data["cert_path"]) || empty($data["private_path"])) {
                    throw new Exception("#824-01: " . __("All the files are required."));
                }

                $base = Core_Model_Directory::getBasePathTo("/var/apps/certificates/");

                # Test hostname
                $current_host = $this->getRequest()->getHttpHost();
                $current_ip = gethostbyname($current_host);
                $ip = gethostbyname($data["hostname"]);
                $letsencrypt_env = System_Model_Config::getValueFor("letsencrypt_env");

                if ($current_ip != $ip) {
                    throw new Exception("#824-02: " . __("The domain %s doesn't belong to you, or is not configured on your server.", $data["hostname"]));
                }

                $ssl_certificate_model = new System_Model_SslCertificates();
                $ssl_cert = $ssl_certificate_model->find($data["hostname"], "hostname");

                if ($ssl_cert->getId()) {
                    throw new Exception("#824-03: " . __("An entry already exists for this hostname, remove it first if you need to change your certificates."));
                }

                if ($data["upload"] == "1") {
                    # Move the TMP files, then save the certificate
                    $folder = $base . "/" . $data["hostname"];
                    if (!file_exists($folder)) {
                        mkdir($folder, 0777, true);
                    }

                    rename($data["cert_path"], $folder . "/cert.pem");
                    rename($data["private_path"], $folder . "/private.pem");
                    rename($data["fullchain_path"], $folder . "/fullchain.pem");

                    # Read SAN
                    $cert_content = openssl_x509_parse(file_get_contents($folder . "/cert.pem"));
                    if (isset($cert_content["extensions"]) && $cert_content["extensions"]["subjectAltName"]) {
                        $parts = explode(",", str_replace("DNS:", "", $cert_content["extensions"]["subjectAltName"]));
                        $certificate_hosts = [];
                        foreach ($parts as $part) {
                            $certificate_hosts[] = trim($part);
                        }
                    } else {
                        $certificate_hosts = [$data["hostname"]];
                    }

                    if (empty($certificate_hosts)) {
                        $certificate_hosts = [$data["hostname"]];
                    }

                    $ssl_cert
                        ->setHostname($data["hostname"])
                        ->setCertificate($folder . "/cert.pem")
                        ->setPrivate($folder . "/private.pem")
                        ->setFullchain($folder . "/fullchain.pem")
                        ->setDomains(Siberian_Json::encode($certificate_hosts))
                        ->setEnvironment($letsencrypt_env)
                        ->setSource(System_Model_SslCertificates::SOURCE_CUSTOMER);

                    if (is_readable($data["ca_path"])) {
                        rename($data["ca_path"], $folder . "/ca.pem");
                        $ssl_cert->setChain($folder . "/ca.pem");
                    }

                    $ssl_cert->save();

                } else {
                    # Test if all three paths are readable from Siberian !!!
                    if (!is_readable($data["cert_path"]) || !is_readable($data["private_path"])) {
                        throw new Exception("#824-06: " . __("One of the three given Certificates path is not readable, please make sure they have the good rights."));
                    }

                    # Read SAN
                    $cert_content = openssl_x509_parse(file_get_contents($data["cert_path"]));
                    if (isset($cert_content["extensions"]) && $cert_content["extensions"]["subjectAltName"]) {
                        $parts = explode(",", str_replace("DNS:", "", $cert_content["extensions"]["subjectAltName"]));
                        $certificate_hosts = [];
                        foreach ($parts as $part) {
                            $certificate_hosts[] = trim($part);
                        }
                    } else {
                        $certificate_hosts = [$data["hostname"]];
                    }

                    if (empty($certificate_hosts)) {
                        $certificate_hosts = [$data["hostname"]];
                    }

                    # Save the path as-is
                    $ssl_cert
                        ->setHostname($data["hostname"])
                        ->setCertificate($data["cert_path"])
                        ->setChain($data["ca_path"])
                        ->setFullchain($data["ca_path"])
                        ->setPrivate($data["fullchain_path"])
                        ->setDomains(Siberian_Json::encode($certificate_hosts))
                        ->setSource(System_Model_SslCertificates::SOURCE_CUSTOMER)
                        ->setEnvironment($letsencrypt_env)
                        ->save();
                }

                // SocketIO
                if (class_exists("SocketIO_Model_SocketIO_Module") && method_exists("SocketIO_Model_SocketIO_Module", "killServer")) {
                    SocketIO_Model_SocketIO_Module::killServer();
                }

                $certs = $ssl_certificate_model->findAll();

                $data = [
                    "success" => true,
                    "message" => __("Your certificates are saved."),
                ];

                $data["certificates"] = [];
                foreach ($certs as $cert) {
                    $data["certificates"][] = [
                        "id" => $cert->getId(),
                        "hostname" => $cert->getHostname(),
                        "certificate" => $cert->getCertificate(),
                        "chain" => $cert->getChain(),
                        "fullchain" => $cert->getFullchain(),
                        "last" => $cert->getLast(),
                        "private" => $cert->getPrivate(),
                        "public" => $cert->getPublic(),
                        "source" => __($cert->getSource()),
                        "created_at" => $cert->getFormattedCreatedAt(),
                        "updated_at" => $cert->getFormattedUpdatedAt(),
                        "show_info" => false,
                        "more_info" => __("-"),
                    ];
                }

            } catch (Exception $e) {
                $data = [
                    "error" => true,
                    "message" => $e->getMessage(),
                ];
            }

            $this->_sendJson($data);

        }

    }

    /**
     * Test the current hostname SSL connection
     */
    public function testsslAction()
    {
        $result = Siberian_Network::testSsl($this->getRequest()->getHttpHost());

        $this->_sendJson($result);
    }

    /**
     * Warning about SSL Expiration in the backoffice dashboard
     */
    public function sslwarningAction()
    {
        $result = Siberian_Network::testSsl($this->getRequest()->getHttpHost());

        if (isset($result['success'])) {
            $rawData = $result['rawData'];
            $remain = $rawData['validTo_time_t'] - time();
            $remainInDays = floor($remain / 86400);
            $showWarning = (boolean)($remainInDays <= 15);
            $validUntil = datetime_to_format(date("Y-m-d H:i:s", $rawData['validTo_time_t']));
            $message = __("Your HTTPS/SSL Certificate is about to expire in <b>%s day%s</b>, don't forget to renew it on the following page <a href=\"%s\">configuration</a>.",
                $remainInDays,
                $remainInDays === 1 ? '' : 's',
                '/backoffice/advanced_configuration');

            $result['sslData'] = [
                'remainInDays' => $remainInDays,
                'showWarning' => $showWarning,
                'validUntil' => $validUntil,
                'message' => $message,
                'useHttps' => true,
            ];
        }

        $this->_sendJson($result);
    }

    /**
     * Simple action to check if host is ok (with json response)
     */
    public function checkhttpAction()
    {
        $data = [
            "success" => true,
            "message" => __("Success"),
        ];

        $this->_sendJson($data);
    }

    /**
     * Simple action to check if host is ok (with json response)
     */
    public function checksslAction()
    {
        $http_host = $this->getRequest()->getHttpHost();
        $request = new Siberian_Request();
        $result = $request->get("https://" . $http_host);
        if ($result) {
            $data = [
                "success" => true,
                "message" => __("Success"),
                "https_url" => "https://" . $http_host,
                "http_url" => "http://" . $http_host,
            ];
        } else {
            $data = [
                "error" => true,
                "message" => __("HTTPS not reachable"),
                "https_url" => "https://" . $http_host,
                "http_url" => "http://" . $http_host,
            ];
        }

        $this->_sendJson($data);
    }

    /**
     * Yes ....
     */
    public function clearpleskAction()
    {
        try {
            $logger = Zend_Registry::get("logger");
            $hostname = $this->getRequest()->getParam("hostname", $this->getRequest()->getHttpHost());
            $ssl_certificate_model = new System_Model_SslCertificates();
            $cert = $ssl_certificate_model->find($hostname, "hostname");

            $siberian_plesk = new Siberian_Plesk();
            $siberian_plesk->removeCertificate($cert);

            $data = [
                "success" => 1,
                "message" => "#824-56: " . __("Successfully cleaned-up old certificate."),
            ];
        } catch (Exception $e) {
            $logger->info("[clearpleskAction]: An error occured %s", $e->getMessage());

            $data = [
                "error" => 1,
                "message" => "#824-55: " . __("[Plesk] %s", $e->getMessage()),
            ];
        }

        $this->_sendJson($data);
    }

    /**
     * Yes ....
     */
    public function installpleskAction()
    {
        try {
            $logger = Zend_Registry::get("logger");
            $hostname = $this->getRequest()->getParam("hostname", $this->getRequest()->getHttpHost());
            $ssl_certificate_model = new System_Model_SslCertificates();
            $cert = $ssl_certificate_model->find($hostname, "hostname");

            $siberian_plesk = new Siberian_Plesk();
            $siberian_plesk->updateCertificate($cert);

            $data = [
                "success" => 1,
                "message" => "#824-56" . __("Successfully installed new certificate."),
            ];
        } catch (Exception $e) {
            $logger->info("[clearpleskAction]: An error occured %s", $e->getMessage());

            $data = [
                "error" => 1,
                "message" => "#824-59: " . __("[Plesk] %s", $e->getMessage()),
            ];
        }

        $this->_sendJson($data);
    }

    /**
     * This action may end unexpectedly because of the panel reloading the webserver
     * This is normal behavior
     */
    public function sendtopanelAction()
    {

        $logger = Zend_Registry::get("logger");
        $panel_type = __get("cpanel_type");
        $hostname = $this->getRequest()->getParam("hostname", $this->getRequest()->getHttpHost());

        $ssl_certificate_model = new System_Model_SslCertificates();
        $cert = $ssl_certificate_model->find($hostname, "hostname");

        $ui_panels = [
            "plesk" => __("Plesk"),
            "cpanel" => __("WHM cPanel"),
            "vestacp" => __("VestaCP"),
            "directadmin" => __("DirectAdmin"),
            "self" => __("Self-managed"),
        ];

        // Sync cPanel - Plesk - VestaCP (beta) - DirectAdmin (beta)
        try {
            $message = __("Successfully saved Certificate to %s", $ui_panels["$panel_type"]);
            switch ($panel_type) {
                case "plesk":
                    $siberian_plesk = new Siberian_Plesk();
                    $siberian_plesk->selectCertificate($cert);

                    break;
                case "cpanel":
                    $cpanel = new Siberian_Cpanel();
                    $cpanel->updateCertificate($cert);

                    break;
                case "vestacp":
                    $vestacp = new Siberian_VestaCP();
                    $vestacp->updateCertificate($cert);

                    break;
                case "directadmin":
                    $directadmin = new Siberian_DirectAdmin();
                    $directadmin->updateCertificate($cert);

                    break;
                case "self":
                    $logger->info(__("Self-managed sync is not available for now."));
                    $message = __("Self-managed sync is not available for now.");

                    break;
            }

            $data = [
                "success" => 1,
                "message" => $message,
            ];

        } catch (Exception $e) {
            $logger->info("#824-50: " . __("An error occured while saving certificate to %s.", $e->getMessage()));
            $data = [
                "error" => 1,
                "message" => $e->getMessage(),
            ];
        }

        $this->_sendJson($data);
    }

    /**
     * Generate SSL from Let's Encrypt, then sync Admin Panel
     */
    public function generatesslAction()
    {
        $ui_panels = [
            "plesk" => __("Plesk"),
            "cpanel" => __("WHM cPanel"),
            "vestacp" => __("VestaCP"),
            "directadmin" => __("DirectAdmin"),
            "self" => __("Self-managed"),
        ];

        $logger = Zend_Registry::get("logger");

        try {

            $letsencrypt_env = __get("letsencrypt_env");

            $letsencrypt_disabled = __get("letsencrypt_disabled");
            if (($letsencrypt_disabled > time()) && ($letsencrypt_env === "production")) {
                $logger->info(__("[Let's Encrypt] cron renewal is disabled until %s due to rate limit hit, skipping.", date("d/m/Y H:i:s", $letsencrypt_disabled)));

                throw new Siberian_Exception(__("Certificate renewal is disabled until %s due to rate limit hit.", datetime_to_format(date("Y-M-d H:i:s", $letsencrypt_disabled))));

            } else {

                # Enabling back
                __set("letsencrypt_disabled", 0);
            }

            // Check panel type
            $panel_type = __get("cpanel_type");
            if ($panel_type == "-1") {
                throw new Siberian_Exception(__("You must select an Admin panel type before requesting a Certificate."));
            }

            $request = $this->getRequest();
            $email = __get("support_email");
            $show_force = false;

            $root = path("/");
            $base = path("/var/apps/certificates/");
            $hostname = $request->getParam("hostname", $request->getHttpHost());

            // Build hostname list
            $hostnames = [$hostname];

            // Add whitelabels if PE
            $is_pe = Siberian_Version::is('PE');
            if ($is_pe) {
                $whitelabel_model = new Whitelabel_Model_Editor();
                $whitelabels = $whitelabel_model->findAll(["is_active = ?", "1"]);

                foreach ($whitelabels as $_whitelabel) {
                    $whitelabel = trim($_whitelabel->getHost());

                    $endWithDot = preg_match("/.*\.$/im", $whitelabel);
                    $r = dns_get_record($whitelabel, DNS_CNAME);
                    $isCname = (!empty($r) && isset($r[0]) && isset($r[0]["target"]) && ($r[0]["target"] == $hostname));
                    $isSelf = ($whitelabel == $hostname);

                    if (!$endWithDot && ($isCname || $isSelf) && $_whitelabel->getIsActive()) {
                        $logger->info(__("Adding %s to SAN.", $whitelabel));

                        $hostnames[] = $whitelabel;
                    }

                    if ($endWithDot) {
                        $logger->info(__('Removing domain %s, domain in dot notation is not supported.', $whitelabel));
                    }
                }
            }

            // Ensure folders have good rights
            exec("chmod -R 775 {$base}");
            exec("chmod -R 777 {$root}/.well-known");

            $acme = new Cert($letsencrypt_env !== 'staging');

            try {
                $acme->getAccount();
            } catch (\Exception $e) {
                $acme->register(true, $email);
            }

            $ssl_certificate_model = new System_Model_SslCertificates();
            $cert = $ssl_certificate_model->find($request->getHttpHost(), 'hostname');

            if (empty($hostnames)) {
                $hostnames = [$request->getHttpHost()];
            }

            // Remove temporary excluded domains from config.user.php
            $excludeDomainSsl = __getConfig('exclude_domain_ssl');
            if ($excludeDomainSsl && is_array($excludeDomainSsl)) {
                $hostnames = array_diff($hostnames, $excludeDomainSsl);
            }

            // Included domains from config.user.php
            $includeDomainSsl = __getConfig('include_domain_ssl');
            if ($includeDomainSsl && is_array($includeDomainSsl)) {
                foreach ($includeDomainSsl as $includeDomain) {
                    $hostnames[] = $includeDomain;
                }
            }

            // Truncate domains list to 100 (SAN is limited to 100 domains)
            $hostnames = array_slice(array_unique(array_filter($hostnames, 'strlen')), 0, 100);

            // Before generating certificate again, compare $hostnames OR expiration date
            $renew = false;
            if (is_readable($cert->getCertificate()) && !empty($hostnames)) {

                $cert_content = openssl_x509_parse(file_get_contents($cert->getCertificate()));

                if (isset($cert_content["extensions"]) && $cert_content["extensions"]["subjectAltName"]) {
                    $certificate_hosts = explode(",", str_replace("DNS:", "", $cert_content["extensions"]["subjectAltName"]));
                    foreach ($hostnames as $_hostname) {
                        $_hostname = trim($_hostname);
                        if (!in_array($_hostname, $certificate_hosts)) {
                            $renew = true;
                            $logger->info(__("[Let's Encrypt] will add %s to SAN.", $_hostname));
                        }
                    }
                }

                // Or compare expiration date (will expire in 30 days or less)
                if (!$renew) {

                    //$thirty_days = 2592000;
                    $eight_days = 691200;
                    //$five_days = 432000;

                    $diff = $cert_content["validTo_time_t"] - time();
                    if ($diff < $eight_days) {
                        # Should renew
                        $renew = true;
                        $logger->info(__("[Let's Encrypt] will expire in %s days.", floor($diff / 86400)));
                    }
                }

            } else {
                $renew = true;
            }

            // staging against production, renew if environment is different
            if ($cert->getEnvironment() != $letsencrypt_env) {
                $renew = true;
            }

            // Whether to renew or not the certificate
            if ($renew) {
                try {
                    $logger->info(__("[Let's Encrypt] renewing certificate."));

                    $docRoot = path("/");
                    $config = [
                        "challenge" => "http-01",
                        "docroot" => $docRoot,
                    ];

                    $domainConfig = [];
                    foreach ($hostnames as $_hostName) {
                        $domainConfig[$_hostName] = $config;
                    }

                    $handler = function($opts){
                        $fn = $opts["config"]["docroot"] . $opts["key"];
                        @mkdir(dirname($fn),0777,true);
                        file_put_contents($fn, $opts["value"]);
                        return function($opts){
                            unlink($opts["config"]["docroot"] . $opts["key"]);
                        };
                    };

                    // Ensure hostname folder exists
                    $hostnameDirectory = path("/var/apps/certificates/{$hostname}");
                    mkdir($hostnameDirectory,0777,true);

                    $fullChainPath = path("/var/apps/certificates/{$hostname}/acme.fullchain.pem");
                    $certKey = $acme->generateRSAKey(2048);
                    $certKeyPath = path("/var/apps/certificates/{$hostname}/acme.privkey.pem");
                    file_put_contents($certKeyPath, $certKey);

                    $fullChain = $acme->getCertificateChain("file://{$certKeyPath}", $domainConfig, $handler);
                    file_put_contents($fullChainPath, $fullChain);

                    // Split fullchain
                    $fullChainContent = file_get_contents($fullChainPath);
                    $parts = explode("\n\n", $fullChainContent);
                    $certPath = path("/var/apps/certificates/{$hostname}/acme.cert.pem");
                    $chainPath = path("/var/apps/certificates/{$hostname}/acme.chain.pem");
                    file_put_contents($certPath, $parts[0]);
                    file_put_contents($chainPath, $parts[1]);

                    $result = true;

                } catch (\Exception $e) {
                    // Simply throws back the exception, we must know!
                    throw $e;
                }

            } else {
                // Sync cert/panel
                $result = true;
            }

            if ($result) {

                $cert
                    ->setErrorCount(0)
                    ->setStatus("enabled")
                    ->setHostname($hostname)
                    ->setSource(System_Model_SslCertificates::SOURCE_LETSENCRYPT)
                    ->setCertificate(sprintf("%s%s/%s", $base, $hostname, "acme.cert.pem"))
                    ->setChain(sprintf("%s%s/%s", $base, $hostname, "acme.chain.pem"))
                    ->setFullchain(sprintf("%s%s/%s", $base, $hostname, "acme.fullchain.pem"))
                    ->setPrivate(sprintf("%s%s/%s", $base, $hostname, "acme.privkey.pem"))
                    ->setEnvironment($letsencrypt_env)
                    ->setRenewDate(time_to_date(time() + 10, "YYYY-MM-dd HH:mm:ss"))
                    ->setDomains(Siberian_Json::encode(array_values($hostnames), JSON_OBJECT_AS_ARRAY))
                    ->save();

                # On success -> clean-up related CRON Alerts
                $backoffice_notification = new Backoffice_Model_Notification();
                $backoffice_notification::clear("System_Model_SslCertificates", $cert->getId());

                // SocketIO
                if (class_exists("SocketIO_Model_SocketIO_Module")) {
                    SocketIO_Model_SocketIO_Module::killServer();
                }

                $message = __("Certificate successfully generated. Please wait while %s is reloading configuration ...", $ui_panels[$panel_type]);

            } else {
                $cert
                    ->setErrorCount($cert->getErrorCount() + 1)
                    ->setErrorDate(time_to_date(time(), "YYYY-MM-dd HH:mm:ss"))
                    ->setRenewDate(time_to_date(time() + 10, "YYYY-MM-dd HH:mm:ss"))
                    ->save();

                if ($cert->getErrorCount() >= 3) {
                    $cert
                        ->setStatus("disabled")
                        ->save();

                    $message = "#824-90: " . __("The certificate request failed 3 times, please check the certificate information, your panel credentials, and everything else.<br />If your certificate is valid, you can try to upload to panel only.");
                } else {

                    $message = "#824-07: " . __("An unknown error occurred while issue-ing your certificate.");
                }


            }

            // Ensure folders have good rights
            exec("chmod -R 775 {$base}");
            exec("chmod -R 777 {$root}/.well-known");

            $data = [
                "success" => 1,
                "show_force" => $show_force,
                "message" => $message,
                "all_messages" => [
                    "https_unreachable" => "#824-99: " . __("HTTPS host is unreachable."),
                    "polling_reload" => __("Waiting for %s to reload, this can take a while...", $panel_type),
                ],
            ];

        } catch (Exception $e) {

            if ((strpos($e->getMessage(), "many currently pending authorizations") !== false) ||
                (strpos($e->getMessage(), "many certificates already issued") !== false)) {
                # We hit the rate limit, disable for the next seven days
                $in_a_week = time() + 604800;
                System_Model_Config::setValueFor("letsencrypt_disabled", $in_a_week);
            }

            $data = [
                "error" => true,
                "message" => $e->getMessage(),
                "trace" => $e->getTraceAsString(),
            ];
        }

        $this->_sendJson($data);
    }

    /**
     *
     */
    public function uploadcertificateAction()
    {

        try {

            if (empty($_FILES) || empty($_FILES['file']['name'])) {
                throw new Exception(__("No file has been sent"));
            }

            $tmp_dir = Core_Model_Directory::getTmpDirectory(true);
            $adapter = new Zend_File_Transfer_Adapter_Http();
            $adapter->setDestination($tmp_dir);
            $code = $this->getRequest()->getParam("code");

            if ($adapter->receive()) {

                $file = $adapter->getFileInfo();

                $uuid = uniqid();
                $new_name = "{$uuid}.pem";
                rename($file["file"]["tmp_name"], $tmp_dir . "/" . $new_name);

                $data = [
                    "success" => 1,
                    "code" => $code,
                    "tmp_name" => $file["file"]["name"],
                    "tmp_path" => $tmp_dir . "/" . $new_name,
                ];

            } else {
                $messages = $adapter->getMessages();
                if (!empty($messages)) {
                    $message = implode("\n", $messages);
                } else {
                    $message = __("An error occurred during the process. Please try again later.");
                }

                throw new Exception($message);
            }
        } catch (Exception $e) {
            $data = [
                "error" => 1,
                "message" => $e->getMessage()
            ];
        }

        $this->_sendJson($data);
    }

    /**
     *
     */
    public function downloadcertAction()
    {
        $request = $this->getRequest();
        $cert_id = $request->getParam("cert_id");
        $type = $request->getParam("type");

        $ssl_certificate_model = new System_Model_SslCertificates();
        $cert = $ssl_certificate_model->find($cert_id);

        if ($cert) {
            switch ($type) {
                case "csr":
                    $name = sprintf("%s-%s", $cert->getHostname(), "last.csr");
                    $path = $cert->getLast();
                    break;
                case "cert":
                    $name = sprintf("%s-%s", $cert->getHostname(), "certificate.pem");
                    $path = $cert->getCertificate();
                    break;
                case "ca":
                    $name = sprintf("%s-%s", $cert->getHostname(), "ca.pem");
                    $path = $cert->getChain();
                    break;
                case "private":
                    $name = sprintf("%s-%s", $cert->getHostname(), "private.pem");
                    $path = $cert->getPrivate();
                    break;
            }

            $this->_download($path, $name, "application/octet-stream");
        }
    }

}
