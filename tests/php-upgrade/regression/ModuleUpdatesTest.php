<?php
/**
 * Module updates (Siberian\ModuleUpdates): manifest cache and staleness, version comparison,
 * terms acceptance, and the download verification (sha256, Ed25519 signature, status mapping).
 * No network: a scripted transport stands in for the downloader.
 */
$siberian = dirname(__DIR__, 3) . '/siberian';
foreach (['Exception', 'Transport', 'Store', 'CurlTransport'] as $class) {
    require_once $siberian . '/lib/Siberian/ModuleUpdates/' . $class . '.php';
}
require_once $siberian . '/lib/Siberian/ModuleUpdates.php';

use Siberian\ModuleUpdates;
use Siberian\ModuleUpdates\Exception as UpdateException;

class MuFakeTransport implements Siberian\ModuleUpdates\Transport
{
    public $calls = [];
    /** @var array method + path => list of responses, consumed in order (the last one repeats) */
    public $script = [];

    public function request($method, $url, array $headers = [], $body = null, $timeout = 10, $maxBytes = 5242880)
    {
        $this->calls[] = ['method' => $method, 'url' => $url, 'headers' => $headers, 'body' => $body];
        $key = $method . ' ' . parse_url($url, PHP_URL_PATH);
        if (empty($this->script[$key])) {
            return ['status' => 0, 'headers' => [], 'body' => ''];
        }
        $response = count($this->script[$key]) > 1 ? array_shift($this->script[$key]) : $this->script[$key][0];

        return $response + ['headers' => [], 'body' => ''];
    }
}

class MuArrayStore implements Siberian\ModuleUpdates\Store
{
    public $data = [];

    public function get($key)
    {
        return isset($this->data[$key]) && $this->data[$key] !== '' ? $this->data[$key] : null;
    }

    public function set($key, $value)
    {
        $this->data[$key] = $value;
    }
}

function mu_manifest($termsHash = 'hash-1', array $modules = null)
{
    return [
        'schema' => 1,
        'generated_at' => '2026-10-24T10:00:00+00:00',
        'terms' => ['version' => '1', 'hash' => $termsHash],
        'modules' => $modules ?? [
            'cabride' => ['name' => 'Cabride', 'version' => '6.0.0', 'item_id' => 1, 'min_siberian' => '5.1.0', 'sha256' => 'x', 'size' => 1],
            'chat' => ['name' => 'Chat', 'version' => '3.0.0', 'item_id' => 2, 'min_siberian' => '5.0.0', 'sha256' => 'x', 'size' => 1],
            'surveys' => ['name' => 'Surveys', 'version' => '3.0.0', 'item_id' => 3, 'min_siberian' => null, 'sha256' => 'x', 'size' => 1],
        ],
    ];
}

function mu_env($siberian = '5.0.18', array $keys = [], &$now = 1000000)
{
    $dir = sys_get_temp_dir() . '/mu-' . bin2hex(random_bytes(5));
    mkdir($dir, 0775, true);
    $transport = new MuFakeTransport();
    $store = new MuArrayStore();
    $service = new ModuleUpdates($transport, $store, $dir . '/cache/modules.json', $siberian, $keys, function () use (&$now) {
        return $now;
    });

    return [$service, $transport, $store, $dir];
}

function mu_expect($code, callable $fn)
{
    try {
        $fn();
    } catch (UpdateException $e) {
        t_equal($code, $e->getErrorCode(), 'error code (' . $e->getMessage() . ')');

        return $e;
    }
    throw new RuntimeException('expected ' . $code . ', nothing was thrown');
}

function mu_package($code, $version, array $pair, $keyId = 'k1', $body = 'PACKAGE-BYTES')
{
    $sha = hash('sha256', $body);
    $sig = base64_encode(sodium_crypto_sign_detached($sha . "\n" . $code . "\n" . $version, $pair['secret']));

    return [
        'status' => 200,
        'body' => $body,
        'headers' => [
            'x-package-sha256' => $sha,
            'x-package-signature' => $sig,
            'x-package-key-id' => $keyId,
            'x-package-version' => $version,
            'content-disposition' => 'attachment; filename="0123456789abcdef-Cabride-' . $version . '.sib"',
        ],
    ];
}

