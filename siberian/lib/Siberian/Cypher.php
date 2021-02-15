<?php

namespace Siberian;

use \phpseclib\Crypt\RSA as RSA;

/**
 * Class Cypher
 * @package Siberian
 */
class Cypher
{
    /**
     * @var null
     */
    public static $lastTmp = null;

    /**
     * @param $publicKeyPath
     * @param $clear
     * @return string
     */
    public static function cypher ($publicKeyPath, $clear)
    {
        $publicKey = file_get_contents($publicKeyPath);

        $rsa = new RSA();
        $rsa->loadKey($publicKey);
        $rsa->setEncryptionMode(RSA::ENCRYPTION_PKCS1);
        $base64cypher = base64_encode($rsa->encrypt($clear));

        return $base64cypher;
    }

    /**
     * @param $privateKeyPath
     * @param $cyphered
     * @param null $passphrase
     * @return string
     */
    public static function decypher ($privateKeyPath, $cyphered, $passphrase = null)
    {
        $privateKey = new RSA();
        if ($passphrase !== null) {
            $privateKey->setPassword($passphrase);
        }
        $privateKey->loadKey(file_get_contents($privateKeyPath));
        $privateKey->setEncryptionMode(RSA::ENCRYPTION_PKCS1);

        return $privateKey->decrypt(base64_decode($cyphered));
    }

    /**
     * @version 4.19.9 - Moves eval from zip packages to Siberian core to prevent AV false positives!
     *
     * @param $file
     * @param $module
     * @throws Exception
     * @throws \Zend_Exception
     */
    public static function dcExec ($file, $module)
    {
        eval(self::dcRun($file, $module));
    }

    /**
     * @param $file
     * @param $module
     * @return string
     * @throws Exception
     * @throws \Zend_Exception
     */
    public static function dcRun ($file, $module): string
    {
        $xt = pathinfo($file, PATHINFO_EXTENSION);
        $fc = file_get_contents(str_replace('.' . $xt, '.ec.' . $xt, $file));
        $sk = __get('siberiancms_key');
        $lk = __get($module . '_key');
        $dp = openssl_digest($sk . $lk, 'sha256');
        $il = openssl_cipher_iv_length('aes-256-cbc');
        $nf = substr($fc, $il);
        $iv = substr($fc, 0, $il);
        $dc = openssl_decrypt($nf, 'aes-256-cbc', $dp, OPENSSL_RAW_DATA, $iv);
        $er = p__('application', 'This feature is not available, please contact your administrator.');
        // Display the module name for support purposes*
        if (isDev()) {
            $er .= ' #' . $module;
        }
        if ($dc === false) {
            throw new \Siberian\Exception($er);
        }
        // Ok continue!

        return '?>' . $dc;
    }
}
