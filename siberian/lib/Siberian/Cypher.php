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
        $clear = $privateKey->decrypt(base64_decode($cyphered));

        return $clear;
    }
}