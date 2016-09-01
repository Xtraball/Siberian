<?php

/**
 * Class Siberian_Network
 *
 * Tooling for hostname, ip, etc...
 *
 * @author Josh Finlay <josh@glamourcastle.com>
 */
class Siberian_Network {

    /**
     * @param $url
     * @return mixed
     */
    public static function testipv4($url) {
        $hostname = self::getHostForUrl($url);
        return (filter_var($hostname, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false);
    }

    /**
     * @param $url
     */
    public static function testipv6($url) {
        if(empty($url)) {
            return false;
        }
        $hostname = self::getHostForUrl($url);
        $base_domain = self::getBaseHost($hostname);

        try {
            $result_hostname = self::gethostbyname6($hostname);
            $result_domain = self::gethostbyname6($base_domain);
        } catch (Exception $e) {
            return false;
        }

        return ($result_hostname || $result_domain);
    }

    /**
     * Extracts host from url
     *
     * @param $url
     * @return bool
     */
    public static function getHostForUrl($url) {
        $parts = parse_url($url);
        if(isset($parts["host"])) {
            return $parts["host"];
        }
        return false;
    }

    /**
     * Extract base domain
     *
     * @param $domain
     * @return string
     */
    public function getBaseHost($domain) {
        $parts = explode(".", $domain);

        return (array_key_exists(count($parts) - 2, $parts) ? $array[count($parts) - 2] : "").".".$parts[count($parts) - 1];
    }

    /**
     * Get AAA Record for $host
     *
     * @param $host
     * @param bool $try_a
     * @return bool
     */
    public static function gethostbyname6($host, $try_a = false) {
        if(empty($host)) {
            return false;
        }
        $dns = self::gethostbynamel6($host, $try_a);
        if ($dns == false) {
            return false;
        }
        else {
            return $dns[0];
        }
    }

    /**
     * Get AAAA record list for $host
     *
     * @param $host
     * @param bool $try_a
     * @return array|bool
     */
    public static function gethostbynamel6($host, $try_a = false) {
        $dns6 = dns_get_record($host, DNS_AAAA);
        if ($try_a == true) {
            $dns4 = dns_get_record($host, DNS_A);
            $dns = array_merge($dns4, $dns6);
        }
        else {
            $dns = $dns6;
        }
        $ip6 = array();
        $ip4 = array();
        foreach ($dns as $record) {
            if ($record["type"] == "A") {
                $ip4[] = $record["ip"];
            }
            if ($record["type"] == "AAAA") {
                $ip6[] = $record["ipv6"];
            }
        }
        if (count($ip6) < 1) {
            if ($try_a == true) {
                if (count($ip4) < 1) {
                    return false;
                }
                else {
                    return $ip4;
                }
            }
            else {
                return false;
            }
        }
        else {
            return $ip6;
        }
    }
}