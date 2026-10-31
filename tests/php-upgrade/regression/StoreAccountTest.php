<?php
/**
 * Store account (Siberian\StoreAccount): the connect dance, token storage and silent renewal, the purchases cache and
 * its staleness, revocation, disconnect. No network: a scripted transport stands in for extensions.siberiancms.com.
 */
$siberian = dirname(__DIR__, 3) . '/siberian';
foreach (['Exception', 'Transport', 'Store', 'CurlTransport'] as $class) {
    require_once $siberian . '/lib/Siberian/ModuleUpdates/' . $class . '.php';
}
require_once $siberian . '/lib/Siberian/StoreAccount.php';

use Siberian\ModuleUpdates\Exception as StoreException;
use Siberian\StoreAccount;

if (!class_exists('SaFakeTransport')) {
    class SaFakeTransport implements Siberian\ModuleUpdates\Transport
    {
        public $calls = [];
        /** @var array "METHOD /path" => responses, consumed in order (the last one repeats) */
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

        public function count($path)
        {
            return count(array_filter($this->calls, function ($call) use ($path) {
                return parse_url($call['url'], PHP_URL_PATH) === $path;
            }));
        }
    }

    class SaArrayStore implements Siberian\ModuleUpdates\Store
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
}

const SA_TOKEN = '/wp-json/siberian/v1/oauth/token';
const SA_DEVICE = '/wp-json/siberian/v1/oauth/device_authorization';
const SA_REVOKE = '/wp-json/siberian/v1/oauth/revoke';
const SA_PURCHASES = '/wp-json/siberian/v1/purchases';

function sa_env(&$now = 1000000)
{
    $dir = sys_get_temp_dir() . '/sa-' . bin2hex(random_bytes(5));
    $transport = new SaFakeTransport();
    $store = new SaArrayStore();
    $service = new StoreAccount($transport, $store, $dir, 'my.example.com', function () use (&$now) {
        return $now;
    });

    return [$service, $transport, $store, $dir];
}

/** removes a folder the way a cache clear does */
function sa_rm($dir)
{
    if (!is_dir($dir)) {
        return;
    }
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST) as $item) {
        $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
    }
    rmdir($dir);
}

function sa_json($status, array $data, array $headers = [])
{
    return ['status' => $status, 'body' => json_encode($data), 'headers' => $headers];
}

function sa_tokens($access = 'sibat_one', $refresh = 'sibrt_one')
{
    return sa_json(200, ['access_token' => $access, 'refresh_token' => $refresh, 'token_type' => 'Bearer', 'expires_in' => 900, 'scope' => 'purchases:read offline_access']);
}

function sa_device()
{
    return sa_json(200, [
        'device_code' => 'sibdc_secret', 'user_code' => 'BCDF-GHJK',
        'verification_uri' => 'https://extensions.siberiancms.com/connect-siberian/',
        'verification_uri_complete' => 'https://extensions.siberiancms.com/connect-siberian/?user_code=BCDF-GHJK',
        'expires_in' => 900, 'interval' => 5,
    ]);
}

function sa_data(array $purchases = null)
{
    return [
        'customer' => ['email' => 'alice@example.com'],
        'generated_at' => '2026-10-25T10:00:00+00:00',
        'purchases' => $purchases ?? [[
            'order_id' => 1, 'number' => '1', 'date' => '2026-03-02', 'status' => 'complete', 'total' => 158.0, 'currency' => 'EUR', 'gateway' => 'stripe',
            'downloads' => [
                ['download_id' => 10, 'name' => 'Cabride', 'slug' => 'cabride', 'price' => 79.0, 'license' => ['key' => 'KEY-CABRIDE-0001', 'status' => 'active', 'lifetime' => false, 'expires' => '2027-03-02', 'activation_limit' => 1, 'activation_count' => 1, 'sites' => ['sk-abc']]],
                ['download_id' => 11, 'name' => 'Chat', 'slug' => 'chat', 'price' => 79.0, 'license' => ['key' => 'KEY-CHAT-0002', 'status' => 'inactive', 'lifetime' => false, 'expires' => '2027-03-02', 'activation_limit' => 1, 'activation_count' => 0, 'sites' => []]],
            ],
        ]],
    ];
}

/** connects and returns once tokens and the first purchases are stored */
function sa_connected(&$now = 1000000)
{
    [$svc, $http, $store, $dir] = sa_env($now);
    $http->script['POST ' . SA_DEVICE] = [sa_device()];
    $http->script['POST ' . SA_TOKEN] = [sa_tokens()];
    $http->script['GET ' . SA_PURCHASES] = [sa_json(200, sa_data(), ['etag' => '"v1"'])];
    $svc->startConnect();
    $svc->poll();

    return [$svc, $http, $store, $dir];
}

