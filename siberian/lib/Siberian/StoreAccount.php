<?php

namespace Siberian;

use Siberian\ModuleUpdates\Exception;
use Siberian\ModuleUpdates\Store;
use Siberian\ModuleUpdates\Transport;

/**
 * The customer's account on https://extensions.siberiancms.com, read only: purchases and license keys, shown in
 * Settings > Advanced > Store account. Nothing else in Siberian uses this data.
 *
 * OAuth 2.0 device authorization grant (RFC 8628): the admin gets a code, approves it on the store with their own
 * login, and this install keeps a refresh token until it is disconnected here or revoked on the store. No store
 * password and no store admin key ever reach Siberian. Server side: Xtraball/siberian-edd-purchases-api.
 *
 *  - startConnect() / poll()   the connect dance
 *  - purchases()               cached copy, refreshed in the background when older than a day or on demand;
 *                              a failing store never hides the cached copy and never disconnects anything
 *  - disconnect()              revokes at the store (best effort) and forgets everything locally
 *
 * Tokens are stored encrypted with a key file kept outside the database, so a database dump alone is not enough.
 */
class StoreAccount
{
    const BASE_URL = 'https://extensions.siberiancms.com';

    const CLIENT_ID = 'siberian-cms';

    const SCOPE = 'purchases:read offline_access';

    const USER_AGENT = 'User-Agent: SiberianCMS-StoreAccount/1.0';

    /**
     * The cached purchases are refreshed when older than this and the page is opened (seconds).
     */
    const FRESH_MAX_AGE = 86400;

    /**
     * After a failed refresh, wait this long before asking again (seconds).
     */
    const ERROR_BACKOFF = 300;

    const K_INSTALL_ID = 'store_account_install_id';
    const K_TOKENS = 'store_account_tokens';
    const K_PENDING = 'store_account_pending';
    const K_STATE = 'store_account_state';

    /** @var Transport */
    private $transport;

    /** @var Store */
    private $store;

    /** @var string */
    private $dir;

    /** @var string */
    private $label;

    /** @var callable */
    private $clock;

    /** @var string */
    private $baseUrl;

    /** @var string|null */
    private $legacyDir;

    /**
     * @param Transport $transport
     * @param Store $store small values (install id, tokens, pending code, last error)
     * @param string $dir private folder for the encryption key, the purchases cache and the lock. It must be a folder
     *                    that "clear cache" and module updates do not empty: the saved tokens are unreadable without the key
     * @param string $label shown to the customer when approving (the host of this install)
     * @param callable|null $clock
     * @param string $baseUrl
     * @param string|null $legacyDir where the folder used to be (var/cache/store-account, emptied by every cache clear);
     *                               a key and a purchases copy that survived there are moved to $dir, once
     */
    public function __construct(Transport $transport, Store $store, $dir, $label = '', $clock = null, $baseUrl = self::BASE_URL, $legacyDir = null)
    {
        $this->transport = $transport;
        $this->store = $store;
        $this->dir = rtrim($dir, '/');
        $this->label = (string) $label;
        $this->clock = $clock;
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->legacyDir = $legacyDir === null ? null : rtrim($legacyDir, '/');
    }

    /**
     * @return array connected, email, synced_at, pending (user_code, verification_uri, verification_uri_complete, expires_at) or null
     */
    public function status()
    {
        // Opening the tokens comes first: if the key was lost, that is the call that notices it and flags the reconnection.
        $connected = $this->readTokens() !== null;
        $cache = $this->readCache();
        $state = $this->readState();
        $pending = $this->readPending();

        return [
            'connected' => $connected,
            'needs_reconnect' => !empty($state['revoked']),
            'email' => isset($cache['data']['customer']['email']) ? $cache['data']['customer']['email'] : null,
            'synced_at' => isset($cache['fetched_at']) ? (int) $cache['fetched_at'] : null,
            'pending' => $pending === null ? null : [
                'user_code' => $pending['user_code'],
                'verification_uri' => $pending['verification_uri'],
                'verification_uri_complete' => $pending['verification_uri_complete'],
                'expires_at' => $pending['expires_at'],
                'interval' => $pending['interval'],
            ],
        ];
    }

