<?php
/**
 * Siberian_Network: the pure parts (host extraction, IPv4 ranges, CloudFlare
 * ranges). DNS and TLS parts are not tested here (no network in the suite).
 */

return [
    'getHostForUrl extracts the host or gives false' => function () {
        t_equal('example.com', Siberian_Network::getHostForUrl('https://example.com:8080/a?b=1'));
        t_equal(false, Siberian_Network::getHostForUrl('not a url'));
    },
    'testipv4 is true only for an IPv4 host' => function () {
        t_true(Siberian_Network::testipv4('http://192.168.1.10/x'));
        t_equal(false, Siberian_Network::testipv4('http://example.com/x'));
        t_equal(false, Siberian_Network::testipv4('http://[::1]/x'));
        t_equal(false, Siberian_Network::testipv4('http://999.1.1.1/x'));
    },
    'ipv4InRange handles a CIDR range' => function () {
        t_true(Siberian_Network::ipv4InRange('10.0.0.77', '10.0.0.0/24'));
        t_equal(false, Siberian_Network::ipv4InRange('10.0.1.77', '10.0.0.0/24'));
        t_true(Siberian_Network::ipv4InRange('104.16.5.5', '104.16.0.0/12'));
        t_true(Siberian_Network::ipv4InRange('104.31.255.255', '104.16.0.0/12'));
        t_equal(false, Siberian_Network::ipv4InRange('104.32.0.1', '104.16.0.0/12'));
    },
    'ipv4InRange treats a bare address as /32' => function () {
        t_true(Siberian_Network::ipv4InRange('1.2.3.4', '1.2.3.4'));
        t_equal(false, Siberian_Network::ipv4InRange('1.2.3.5', '1.2.3.4'));
    },
    'ipv4InRange /0 matches everything' => function () {
        t_true(Siberian_Network::ipv4InRange('203.0.113.9', '0.0.0.0/0'));
    },
    'every CloudFlare range contains its own first address' => function () {
        foreach (Siberian_Network::$cloudflare_ipv4 as $range) {
            list($ip) = explode('/', $range);
            t_true(Siberian_Network::ipv4InRange($ip, $range), $range);
        }
    },
    'getBaseHost keeps the last two labels' => function () {
        $n = new Siberian_Network();
        t_equal('example.com', $n->getBaseHost('www.example.com'));
        t_equal('example.com', $n->getBaseHost('a.b.example.com'));
    },
    'testipv6 on a host name does not end in a fatal error' => function () {
        // testipv6() calls the non-static getBaseHost() as self::getBaseHost(): an Error on PHP 8.
        $r = Siberian_Network::testipv6('http://localhost/');
        t_true($r === false || $r === true || is_string($r));
    },
    'testipv6 of an empty url is false' => function () {
        t_equal(false, Siberian_Network::testipv6(''));
    },
];
