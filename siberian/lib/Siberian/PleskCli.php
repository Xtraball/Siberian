<?php

namespace Siberian;

use Zend_Registry;
use Api_Model_Key;

/**
 * Class PleskCli
 * @package Siberian
 *
 * @replay 4.19.0
 */
class PleskCli
{

    /**
     * @var Api
     */
    protected $api;

    /**
     * @var mixed|null
     */
    public $logger = null;

    /**
     * @var array
     */
    protected $config = [
        'ip' => null,
        'webspace' => null,
    ];

    /**
     * PleskCli constructor.
     * @throws \Zend_Exception
     */
    public function __construct()
    {
        $this->logger = Zend_Registry::get('logger');
        $pleskCli = Api_Model_Key::findKeysFor('pleskcli');

        $this->config['ip'] = $pleskCli->getIp();
        $this->config['webspace'] = $pleskCli->getWebspace();
    }

    /**
     * @param $sslCertificate
     * @return bool
     * @throws Exception
     * @throws \Zend_Exception
     */
    public function installCertificate($sslCertificate): bool
    {
        $base = path('/var/apps/certificates/');
        $ip = $this->config['ip'];
        $webspace = $this->config['webspace'];
        $folder = $base . '/' . $sslCertificate->getHostname();

        // Adding current time to the name, so we only ever create certs!
        $certName = 'siberian_pleskcli_' . $this->config['webspace'] . '_' . time();

        // Create the cert!
        $cmdParts = [
            "plesk bin certificate",
            "-c '{$certName}'",
            "-domain {$webspace}",
            "-key-file {$folder}/acme.privkey.pem",
            "-cert-file {$folder}/acme.cert.pem",
            "-cacert-file {$folder}/acme.chain.pem",
        ];
        exec(implode_polyfill(' ', $cmdParts), $result, $return);
        if ((int) $return !== 0) {
            throw new Exception(__('[Error SSL] PleskBin returned error code %s, and message %s',
                $return, implode_polyfill(', ', $result)));
        }

        // Install cert on webspace!
        $cmdParts = [
            "plesk bin site",
            "-u {$webspace}",
            "-certificate-name {$certName}",
        ];
        exec(implode_polyfill(' ', $cmdParts), $result, $return);
        if ((int) $return !== 0) {
            throw new Exception(__('[Error SSL] PleskBin returned error code %s, and message %s',
                $return, implode_polyfill(', ', $result)));
        }

        // Create on default!
        $cmdParts = [
            "plesk bin certificate",
            "-c '{$certName}'",
            "-admin ",
            "-key-file {$folder}/acme.privkey.pem",
            "-cert-file {$folder}/acme.cert.pem",
            "-cacert-file {$folder}/acme.chain.pem",
        ];
        exec(implode_polyfill(' ', $cmdParts), $result, $return);
        if ((int) $return !== 0) {
            throw new Exception(__('[Error SSL] PleskBin returned error code %s, and message %s',
                $return, implode_polyfill(', ', $result)));
        }

        // Install cert on default!
        $cmdParts = [
            "plesk bin ipmanage",
            "-u {$ip}",
            "-ssl_certificate {$certName}",
        ];
        exec(implode_polyfill(' ', $cmdParts), $result, $return);
        if ((int) $return !== 0) {
            throw new Exception(__('[Error SSL] PleskBin returned error code %s, and message %s',
                $return, implode_polyfill(', ', $result)));
        }
    }
}