    /**
     * Asks the store for a code. Replaces any code that was waiting.
     *
     * @return array the same pending block as status()
     * @throws Exception
     */
    public function startConnect()
    {
        $response = $this->post('/wp-json/siberian/v1/oauth/device_authorization', [
            'client_id' => self::CLIENT_ID,
            'install_id' => $this->installId(),
            'install_label' => $this->label,
            'scope' => self::SCOPE,
        ]);
        $body = $this->json($response);
        if ($response['status'] !== 200 || empty($body['device_code']) || empty($body['user_code'])) {
            throw new Exception('store_unavailable', $this->reason($response, 'The store could not start the connection.'));
        }

        $now = $this->now();
        $this->writePending([
            'device_code' => $body['device_code'],
            'user_code' => (string) $body['user_code'],
            'verification_uri' => (string) $body['verification_uri'],
            'verification_uri_complete' => (string) (isset($body['verification_uri_complete']) ? $body['verification_uri_complete'] : $body['verification_uri']),
            'expires_at' => $now + (int) $body['expires_in'],
            'interval' => max(5, (int) (isset($body['interval']) ? $body['interval'] : 5)),
        ]);
        $this->writeState([]);

        return $this->status()['pending'];
    }

    /**
     * One poll of the token endpoint. The page calls this every `interval` seconds.
     *
     * @return array state: none | pending | connected | denied | expired
     */
    public function poll()
    {
        $pending = $this->readPending();
        if ($pending === null) {
            return ['state' => 'none'];
        }
        if ($pending['expires_at'] < $this->now()) {
            $this->writePending(null);

            return ['state' => 'expired'];
        }

        $response = $this->post('/wp-json/siberian/v1/oauth/token', [
            'client_id' => self::CLIENT_ID,
            'grant_type' => 'urn:ietf:params:oauth:grant-type:device_code',
            'device_code' => $pending['device_code'],
        ]);
        $body = $this->json($response);

        if ($response['status'] === 200 && !empty($body['access_token'])) {
            $this->saveTokens($body);
            $this->writePending(null);
            $this->writeState([]);
            try {
                $this->purchases(true);
            } catch (Exception $e) {
                // Connected anyway, the list loads on the next view.
            }

            return ['state' => 'connected'];
        }

        $error = isset($body['error']) ? $body['error'] : '';
        if ($error === 'access_denied') {
            $this->writePending(null);

            return ['state' => 'denied'];
        }
        if ($error === 'expired_token' || $error === 'invalid_grant') {
            $this->writePending(null);

            return ['state' => 'expired'];
        }
        if ($error === 'slow_down') {
            $pending['interval'] += 5;
            $this->writePending($pending);
        }

        return ['state' => 'pending', 'interval' => $pending['interval'], 'unreachable' => $response['status'] === 0];
    }

    /**
     * The purchases of the connected account.
     *
     * @param bool $force ask the store now even if the copy is recent
     * @return array connected, email, purchases, synced_at, stale (the last refresh failed), error (code) or null
     * @throws Exception not_connected, or the store failed and there is no copy yet
     */
    public function purchases($force = false)
    {
        if ($this->readTokens() === null) {
            throw new Exception('not_connected', 'Connect your account on the store first.');
        }

        $cache = $this->readCache();
        $state = $this->readState();
        $now = $this->now();
        $age = $cache === null ? PHP_INT_MAX : $now - $cache['fetched_at'];
        $backingOff = !$force && isset($state['error_at']) && $now - $state['error_at'] < self::ERROR_BACKOFF;

        if ($cache === null || $force || ($age >= self::FRESH_MAX_AGE && !$backingOff)) {
            try {
                $cache = $this->refreshPurchases($cache);
                $this->writeState([]);
            } catch (Exception $e) {
                $this->writeState(['error' => $e->getErrorCode(), 'error_at' => $now] + $this->readState());
                if ($cache === null) {
                    throw $e;
                }

                return $this->shape($cache, $e->getErrorCode());
            }
        }

        $state = $this->readState();

        return $this->shape($cache, isset($state['error']) ? $state['error'] : null);
    }