function sa_expect($code, callable $fn)
{
    try {
        $fn();
    } catch (StoreException $e) {
        t_equal($code, $e->getErrorCode(), 'error code (' . $e->getMessage() . ')');

        return $e;
    }
    throw new RuntimeException('expected ' . $code . ', nothing was thrown');
}

return [
    'startConnect sends the install id, label and scope and keeps the code' => function () {
        [$svc, $http, $store] = sa_env();
        $http->script['POST ' . SA_DEVICE] = [sa_device()];
        $pending = $svc->startConnect();
        t_equal('BCDF-GHJK', $pending['user_code'], 'user code');
        t_equal('https://extensions.siberiancms.com/connect-siberian/?user_code=BCDF-GHJK', $pending['verification_uri_complete'], 'link');
        parse_str($http->calls[0]['body'], $sent);
        t_equal('siberian-cms', $sent['client_id'], 'client id');
        t_equal('my.example.com', $sent['install_label'], 'label');
        t_equal('purchases:read offline_access', $sent['scope'], 'scope');
        t_true(preg_match('/^[a-f0-9]{32}$/', $sent['install_id']) === 1, 'install id');
        t_equal($sent['install_id'], $store->data[StoreAccount::K_INSTALL_ID], 'install id is kept');
        t_true(strpos($store->data[StoreAccount::K_PENDING], 'sibdc_secret') === false, 'device code is not stored in clear');
        t_equal('BCDF-GHJK', $svc->status()['pending']['user_code'], 'status shows the code');
        t_true(!array_key_exists('device_code', $svc->status()['pending']), 'status never exposes the device code');
    },

    'the install id stays the same across connections' => function () {
        [$svc, $http, $store] = sa_env();
        $http->script['POST ' . SA_DEVICE] = [sa_device()];
        $svc->startConnect();
        $svc->startConnect();
        parse_str($http->calls[0]['body'], $a);
        parse_str($http->calls[1]['body'], $b);
        t_equal($a['install_id'], $b['install_id'], 'same id');
    },

    'startConnect reports an unreachable store' => function () {
        [$svc] = sa_env();
        sa_expect('store_unavailable', function () use ($svc) {
            $svc->startConnect();
        });
    },

    'poll waits while the customer has not approved, and slows down when told' => function () {
        [$svc, $http] = sa_env();
        $http->script['POST ' . SA_DEVICE] = [sa_device()];
        $http->script['POST ' . SA_TOKEN] = [sa_json(400, ['error' => 'authorization_pending']), sa_json(400, ['error' => 'slow_down'])];
        $svc->startConnect();
        t_equal('pending', $svc->poll()['state'], 'pending');
        $second = $svc->poll();
        t_equal('pending', $second['state'], 'still pending');
        t_equal(10, $second['interval'], 'interval grew');
        t_equal(false, $svc->status()['connected'], 'not connected');
    },

    'poll turns an approval into a connection, tokens encrypted, purchases loaded' => function () {
        [$svc, $http, $store, $dir] = sa_connected();
        $status = $svc->status();
        t_equal(true, $status['connected'], 'connected');
        t_equal('alice@example.com', $status['email'], 'email from the cache');
        t_equal(null, $status['pending'], 'no code left');
        t_true(strpos($store->data[StoreAccount::K_TOKENS], 'sibat_') === false && strpos($store->data[StoreAccount::K_TOKENS], 'sibrt_') === false, 'tokens are encrypted');
        t_true(is_file($dir . '/store-account.key') && (fileperms($dir . '/store-account.key') & 0077) === 0, 'key file is private');
        t_true(in_array('Authorization: Bearer sibat_one', $http->calls[2]['headers'], true), 'bearer sent');
        t_true(in_array('X-Authorization: Bearer sibat_one', $http->calls[2]['headers'], true), 'fallback header sent');
        t_true(in_array('User-Agent: SiberianCMS-StoreAccount/1.0', $http->calls[2]['headers'], true), 'own user agent on reads');
        t_true(in_array('User-Agent: SiberianCMS-StoreAccount/1.0', $http->calls[0]['headers'], true), 'own user agent on posts');
        $purchases = $svc->purchases();
        t_equal(2, count($purchases['purchases'][0]['downloads']), 'one purchase, two downloads');
        t_equal('KEY-CABRIDE-0001', $purchases['purchases'][0]['downloads'][0]['license']['key'], 'license key');
        t_equal(['sk-abc'], $purchases['purchases'][0]['downloads'][0]['license']['sites'], 'linked site');
    },

    'denied and expired codes end the dance' => function () {
        [$svc, $http] = sa_env();
        $http->script['POST ' . SA_DEVICE] = [sa_device()];
        $http->script['POST ' . SA_TOKEN] = [sa_json(400, ['error' => 'access_denied'])];
        $svc->startConnect();
        t_equal('denied', $svc->poll()['state'], 'denied');
        t_equal(null, $svc->status()['pending'], 'cleared');
        t_equal('none', $svc->poll()['state'], 'nothing to poll');

        $now = 1000000;
        [$svc, $http] = sa_env($now);
        $http->script['POST ' . SA_DEVICE] = [sa_device()];
        $svc->startConnect();
        $now += 901;
        t_equal('expired', $svc->poll()['state'], 'expired locally');
    },

    'purchases is served from the cache for a day without calling the store' => function () {
        $now = 1000000;
        [$svc, $http] = sa_connected($now);
        $before = $http->count(SA_PURCHASES);
        $now += 3600;
        $svc->purchases();
        t_equal($before, $http->count(SA_PURCHASES), 'no call within a day');
        $now += 90000;
        $http->script['GET ' . SA_PURCHASES] = [['status' => 304]];
        $result = $svc->purchases();
        t_equal($before + 1, $http->count(SA_PURCHASES), 'one call after a day');
        $last = end($http->calls);
        t_true(in_array('If-None-Match: "v1"', $last['headers'], true), 'conditional request');
        t_equal(false, $result['stale'], '304 is fresh');
        t_equal($now, $result['synced_at'], '304 renews the sync time');
    },

    'refresh=true asks the store now and replaces the copy' => function () {
        $now = 1000000;
        [$svc, $http] = sa_connected($now);
        $http->script['GET ' . SA_PURCHASES] = [sa_json(200, sa_data([]), ['etag' => '"v2"'])];
        $result = $svc->purchases(true);
        t_equal([], $result['purchases'], 'new list');
    },

    'the access token is renewed silently and the refresh token rotates' => function () {
        $now = 1000000;
        [$svc, $http] = sa_connected($now);
        $now += 90000;  // access token (15 min) and cache (1 day) are both old
        $http->script['POST ' . SA_TOKEN] = [sa_tokens('sibat_two', 'sibrt_two')];
        $http->script['GET ' . SA_PURCHASES] = [['status' => 304]];
        $svc->purchases();
        $refresh = array_values(array_filter($http->calls, function ($c) {
            return strpos((string) $c['body'], 'grant_type=refresh_token') !== false;
        }));
        t_equal(1, count($refresh), 'one refresh');
        parse_str($refresh[0]['body'], $sent);
        t_equal('sibrt_one', $sent['refresh_token'], 'old refresh token presented');
        $last = end($http->calls);
        t_true(in_array('Authorization: Bearer sibat_two', $last['headers'], true), 'new access token used');
        $now += 90000;
        $http->script['POST ' . SA_TOKEN] = [sa_tokens('sibat_three', 'sibrt_three')];
        $svc->purchases();
        $refresh = array_values(array_filter($http->calls, function ($c) {
            return strpos((string) $c['body'], 'grant_type=refresh_token') !== false;
        }));
        parse_str($refresh[1]['body'], $sent);
        t_equal('sibrt_two', $sent['refresh_token'], 'the rotated token is the one used next');
    },

    'a store that is down never hides the copy and never disconnects' => function () {
        $now = 1000000;
        [$svc, $http] = sa_connected($now);
        $now += 90000;
        $http->script['POST ' . SA_TOKEN] = [['status' => 503, 'body' => '']];
        $result = $svc->purchases();
        t_equal(true, $result['stale'], 'flagged stale');
        t_equal('store_unavailable', $result['error'], 'error code');
        t_equal(2, count($result['purchases'][0]['downloads']), 'copy still there');
        t_equal(true, $svc->status()['connected'], 'still connected');
    },

    'after a failure the store is left alone for a few minutes' => function () {
        $now = 1000000;
        [$svc, $http] = sa_connected($now);
        $now += 90000;
        $http->script['POST ' . SA_TOKEN] = [['status' => 503, 'body' => '']];
        $svc->purchases();
        $calls = count($http->calls);
        $now += 60;
        $svc->purchases();
        t_equal($calls, count($http->calls), 'no new call inside the back-off');
        $now += 400;
        $svc->purchases();
        t_true(count($http->calls) > $calls, 'tries again later');
    },

    'a first load with the store down is an error, not an empty list' => function () {
        [$svc, $http] = sa_env();
        $http->script['POST ' . SA_DEVICE] = [sa_device()];
        $http->script['POST ' . SA_TOKEN] = [sa_tokens()];
        $svc->startConnect();
        $svc->poll();   // connected, but the first purchases call fails (no script for it)
        sa_expect('store_unavailable', function () use ($svc) {
            $svc->purchases();
        });
        t_equal(true, $svc->status()['connected'], 'connection kept');
    },

    'a connection revoked on the store asks to reconnect and keeps nothing secret' => function () {
        $now = 1000000;
        [$svc, $http, $store] = sa_connected($now);
        $now += 90000;
        $http->script['POST ' . SA_TOKEN] = [sa_json(400, ['error' => 'invalid_grant'])];
        $result = $svc->purchases();
        t_equal('revoked', $result['error'], 'revoked');
        t_equal(true, $result['needs_reconnect'], 'needs reconnect');
        $status = $svc->status();
        t_equal(false, $status['connected'], 'not connected anymore');
        t_equal(true, $status['needs_reconnect'], 'status says so');
        t_equal(null, $store->get(StoreAccount::K_TOKENS), 'tokens gone');
        sa_expect('not_connected', function () use ($svc) {
            $svc->purchases();
        });
    },

    'a 401 on the purchases forces one renewal and one retry' => function () {
        $now = 1000000;
        [$svc, $http] = sa_connected($now);
        $now += 90000;
        $http->script['GET ' . SA_PURCHASES] = [['status' => 401], sa_json(200, sa_data([]), ['etag' => '"v3"'])];
        $http->script['POST ' . SA_TOKEN] = [sa_tokens('sibat_a', 'sibrt_a'), sa_tokens('sibat_b', 'sibrt_b')];
        $result = $svc->purchases();
        t_equal([], $result['purchases'], 'second attempt served');
        t_equal(null, $result['error'], 'no error');
        $last = end($http->calls);
        t_true(in_array('Authorization: Bearer sibat_b', $last['headers'], true) && in_array('X-Authorization: Bearer sibat_b', $last['headers'], true), 'retry uses the renewed token on both headers');
    },

    'disconnect revokes at the store and forgets everything' => function () {
        [$svc, $http, $store, $dir] = sa_connected();
        $svc->disconnect();
        t_equal(false, $svc->status()['connected'], 'not connected');
        t_equal(null, $svc->status()['email'], 'no cached email');
        t_true(!is_file($dir . '/purchases.json'), 'cache file removed');
        sa_expect('not_connected', function () use ($svc) {
            $svc->purchases();
        });
    },

    'disconnect sends the refresh token to the revoke endpoint' => function () {
        [$svc, $http] = sa_connected();
        $http->script['POST ' . SA_REVOKE] = [sa_json(200, [])];
        $result = $svc->disconnect();
        t_equal(true, $result['revoked_remotely'], 'revoked remotely');
        $revoke = end($http->calls);
        t_equal(SA_REVOKE, parse_url($revoke['url'], PHP_URL_PATH), 'revoke endpoint');
        parse_str($revoke['body'], $sent);
        t_equal('sibrt_one', $sent['token'], 'refresh token revoked');
    },

    'disconnect still forgets locally when the store is unreachable' => function () {
        [$svc, $http, $store] = sa_connected();
        $result = $svc->disconnect();   // the revoke call gets no answer
        t_equal(false, $result['revoked_remotely'], 'not revoked remotely');
        t_equal(false, $svc->status()['connected'], 'forgotten locally');
        t_equal(null, $store->get(StoreAccount::K_TOKENS), 'tokens gone');
    },

    // A cache clear / module update empties the folder that holds the key: the saved tokens can never be opened again.
    'a folder emptied behind our back asks to reconnect instead of silently showing "not connected"' => function () {
        $now = 1000000;
        [$svc, $http, $store, $dir] = sa_connected($now);
        t_equal(true, $svc->status()['connected'], 'connected before the wipe');
        sa_rm($dir);
        $after = new StoreAccount($http, $store, $dir, 'my.example.com', function () use (&$now) {
            return $now;
        });
        $status = $after->status();
        t_equal(false, $status['connected'], 'not connected: the tokens are unreadable');
        t_equal(true, $status['needs_reconnect'], 'the page is told to ask for a reconnection');
        t_equal(null, $store->get(StoreAccount::K_TOKENS), 'the unreadable tokens are dropped');
        sa_expect('not_connected', function () use ($after) {
            $after->purchases();
        });
        // and connecting again works with the new key
        $http->script['POST ' . SA_DEVICE] = [sa_device()];
        $http->script['POST ' . SA_TOKEN] = [sa_tokens('sibat_new', 'sibrt_new')];
        $http->script['GET ' . SA_PURCHASES] = [sa_json(200, sa_data(), ['etag' => '"v1"'])];
        $after->startConnect();
        t_equal('connected', $after->poll()['state'], 'reconnected');
        $final = $after->status();
        t_equal(true, $final['connected'], 'connected again');
        t_equal(false, $final['needs_reconnect'], 'no reconnect flag any more');
    },

    'a first connection on a fresh install does not raise the reconnect flag' => function () {
        [$svc, $http] = sa_env();
        $http->script['POST ' . SA_DEVICE] = [sa_device()];
        $status = $svc->status();
        t_equal(false, $status['connected'], 'not connected');
        t_equal(false, $status['needs_reconnect'], 'nothing to reconnect');
        $svc->startConnect();   // creates the key
        t_equal(false, $svc->status()['needs_reconnect'], 'still nothing to reconnect after the key is created');
    },

    'the key and the cache of the old folder (var/cache) are moved to the private folder' => function () {
        $now = 1000000;
        $legacy = sys_get_temp_dir() . '/sa-legacy-' . bin2hex(random_bytes(4));
        $new = sys_get_temp_dir() . '/sa-new-' . bin2hex(random_bytes(4));
        $http = new SaFakeTransport();
        $store = new SaArrayStore();
        $clock = function () use (&$now) {
            return $now;
        };
        // connected the way 5.1.x did it: everything in the old folder
        $old = new StoreAccount($http, $store, $legacy, 'my.example.com', $clock);
        $http->script['POST ' . SA_DEVICE] = [sa_device()];
        $http->script['POST ' . SA_TOKEN] = [sa_tokens()];
        $http->script['GET ' . SA_PURCHASES] = [sa_json(200, sa_data(), ['etag' => '"v1"'])];
        $old->startConnect();
        $old->poll();
        t_true(is_file($legacy . '/store-account.key') && is_file($legacy . '/purchases.json'), 'key and cache are in the old folder');

        $upgraded = new StoreAccount($http, $store, $new, 'my.example.com', $clock, StoreAccount::BASE_URL, $legacy);
        $status = $upgraded->status();
        t_equal(true, $status['connected'], 'the connection survives the move');
        t_equal(false, $status['needs_reconnect'], 'no reconnect needed');
        t_equal('alice@example.com', $status['email'], 'the purchases copy came along');
        t_true(is_file($new . '/store-account.key') && (fileperms($new . '/store-account.key') & 0077) === 0, 'private key now in the new folder');
        t_true(!is_file($legacy . '/store-account.key'), 'no second copy of the key is left in the old folder');
        t_true(strpos((string) file_get_contents($new . '/.htaccess'), 'denied') !== false, 'the new folder is closed to the web server'); // FRAGILE-OK: asserts the deny rule we write ourselves in ensureDir(); fails loudly if it changes
    },

    'the application keeps the store account folder out of var/cache' => function () {
        // Backoffice_Model_StoreAccount needs the whole framework, so this guards the one line that decides where the key lives.
        $source = (string) file_get_contents(dirname(__DIR__, 3) . '/siberian/app/sae/modules/Backoffice/Model/StoreAccount.php');
        t_true(strpos($source, "getBasePathTo('var/private/store-account')") !== false, 'the folder is var/private/store-account'); // FRAGILE-OK: the model needs the whole framework to load, this is the only guard of where the key lives
        t_true(preg_match("/new StoreAccount\\([^;]*getBasePathTo\\('var\\/cache\\/store-account'\\)\\s*\\);/s", $source) === 1, 'var/cache/store-account is only passed as the old folder to migrate from');
        // Siberian_Cache empties these, so nothing that has to survive may live in them.
        $cache = (string) file_get_contents(dirname(__DIR__, 3) . '/siberian/lib/Siberian/Cache.php');
        t_true(strpos($cache, "'/var/private") === false && strpos($cache, '"/var/private') === false, 'Siberian_Cache does not touch var/private'); // FRAGILE-OK: Siberian_Cache cannot be exercised here, a source check fails loudly if someone adds var/private to it
    },

    'only https is used for the store' => function () {
        $svc = new StoreAccount(new Siberian\ModuleUpdates\CurlTransport(), new SaArrayStore(), sys_get_temp_dir() . '/sa-http-' . bin2hex(random_bytes(4)), 'x', null, 'http://extensions.example.com');
        sa_expect('insecure_url', function () use ($svc) {
            $svc->startConnect();
        });
    },
];
