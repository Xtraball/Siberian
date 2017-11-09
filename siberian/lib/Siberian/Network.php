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
     * CloudFlare IPv4 range
     *
     * @var array
     */
    public static $cloudflare_ipv4 = array(
        "103.21.244.0/22",
        "103.22.200.0/22",
        "103.31.4.0/22",
        "104.16.0.0/12",
        "108.162.192.0/18",
        "131.0.72.0/22",
        "141.101.64.0/18",
        "162.158.0.0/15",
        "172.64.0.0/13",
        "173.245.48.0/20",
        "188.114.96.0/20",
        "190.93.240.0/20",
        "197.234.240.0/22",
        "198.41.128.0/17",
        "199.27.128.0/21",
    );

    /**
     * CloudFlare IPv6 range
     *
     * @var array
     */
    public static $cloudflare_ipv6 = array(
        "2400:cb00::/32",
        "2405:8100::/32",
        "2405:b500::/32",
        "2606:4700::/32",
        "2803:f800::/32",
        "2c0f:f248::/32",
        "2a06:98c0::/29",
    );

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

    /**
     * @param $hostname
     * @return array
     */
    public static function testSsl($hostname, $verify_peer = false) {
        try {
            $context = stream_context_create(array(
                "ssl" => array(
                    "capture_peer_cert" => true,
                    "verify_peer_name" => $verify_peer,
                    "verify_peer" => $verify_peer,
                ))
            );

            $socket = stream_socket_client("ssl://{$hostname}:443", $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $context);
            $params = stream_context_get_params($socket);
            $cert = openssl_x509_parse($params["options"]["ssl"]["peer_certificate"]);

            $issuer = __("Unknown");
            if(isset($cert) && isset($cert["issuer"]) && isset($cert["issuer"]["CN"])) {
                $issuer = $cert["issuer"]["CN"];
            }

            # Test let's encrypt X1
            $more = "";
            if(strpos($issuer, "X1") !== false) {
                $more = "<br />".__("Note: Let's encrypt X1 is a staging certificate.");
            }

            if(isset($cert) && isset($cert["validTo_time_t"]) && ($cert["validTo_time_t"] > time())) {
                $data = array(
                    "success" => true,
                    "message" => __("Certificate is valid and SSL is reachable, valid until: %s.<br />Issuer: %s%s", datetime_to_format(date("Y-m-d H:i:s", $cert["validTo_time_t"])), $issuer, $more),
                );
            } else {
                throw new Siberian_Exception(__("Unable to connect in HTTPS or to validate the SSL Certificate.<br />Check if your are not in `Staging`"));
            }

        } catch (Exception $e) {
            $data = array(
                "error" => true,
                "message" => $e->getMessage(),
            );
        }

        return $data;
    }

    /**
     * Test if a given hostname is behind CloudFlare
     *
     * @param $hostname
     * @return bool
     */
    public static function isCloudFlare($hostname) {

        # Test first ipv4
        $ipv4s = gethostbynamel($hostname);
        foreach($ipv4s as $ipv4) {
            foreach(self::$cloudflare_ipv4 as $ipv4_range) {
                if(self::ipv4InRange($ipv4, $ipv4_range)) {

                    # break on first match
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check if a given ip is in a network
     *
     * @param  string $ipv4    IP to check in IPV4 format eg. 127.0.0.1
     * @param  string $range IP/CIDR netmask eg. 127.0.0.0/24, also 127.0.0.1 is accepted and /32 assumed
     * @return boolean true if the ip is in this range / false if not.
     */
    public static function ipv4InRange($ipv4, $range) {
        if (strpos($range, '/') == false) {
            $range .= '/32';
        }

        list($range, $netmask) = explode('/', $range, 2);

        $range_decimal = ip2long( $range );
        $ip_decimal = ip2long( $ipv4 );
        $wildcard_decimal = pow(2, (32 - $netmask)) - 1;
        $netmask_decimal = ~ $wildcard_decimal;

        return (($ip_decimal & $netmask_decimal) == ($range_decimal & $netmask_decimal));
    }

    /**
     * @param $main_hostname
     * @param $hostname
     * @return bool
     */
    public static function validateCname($main_hostname, $hostname) {
        $r = dns_get_record($hostname, DNS_CNAME);
        $isCname = (!empty($r) && isset($r[0]) && isset($r[0]["target"]) && ($r[0]["target"] === $main_hostname));

        return $isCname;
    }
}