    /**
     * Forgets this connection. The store is told (best effort), local data goes either way.
     *
     * @return array revoked_remotely
     */
    public function disconnect()
    {
        $revoked = false;
        $tokens = $this->readTokens();
        if ($tokens !== null) {
            $response = $this->post('/wp-json/siberian/v1/oauth/revoke', [
                'client_id' => self::CLIENT_ID,
                'token' => $tokens['refresh'],
            ]);
            $revoked = $response['status'] === 200;
        }
        $this->store->set(self::K_TOKENS, '');
        $this->writePending(null);
        $this->writeState([]);
        $file = $this->dir . '/purchases.json';
        if (is_file($file)) {
            @unlink($file);
        }

        return ['revoked_remotely' => $revoked];
    }

    // --- tokens ---------------------------------------------------------------------------------------

    /**
     * @param array|null $cache
     * @return array
     * @throws Exception
     */
    private function refreshPurchases($cache)
    {
        $token = $this->accessToken();
        // X-Authorization carries the same token: some Apache + PHP-FPM setups drop the Authorization header.
        // The store sits behind NinjaFirewall, which refuses the default curl User-Agent as a scanner: always send our own.
        $headers = ['Accept: application/json', self::USER_AGENT, 'Authorization: Bearer ' . $token, 'X-Authorization: Bearer ' . $token];
        if ($cache !== null && !empty($cache['etag'])) {
            $headers[] = 'If-None-Match: ' . $cache['etag'];
        }
        $response = $this->transport->request('GET', $this->baseUrl . '/wp-json/siberian/v1/purchases', $headers, null, 15, 8388608);

        if ($response['status'] === 401) {
            // The access token was refused before its time (revoked and reissued elsewhere): one forced refresh.
            $fresh = $this->accessToken(true);
            $headers[2] = 'Authorization: Bearer ' . $fresh;
            $headers[3] = 'X-Authorization: Bearer ' . $fresh;
            $response = $this->transport->request('GET', $this->baseUrl . '/wp-json/siberian/v1/purchases', $headers, null, 15, 8388608);
        }

        if ($response['status'] === 304 && $cache !== null) {
            $cache['fetched_at'] = $this->now();
        } elseif ($response['status'] === 200) {
            $data = $this->json($response);
            if (!isset($data['purchases']) || !is_array($data['purchases'])) {
                throw new Exception('store_invalid', 'The store answered with something unexpected.');
            }
            $etag = isset($response['headers']['etag']) ? $response['headers']['etag'] : '';
            $cache = ['fetched_at' => $this->now(), 'etag' => $etag, 'data' => $data];
        } elseif ($response['status'] === 401 || $response['status'] === 403) {
            $this->markRevoked();
            throw new Exception('revoked', 'The store closed this connection. Connect your account again.');
        } else {
            throw new Exception('store_unavailable', $this->reason($response, 'The store is not reachable right now.'));
        }

        $this->writeCache($cache);

        return $cache;
    }

    /**
     * A valid access token, renewing it silently when needed. One renewal at a time: the rotating refresh token
     * must never be used twice in parallel.
     *
     * @param bool $force
     * @return string
     * @throws Exception
     */
    private function accessToken($force = false)
    {
        $lock = $this->lock();
        try {
            $tokens = $this->readTokens();
            if ($tokens === null) {
                throw new Exception('not_connected', 'Connect your account on the store first.');
            }
            if (!$force && $tokens['access_expires'] - 30 > $this->now()) {
                return $tokens['access'];
            }

            $response = $this->post('/wp-json/siberian/v1/oauth/token', [
                'client_id' => self::CLIENT_ID,
                'grant_type' => 'refresh_token',
                'refresh_token' => $tokens['refresh'],
            ]);
            $body = $this->json($response);
            if ($response['status'] === 200 && !empty($body['access_token']) && !empty($body['refresh_token'])) {
                $this->saveTokens($body);

                return $body['access_token'];
            }
            if (isset($body['error']) && $body['error'] === 'invalid_grant') {
                $this->markRevoked();
                throw new Exception('revoked', 'The store closed this connection. Connect your account again.');
            }
            throw new Exception('store_unavailable', $this->reason($response, 'The store is not reachable right now.'));
        } finally {
            $this->unlock($lock);
        }
    }