function mu_keypair()
{
    $pair = sodium_crypto_sign_keypair();

    return ['secret' => sodium_crypto_sign_secretkey($pair), 'public' => base64_encode(sodium_crypto_sign_publickey($pair))];
}

$json = function ($status, array $data, array $headers = []) {
    return ['status' => $status, 'body' => json_encode($data), 'headers' => $headers];
};

return [
    'refresh fetches and caches the list' => function () use ($json) {
        [$svc, $http, , $dir] = mu_env();
        $http->script['GET /api/v1/modules.json'] = [$json(200, mu_manifest(), ['etag' => '"abc"'])];
        $list = $svc->refresh();
        t_equal(false, $list['stale'], 'fresh');
        t_equal(3, count($list['manifest']['modules']), 'modules');
        t_true(is_file($dir . '/cache/modules.json'), 'cache written');
        t_equal('https://downloader.siberiancms.com/api/v1/modules.json', $http->calls[0]['url'], 'url');
    },

    'refresh uses the cache for a day without calling the downloader' => function () use ($json) {
        $now = 1000000;
        [$svc, $http] = mu_env('5.0.18', [], $now);
        $http->script['GET /api/v1/modules.json'] = [$json(200, mu_manifest())];
        $svc->refresh();
        $now += 3600;
        $svc->refresh();
        t_equal(1, count($http->calls), 'one call');
        $now += 86400;
        $svc->refresh();
        t_equal(2, count($http->calls), 'second call after a day');
    },

    'refresh sends If-None-Match and keeps the cache on 304' => function () use ($json) {
        $now = 1000000;
        [$svc, $http] = mu_env('5.0.18', [], $now);
        $http->script['GET /api/v1/modules.json'] = [$json(200, mu_manifest(), ['etag' => '"abc"']), ['status' => 304]];
        $svc->refresh();
        $now += 90000;
        $list = $svc->refresh();
        t_true(in_array('If-None-Match: "abc"', $http->calls[1]['headers'], true), 'conditional header');
        t_equal(false, $list['stale'], '304 is fresh');
        t_equal(3, count($list['manifest']['modules']), 'cache kept');
    },

    'force refresh falls back on the cache when the downloader is down' => function () use ($json) {
        $now = 1000000;
        [$svc, $http] = mu_env('5.0.18', [], $now);
        $http->script['GET /api/v1/modules.json'] = [$json(200, mu_manifest()), ['status' => 503]];
        $svc->refresh();
        $now += 60;
        $list = $svc->refresh(true);
        t_equal(true, $list['stale'], 'stale flag');
        t_equal(3, count($list['manifest']['modules']), 'cache served');
    },

    'the cache stops being served after 7 days' => function () use ($json) {
        $now = 1000000;
        [$svc, $http] = mu_env('5.0.18', [], $now);
        $http->script['GET /api/v1/modules.json'] = [$json(200, mu_manifest()), ['status' => 0]];
        $svc->refresh();
        $now += 7 * 86400 + 1;
        mu_expect('unavailable', function () use ($svc) {
            $svc->refresh();
        });
    },

    'no cache and no downloader is an error, not a crash' => function () {
        [$svc] = mu_env();
        mu_expect('unavailable', function () use ($svc) {
            $svc->refresh(true);
        });
    },

    'a manifest with a wrong schema is ignored' => function () use ($json) {
        [$svc, $http] = mu_env();
        $bad = mu_manifest();
        $bad['schema'] = 2;
        $http->script['GET /api/v1/modules.json'] = [$json(200, $bad)];
        mu_expect('unavailable', function () use ($svc) {
            $svc->refresh();
        });
    },

    'the kill switch stops every call' => function () {
        [$svc, $http, $store] = mu_env();
        $store->set('module_updates_enabled', '0');
        mu_expect('disabled', function () use ($svc) {
            $svc->refresh();
        });
        mu_expect('disabled', function () use ($svc) {
            $svc->fetchTerms();
        });
        t_equal(0, count($http->calls), 'no network');
    },

    'pending compares versions and skips what it should' => function () use ($json) {
        [$svc, $http] = mu_env('5.0.18');
        $http->script['GET /api/v1/modules.json'] = [$json(200, mu_manifest())];
        $list = $svc->refresh();
        $installed = [
            ['code' => 'cabride', 'name' => 'Cabride', 'version' => '5.2.0', 'has_license' => true],
            ['code' => 'chat', 'name' => 'Chat', 'version' => '3.0.0', 'has_license' => true],
            ['code' => 'surveys', 'name' => 'Surveys', 'version' => '2.9.9', 'has_license' => false],
            ['code' => 'unknown', 'name' => 'Unknown', 'version' => '1.0.0', 'has_license' => true],
            ['code' => null, 'name' => 'Core', 'version' => '1.0.0', 'has_license' => true],
        ];
        $pending = $svc->pending($installed, $list);
        t_equal(2, count($pending), 'cabride, and surveys whose key is not saved yet');
        t_equal('cabride', $pending[0]['code'], 'code');
        t_equal('6.0.0', $pending[0]['available'], 'available');
        t_equal(false, $pending[0]['compatible'], 'needs 5.1.0, running 5.0.18');
        t_equal(false, $pending[0]['license_missing'], 'cabride has its key');
        t_equal('surveys', $pending[1]['code'], 'a module without a saved key is listed');
        t_equal(true, $pending[1]['license_missing'], 'and flagged, so the administrator is asked for the key');
    },

    'pending marks a module compatible once Siberian is new enough' => function () use ($json) {
        [$svc, $http] = mu_env('5.1.0');
        $http->script['GET /api/v1/modules.json'] = [$json(200, mu_manifest())];
        $pending = $svc->pending([['code' => 'cabride', 'name' => 'Cabride', 'version' => '5.2.0', 'has_license' => true]], $svc->refresh());
        t_equal(true, $pending[0]['compatible'], 'compatible');
    },

    'a module installed without a code is matched to the list by name' => function () use ($json) {
        [$svc, $http] = mu_env('5.1.0');
        $m = mu_manifest('h', ['inapplink' => ['name' => 'InAppLink', 'version' => '4.1.13', 'item_id' => 3, 'min_siberian' => null]]);
        $http->script['GET /api/v1/modules.json'] = [$json(200, $m)];
        $list = $svc->refresh();
        $pending = $svc->pending([
            ['code' => null, 'name' => 'InAppLink', 'version' => '2.0.0', 'has_license' => false],
            ['code' => '', 'name' => 'Core', 'version' => '1.0.0', 'has_license' => false],
        ], $list);
        t_equal(1, count($pending), 'the old inapplink is found, core is not');
        t_equal('inapplink', $pending[0]['code'], 'the list code is used');
        t_equal(true, $pending[0]['license_missing'], 'the administrator is asked for the key');
        $m = mu_manifest('h', [
            'a' => ['name' => 'Twin', 'version' => '2.0.0', 'item_id' => 4, 'min_siberian' => null],
            'b' => ['name' => 'twin', 'version' => '2.0.0', 'item_id' => 5, 'min_siberian' => null],
        ]);
        $http->script['GET /api/v1/modules.json'] = [$json(200, $m)];
        $svc2 = mu_env('5.1.0');
        $svc2[1]->script['GET /api/v1/modules.json'] = [$json(200, $m)];
        t_equal(0, count($svc2[0]->pending([['code' => null, 'name' => 'Twin', 'version' => '1.0.0', 'has_license' => false]], $svc2[0]->refresh())), 'two entries with one name are never guessed');
    },

    'version comparison is numeric, not alphabetical' => function () use ($json) {
        [$svc, $http] = mu_env('5.1.0');
        $m = mu_manifest('h', ['chat' => ['name' => 'Chat', 'version' => '3.10.0', 'item_id' => 2, 'min_siberian' => null]]);
        $http->script['GET /api/v1/modules.json'] = [$json(200, $m)];
        $pending = $svc->pending([['code' => 'chat', 'name' => 'Chat', 'version' => '3.9.0', 'has_license' => true]], $svc->refresh());
        t_equal(1, count($pending), '3.10.0 is newer than 3.9.0');
    },

    'terms: acceptance is tied to the live hash' => function () use ($json) {
        [$svc, $http, $store] = mu_env();
        $http->script['GET /api/v1/modules.json'] = [$json(200, mu_manifest('hash-1'))];
        $http->script['GET /api/v1/terms'] = [$json(200, ['version' => '1', 'hash' => 'hash-1', 'effective' => '2026-10-24', 'html' => '<p>x</p>'])];
        $list = $svc->refresh();
        t_equal(false, $svc->termsAccepted($list), 'not accepted yet');
        $svc->acceptTerms('hash-1', 7);
        t_equal(true, $svc->termsAccepted($list), 'accepted');
        t_equal('7', $store->get('module_updates_terms_accepted_by'), 'who');
        $changed = ['manifest' => mu_manifest('hash-2')] + $list;
        t_equal(false, $svc->termsAccepted($changed), 'a new hash asks again');
    },

    'terms: accepting an outdated page is refused' => function () use ($json) {
        [$svc, $http, $store] = mu_env();
        $http->script['GET /api/v1/terms'] = [$json(200, ['version' => '2', 'hash' => 'hash-2', 'effective' => '', 'html' => '<p>new</p>'])];
        $e = mu_expect('terms_changed', function () use ($svc) {
            $svc->acceptTerms('hash-1', 7);
        });
        t_equal('hash-2', $e->getExtra()['terms']['hash'], 'new terms returned');
        t_equal(null, $store->get('module_updates_terms_hash'), 'nothing stored');
    },

    'download verifies sha256 and signature, then stores the file' => function () {
        $pair = mu_keypair();
        [$svc, $http, , $dir] = mu_env('5.1.0', ['k1' => $pair['public']]);
        $http->script['POST /api/v1/modules/cabride/download'] = [mu_package('cabride', '6.0.0', $pair)];
        $out = $svc->download('cabride', 'SK', 'LK', '5.2.0', 'example.com', $dir, 'hash-1');
        t_equal('Cabride-6.0.0.sib', $out['filename'], 'server temp prefix removed');
        t_equal('PACKAGE-BYTES', file_get_contents($out['path']), 'content');
        $sent = json_decode($http->calls[0]['body'], true);
        t_equal(['sk', 'lk', 'host', 'terms_hash', 'installed_version', 'ts', 'nonce'], array_keys($sent), 'request fields');
        t_equal(32, strlen($sent['nonce']), 'random nonce');
        t_true(in_array('X-Siberian-Version: 5.1.0', $http->calls[0]['headers'], true), 'version header');
    },

    'download refuses a bad signature and writes nothing' => function () {
        $pair = mu_keypair();
        $other = mu_keypair();
        [$svc, $http, , $dir] = mu_env('5.1.0', ['k1' => $pair['public']]);
        $http->script['POST /api/v1/modules/cabride/download'] = [mu_package('cabride', '6.0.0', $other)];
        mu_expect('bad_signature', function () use ($svc, $dir) {
            $svc->download('cabride', 'SK', 'LK', '5.2.0', 'h', $dir, 'hash-1');
        });
        t_equal([], glob($dir . '/*.sib'), 'no file');
    },

    'download refuses a signature made for another module or version' => function () {
        $pair = mu_keypair();
        [$svc, $http, , $dir] = mu_env('5.1.0', ['k1' => $pair['public']]);
        $http->script['POST /api/v1/modules/cabride/download'] = [mu_package('chat', '6.0.0', $pair)];
        mu_expect('bad_signature', function () use ($svc, $dir) {
            $svc->download('cabride', 'SK', 'LK', '5.2.0', 'h', $dir, 'hash-1');
        });
    },

    'download refuses a body that does not match its sha256' => function () {
        $pair = mu_keypair();
        [$svc, $http, , $dir] = mu_env('5.1.0', ['k1' => $pair['public']]);
        $package = mu_package('cabride', '6.0.0', $pair);
        $package['body'] = 'TAMPERED';
        $http->script['POST /api/v1/modules/cabride/download'] = [$package];
        mu_expect('bad_package', function () use ($svc, $dir) {
            $svc->download('cabride', 'SK', 'LK', '5.2.0', 'h', $dir, 'hash-1');
        });
    },

    'download refuses an unknown key id' => function () {
        $pair = mu_keypair();
        [$svc, $http, , $dir] = mu_env('5.1.0', ['k1' => $pair['public']]);
        $http->script['POST /api/v1/modules/cabride/download'] = [mu_package('cabride', '6.0.0', $pair, 'k9')];
        mu_expect('bad_signature', function () use ($svc, $dir) {
            $svc->download('cabride', 'SK', 'LK', '5.2.0', 'h', $dir, 'hash-1');
        });
    },

    'download refuses a package that is not newer (rollback attempt)' => function () {
        $pair = mu_keypair();
        [$svc, $http, , $dir] = mu_env('5.1.0', ['k1' => $pair['public']]);
        $http->script['POST /api/v1/modules/cabride/download'] = [mu_package('cabride', '5.0.0', $pair)];
        mu_expect('not_newer', function () use ($svc, $dir) {
            $svc->download('cabride', 'SK', 'LK', '5.2.0', 'h', $dir, 'hash-1');
        });
    },

    'download is refused while no signing key is pinned' => function () {
        [$svc, $http, , $dir] = mu_env('5.1.0', []);
        mu_expect('no_signing_key', function () use ($svc, $dir) {
            $svc->download('cabride', 'SK', 'LK', '5.2.0', 'h', $dir, 'hash-1');
        });
        t_equal(0, count($http->calls), 'no request, the licences stay home');
    },

    'download rejects a module code that is not a plain slug' => function () {
        $pair = mu_keypair();
        [$svc, $http, , $dir] = mu_env('5.1.0', ['k1' => $pair['public']]);
        mu_expect('bad_request', function () use ($svc, $dir) {
            $svc->download('../etc/passwd', 'SK', 'LK', '1.0.0', 'h', $dir, 'x');
        });
        t_equal(0, count($http->calls), 'no request');
    },

    'download maps downloader statuses to error codes' => function () use ($json) {
        $pair = mu_keypair();
        $cases = [
            [401, 'siberian_license'], [403, 'module_license'], [404, 'not_found'],
            [426, 'siberian_too_old'], [429, 'rate_limited'], [503, 'unavailable'], [0, 'unavailable'],
        ];
        foreach ($cases as [$status, $code]) {
            [$svc, $http, , $dir] = mu_env('5.1.0', ['k1' => $pair['public']]);
            $http->script['POST /api/v1/modules/cabride/download'] = [$json($status, ['error' => 'x', 'message' => 'm'])];
            mu_expect($code, function () use ($svc, $dir) {
                $svc->download('cabride', 'SK', 'LK', '5.2.0', 'h', $dir, 'hash-1');
            });
        }
    },

    'a 409 forgets the acceptance and hands back the new terms' => function () use ($json) {
        $pair = mu_keypair();
        [$svc, $http, $store, $dir] = mu_env('5.1.0', ['k1' => $pair['public']]);
        $store->set('module_updates_terms_hash', 'hash-1');
        $http->script['POST /api/v1/modules/cabride/download'] = [$json(409, ['error' => 'terms_changed', 'message' => 'changed', 'terms' => ['hash' => 'hash-2', 'html' => '<p>n</p>']])];
        $e = mu_expect('terms_changed', function () use ($svc, $dir) {
            $svc->download('cabride', 'SK', 'LK', '5.2.0', 'h', $dir, 'hash-1');
        });
        t_equal('hash-2', $e->getExtra()['terms']['hash'], 'new terms');
        t_equal(null, $store->get('module_updates_terms_hash'), 'acceptance cleared');
    },

    'the strict transport refuses plain http' => function () {
        $transport = new Siberian\ModuleUpdates\CurlTransport();
        mu_expect('insecure_url', function () use ($transport) {
            $transport->request('GET', 'http://downloader.siberiancms.com/api/v1/modules.json');
        });
    },
];
