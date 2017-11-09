<?php

class Siberian_Application_Config_Secrets
{
    private static $secrets_path = "app/configs/secrets.json";

    /**
     * Generate secret for a given name
     *
     * @return mixed string secret token or NULL if secret already exists
     */
    public static function generate($name)
    {
        return self::_addSecret($name, self::__getToken());
    }

    /**
     * Generate secret for a given name only if it doesn't exist
     *
     * @return string secret token
     */
    public static function generateOnce($name) {
        $secret = self::get($name);

        if(empty($secret)) {
            return self::generate(name);
        }

        return $secret;
    }

    /**
     * Get secret for a given name
     *
     * @return mixed string secret token or NULL if it doesn't exist
     */
    public static function get($name) {
        $secrets = self::_readSecrets();
        if(array_key_exists($name, $secrets)) {
            return $secrets[$name];
        }

        return null;
    }

    /**
     * Destroy secret for a given name if it exist
     *
     * @return string secret token
     */
    public static function destroy($name) {
        self::_removeSecret($name);
    }

    /**
     * Destroy and regenerate secret for a given name if it exist
     *
     * @return string secret token
     */
    public static function regenerate($name) {
        self::destroy($name);
        return self::generate($name);
    }

    private static function _createSecretsFileIfNeeded()
    {
        $secrets_json = Core_Model_Directory::getBasePathTo(self::$secrets_path);
        if (!file_exists($secrets_json) AND is_writable($secrets_json)) {
            $json = fopen($secrets_json, 'w');
            fputs($json, "{}");
            fclose($json);
        }
        return $secrets_json;
    }

    private static function _addSecret($name, $secret)
    {
        $secrets = self::_readSecrets();
        if(!array_key_exists($name, $secrets)) {
            $secrets[$name] = $secret;
            self::_writeSecrets($secrets);
            return $secret;
        }

        return null;
    }

    private static function _removeSecret($name)
    {
        $secrets = self::_readSecrets();
        if(array_key_exists($name, $secrets)) {
            unset($secrets[$name]);
        }
        self::_writeSecrets($secrets);
    }

    private static function _readSecrets()
    {
        $secrets_json = self::_createSecretsFileIfNeeded();
        $json = fopen($secrets_json, 'r');
        $data = fread($json, filesize($secrets_json));
        fclose($json);

        $decoded = json_decode($data, true);

        return $decoded ?: array();
    }

    private static function _writeSecrets(array $listOfSecrets)
    {
        $secrets_json = self::_createSecretsFileIfNeeded();
        $json = fopen($secrets_json, 'w');
        fputs($json, json_encode($listOfSecrets));
        fclose($json);
    }

    private static function __crypto_rand_secure($min, $max) {
        $range = $max - $min;
        if ($range < 0) return $min; // not so random...
        $log = log($range, 2);
        $bytes = (int) ($log / 8) + 1; // length in bytes
        $bits = (int) $log + 1; // length in bits
        $filter = (int) (1 << $bits) - 1; // set all lower bits to 1
        do {
            $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
            $rnd = $rnd & $filter; // discard irrelevant bits
        } while ($rnd >= $range);
        return $min + $rnd;
    }

    private static function __getToken($length=32){
        $token = "";
        $codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $codeAlphabet.= "abcdefghijklmnopqrstuvwxyz";
        $codeAlphabet.= "0123456789";
        for($i=0;$i<$length;$i++){
            $token .= $codeAlphabet[self::__crypto_rand_secure(0,strlen($codeAlphabet))];
        }
        return $token;
    }

}