    private function markRevoked()
    {
        $this->store->set(self::K_TOKENS, '');
        $this->writeState(['revoked' => true]);
    }

    private function saveTokens(array $body)
    {
        $previous = $this->readTokens();
        $this->store->set(self::K_TOKENS, $this->seal(json_encode([
            'access' => $body['access_token'],
            'access_expires' => $this->now() + (int) $body['expires_in'],
            'refresh' => isset($body['refresh_token']) ? $body['refresh_token'] : ($previous !== null ? $previous['refresh'] : ''),
        ])));
    }

    /**
     * @return array|null access, access_expires, refresh
     */
    private function readTokens()
    {
        $sealed = $this->store->get(self::K_TOKENS);
        if ($sealed === null) {
            return null;
        }
        $json = $this->open($sealed);
        $tokens = $json === null ? null : json_decode($json, true);

        return is_array($tokens) && !empty($tokens['refresh']) ? $tokens : null;
    }

    // --- pending code and state -----------------------------------------------------------------------

    private function readPending()
    {
        $sealed = $this->store->get(self::K_PENDING);
        if ($sealed === null) {
            return null;
        }
        $json = $this->open($sealed);
        $pending = $json === null ? null : json_decode($json, true);

        return is_array($pending) && isset($pending['device_code']) ? $pending : null;
    }

    private function writePending($pending)
    {
        $this->store->set(self::K_PENDING, $pending === null ? '' : $this->seal(json_encode($pending)));
    }

    private function readState()
    {
        $state = json_decode((string) $this->store->get(self::K_STATE), true);

        return is_array($state) ? $state : [];
    }

    private function writeState(array $state)
    {
        $this->store->set(self::K_STATE, $state === [] ? '' : json_encode($state));
    }

    /**
     * @return string stable random id of this install, sent so the customer can tell installs apart
     */
    private function installId()
    {
        $id = $this->store->get(self::K_INSTALL_ID);
        if ($id === null || !preg_match('/^[a-f0-9]{32}$/', $id)) {
            $id = bin2hex(random_bytes(16));
            $this->store->set(self::K_INSTALL_ID, $id);
        }

        return $id;
    }

    // --- purchases cache (file) -----------------------------------------------------------------------

    private function readCache()
    {
        $this->adoptLegacy();
        $file = $this->dir . '/purchases.json';
        if (!is_file($file)) {
            return null;
        }
        $cache = json_decode((string) file_get_contents($file), true);

        return is_array($cache) && isset($cache['fetched_at'], $cache['data']) ? $cache : null;
    }

    private function writeCache(array $cache)
    {
        $this->ensureDir();
        $file = $this->dir . '/purchases.json';
        $tmp = $file . '.' . bin2hex(random_bytes(4)) . '.tmp';
        file_put_contents($tmp, json_encode($cache));
        @chmod($tmp, 0600);
        rename($tmp, $file);
    }

    private function shape(array $cache, $error)
    {
        $data = $cache['data'];

        return [
            'connected' => $this->readTokens() !== null,
            'needs_reconnect' => $error === 'revoked',
            'email' => isset($data['customer']['email']) ? $data['customer']['email'] : null,
            'purchases' => $data['purchases'],
            'synced_at' => (int) $cache['fetched_at'],
            'stale' => $error !== null,
            'error' => $error,
        ];
    }

    // --- crypto, files, http --------------------------------------------------------------------------

    private function ensureDir()
    {
        if (!is_dir($this->dir)) {
            mkdir($this->dir, 0700, true);
        }
        // The key lives here: on Apache, never serve this folder.
        $deny = $this->dir . '/.htaccess';
        if (!is_file($deny)) {
            @file_put_contents($deny, "Require all denied\n<IfModule !mod_authz_core.c>\n    Deny from all\n</IfModule>\n");
        }
    }

