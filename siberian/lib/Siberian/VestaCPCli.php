<?php

namespace Siberian;

use Zend_Registry;
use Api_Model_Key;

/**
 * Class VestaCPCli
 * @package Siberian
 *
 * @replay 4.18.15
 */
class VestaCPCli
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
        'username' => null,
        'webspace' => null,
    ];

    /**
     * VestaCP constructor.
     * @throws Exception
     * @throws \Zend_Exception
     */
    public function __construct()
    {
        $this->logger = Zend_Registry::get('logger');
        $vestacpApi = Api_Model_Key::findKeysFor('vestacpcli');

        $this->config['username'] = $vestacpApi->getUser();
        $this->config['webspace'] = $vestacpApi->getWebspace();
    }

    /**
     * @param $sslCertificate
     * @return bool
     * @throws Exception
     */
    public function installCertificate($sslCertificate): bool
    {
        $base = path('/var/apps/certificates/');
        $folder = $base . '/' . $sslCertificate->getHostname();
        // Coy the files
        copy($folder . '/acme.cert.pem', $folder . '/' . $this->config['webspace'] . '.crt');
        copy($folder . '/acme.privkey.pem', $folder . '/' . $this->config['webspace'] . '.key');
        copy($folder . '/acme.chain.pem', $folder . '/' . $this->config['webspace'] . '.ca');

        $installCmd = "export VESTA=/usr/local/vesta/; sudo /usr/local/vesta/bin/v-add-web-domain-ssl {$this->config['username']} {$this->config['webspace']} $folder restart 2>&1";
        exec($installCmd, $result, $return);
        if ((int) $return === 0) {
            return true;
        }

        if ((int) $return === 4) {
            $updateCmd = "export VESTA=/usr/local/vesta/; sudo /usr/local/vesta/bin/v-update-web-domain-ssl {$this->config['username']} {$this->config['webspace']} $folder restart 2>&1";
            exec($updateCmd, $result, $return);
            if ((int) $return === 0) {
                return true;
            }
        }
        throw new Exception(__('Error SSL : Vesta API Query returned error code %s, and message %s',
            $return, join(', ', $result)));
    }
}
