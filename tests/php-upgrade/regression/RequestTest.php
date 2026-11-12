<?php
/**
 * Siberian\Request against a real local HTTP server (fixtures/router.php):
 * query building, bodies, auth, headers, cookies, redirects, status codes,
 * timeouts and a dead server.
 */

use Siberian\Request;

if (!function_exists('path')) {
    function path($p = '')
    {
        return sys_get_temp_dir() . '/' . ltrim($p, '/');
    }
}
require_once dirname(__DIR__, 3) . '/siberian/lib/Siberian/Request.php';

function t_hdr($json, $name)
{
    foreach ($json['headers'] as $k => $v) {
        if (strcasecmp($k, $name) === 0) {
            return $v;
        }
    }
    return null;
}

return [
    'get appends the data as a query string' => function () {
        $r = json_decode(Request::get(t_server() . '/echo', ['a' => 1, 'b' => 'x y']), true);
        t_equal('GET', $r['method']);
        t_equal(['a' => '1', 'b' => 'x y'], $r['query']);
        t_equal(200, Request::$statusCode);
    },
    'get merges the data into a query already in the url' => function () {
        $r = json_decode(Request::get(t_server() . '/echo?k=v', ['a' => 1]), true);
        t_equal(['k' => 'v', 'a' => '1'], $r['query']);
    },
    'get follows redirects' => function () {
        $r = json_decode(Request::get(t_server() . '/redirect'), true);
        t_equal(['from' => 'redirect'], $r['query']);
    },
    'the status code of the last call is kept, also on errors' => function () {
        Request::get(t_server() . '/status/500');
        t_equal(500, Request::$statusCode);
        Request::get(t_server() . '/nowhere');
        t_equal(404, Request::$statusCode);
    },
    'post sends the data form encoded' => function () {
        $r = json_decode(Request::post(t_server() . '/echo', ['a' => 1, 'n' => ['x' => 'y']]), true);
        t_equal('POST', $r['method']);
        t_equal('a=1&n%5Bx%5D=y', $r['body']);
    },
    'post with json_body sends the string untouched' => function () {
        $r = json_decode(Request::post(
            t_server() . '/echo',
            '{"a":1}',
            null,
            null,
            ['Content-Type: application/json'],
            ['json_body' => true]
        ), true);
        t_equal('{"a":1}', $r['body']);
        t_equal('application/json', $r['type']);
    },
    'basic auth is sent' => function () {
        $r = json_decode(Request::get(t_server() . '/echo', [], null, [
            'type' => 'basic', 'username' => 'u', 'password' => 'p',
        ]), true);
        t_equal('Basic ' . base64_encode('u:p'), $r['auth']);
    },
    'bearer auth is sent in the Api-Auth-Bearer header' => function () {
        $r = json_decode(Request::get(t_server() . '/headers', [], null, [
            'type' => 'bearer', 'bearer' => 'tok',
        ]), true);
        t_equal('Bearer tok', t_hdr($r, 'Api-Auth-Bearer'));
    },
    'bearer auth survives when other headers are given too' => function () {
        // CURLOPT_HTTPHEADER is set twice (auth, then headers): the second call replaces the first.
        $r = json_decode(Request::get(t_server() . '/headers', [], null, [
            'type' => 'bearer', 'bearer' => 'tok',
        ], ['X-One: 1']), true);
        t_equal('1', t_hdr($r, 'X-One'));
        t_equal('Bearer tok', t_hdr($r, 'Api-Auth-Bearer'));
    },
    'cookies are stored in the jar and sent back' => function () {
        $jar = tempnam(sys_get_temp_dir(), 'sibjar');
        Request::get(t_server() . '/set-cookie', [], $jar);
        $r = json_decode(Request::get(t_server() . '/headers', [], $jar), true);
        t_equal(['sib' => 'jar1'], $r['cookie']);
        @unlink($jar);
    },
    'curl options given by the caller replace the defaults' => function () {
        $r = json_decode(Request::get(t_server() . '/echo', [], null, null, null, [], [
            CURLOPT_USERAGENT => 'sib-test',
        ]), true);
        t_equal('sib-test', $r['agent']);
    },
    'the timeout option is honoured and the call returns false' => function () {
        $t = microtime(true);
        $res = Request::get(t_server() . '/slow', [], null, null, null, ['timeout' => 1]);
        t_equal(false, $res);
        t_true(microtime(true) - $t < 2.5, 'returned before the server answered');
        t_equal(0, Request::$statusCode);
    },
    'a dead server gives false and status 0' => function () {
        $probe = stream_socket_server('tcp://127.0.0.1:0');
        $name = stream_socket_get_name($probe, false);
        fclose($probe);
        $res = Request::get('http://' . $name . '/', [], null, null, null, ['timeout' => 1]);
        t_equal(false, $res);
        t_equal(0, Request::$statusCode);
    },
    'testStream returns the content type of the stream' => function () {
        t_equal('audio/mpeg', Request::testStream(t_server() . '/stream'));
    },
];