    /**
     * The folder used to be var/cache/store-account, which "clear cache" and every module update empty: the key went with it
     * and the saved tokens, which only that key can open, were lost. Whatever survived there (a cache clear that did not run
     * yet, an install that has not been cleaned since the update) is moved to the private folder, once.
     */
    private function adoptLegacy()
    {
        if ($this->legacyDir === null || $this->legacyDir === $this->dir || !is_dir($this->legacyDir)) {
            return;
        }
        $moved = false;
        foreach (['store-account.key', 'purchases.json'] as $name) {
            $from = $this->legacyDir . '/' . $name;
            $to = $this->dir . '/' . $name;
            if (!is_file($from)) {
                continue;
            }
            if (!is_file($to)) {
                $this->ensureDir();
                $tmp = $to . '.' . bin2hex(random_bytes(4)) . '.tmp';
                if (@copy($from, $tmp)) {
                    @chmod($tmp, 0600);
                    // link() fails if another request moved it first, in which case theirs wins.
                    @link($tmp, $to);
                    $moved = true;
                }
                @unlink($tmp);
            }
            if (is_file($to)) {
                @unlink($from);
            }
        }
        if ($moved) {
            @rmdir($this->legacyDir);
        }
    }

    /**
     * A key was just created while saved tokens exist: the key that sealed them is gone (the folder was emptied), so they can
     * never be read again. Say so, instead of showing a silent "not connected".
     */
    private function noteNewKey()
    {
        $held = $this->store->get(self::K_TOKENS);
        if ($held !== null && $held !== '') {
            $this->store->set(self::K_TOKENS, '');
            $this->writeState(['revoked' => true, 'key_lost' => true]);
        }
        $pending = $this->store->get(self::K_PENDING);
        if ($pending !== null && $pending !== '') {
            $this->store->set(self::K_PENDING, '');
        }
    }

    private function key()
    {
        $this->ensureDir();
        $this->adoptLegacy();
        $file = $this->dir . '/store-account.key';
        if (!is_file($file)) {
            $tmp = $file . '.' . bin2hex(random_bytes(4)) . '.tmp';
            file_put_contents($tmp, random_bytes(SODIUM_CRYPTO_SECRETBOX_KEYBYTES));
            @chmod($tmp, 0600);
            // link() fails if another request created the key first, in which case theirs wins.
            $created = @link($tmp, $file);
            @unlink($tmp);
            if ($created) {
                $this->noteNewKey();
            }
        }
        $key = (string) file_get_contents($file);
        if (strlen($key) !== SODIUM_CRYPTO_SECRETBOX_KEYBYTES) {
            throw new Exception('key_unreadable', 'The encryption key of the store account is unreadable.');
        }

        return $key;
    }

    private function seal($plain)
    {
        $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);

        return base64_encode($nonce . sodium_crypto_secretbox($plain, $nonce, $this->key()));
    }

    private function open($sealed)
    {
        $raw = base64_decode($sealed, true);
        if ($raw === false || strlen($raw) <= SODIUM_CRYPTO_SECRETBOX_NONCEBYTES) {
            return null;
        }
        $plain = sodium_crypto_secretbox_open(
            substr($raw, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES),
            substr($raw, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES),
            $this->key()
        );

        return $plain === false ? null : $plain;
    }

    private function lock()
    {
        $this->ensureDir();
        $handle = fopen($this->dir . '/store-account.lock', 'c');
        if ($handle !== false) {
            flock($handle, LOCK_EX);
        }

        return $handle;
    }

    private function unlock($handle)
    {
        if ($handle) {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }

    private function post($path, array $fields)
    {
        return $this->transport->request('POST', $this->baseUrl . $path, [
            'Content-Type: application/x-www-form-urlencoded',
            'Accept: application/json',
            self::USER_AGENT,
        ], http_build_query($fields), 15);
    }

    private function json(array $response)
    {
        $body = json_decode($response['body'], true);

        return is_array($body) ? $body : [];
    }

    private function reason(array $response, $fallback)
    {
        $body = $this->json($response);

        return isset($body['error_description']) && is_string($body['error_description']) ? $body['error_description'] : $fallback;
    }

    private function now()
    {
        return $this->clock === null ? time() : (int) call_user_func($this->clock);
    }
}
