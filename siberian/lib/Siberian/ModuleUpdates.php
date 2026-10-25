<?php

namespace Siberian;

use Siberian\ModuleUpdates\Exception;
use Siberian\ModuleUpdates\Store;
use Siberian\ModuleUpdates\Transport;

/**
 * Module updates, server side. The downloader (https://downloader.siberiancms.com) publishes one
 * cached JSON with every module version; the comparison happens here, on this server.
 *
 *  - refresh()     conditional fetch of the list, cached, served stale for up to 7 days when the
 *                  downloader is down
 *  - pending()     installed modules that have a newer, compatible version
 *  - terms         the live terms must be accepted inside Siberian, a changed hash asks again
 *  - download()    licences + accepted terms hash go out, the package comes back signed; the
 *                  sha256 and the Ed25519 signature are verified before anything is written
 *
 * Installation itself reuses the existing installer, see Installer_Backoffice_ModuleController.
 */
class ModuleUpdates
{
    const BASE_URL = 'https://downloader.siberiancms.com';

    /**
     * Ed25519 public keys (key id => base64) the downloader signs packages with. Pinned on
     * purpose: the keys are never taken from the network. Filled in once the downloader signing
     * key exists (see LOGBOOK/features/feat-module-updates.md); until then downloads are refused.
     */
    const PINNED_KEYS = [];

    const STALE_MAX_AGE = 604800; // 7 days
    const FRESH_MAX_AGE = 86400;  // the daily cron refreshes it
    const MANIFEST_SCHEMA = 1;

    /**
     * @var Transport
     */
    private $transport;

    /**
     * @var Store
     */
    private $store;

    /**
     * @var string
     */
    private $cacheFile;

    /**
     * @var string
     */
    private $siberianVersion;

    /**
     * @var array
     */
    private $keys;

    /**
     * @var callable
     */
    private $clock;

    /**
     * @var string
     */
    private $baseUrl;

    /**
     * @param Transport $transport
     * @param Store $store
     * @param string $cacheFile Where the module list is cached (var/cache/...)
     * @param string $siberianVersion Running Siberian version
     * @param array $keys Pinned public keys, key id => base64
     * @param callable|null $clock Returns the unix time
     * @param string $baseUrl Downloader base URL, only changed for tests (see Installer_Model_ModuleUpdates)
     */
    public function __construct(Transport $transport, Store $store, $cacheFile, $siberianVersion, array $keys = self::PINNED_KEYS, $clock = null, $baseUrl = self::BASE_URL)
    {
        $this->transport = $transport;
        $this->store = $store;
        $this->cacheFile = $cacheFile;
        $this->siberianVersion = $siberianVersion;
        $this->keys = $keys;
        $this->clock = $clock ?: 'time';
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    /**
     * Kill switch, on by default. Set `module_updates_enabled` to 0 in the settings to stop all calls.
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->store->get('module_updates_enabled') !== '0';
    }

    // ---- The module list --------------------------------------------------------------------

    /**
     * Returns the module list, fetching it when the cache is missing, older than a day or $force.
     * When the downloader cannot be reached the cache is served for up to 7 days.
     *
     * @param bool $force
     * @return array ['manifest' => array, 'fetched_at' => int, 'stale' => bool]
     * @throws Exception
     */
    public function refresh($force = false)
    {
        $this->assertEnabled();
        $now = $this->now();
        $cache = $this->readCache();

        if ($cache !== null && !$force && ($now - $cache['fetched_at']) < self::FRESH_MAX_AGE) {
            return $cache + ['stale' => false];
        }

        $headers = ['Accept: application/json'];
        if ($cache !== null && !empty($cache['etag'])) {
            $headers[] = 'If-None-Match: ' . $cache['etag'];
        }
        $response = $this->transport->request('GET', $this->baseUrl . '/api/v1/modules.json', $headers, null, 5);

        if ($response['status'] === 304 && $cache !== null) {
            $cache['fetched_at'] = $now;
            $this->writeCache($cache);

            return $cache + ['stale' => false];
        }

        if ($response['status'] === 200) {
            $manifest = json_decode($response['body'], true);
            if ($this->isValidManifest($manifest)) {
                $fresh = [
                    'manifest' => $manifest,
                    'fetched_at' => $now,
                    'etag' => $response['headers']['etag'] ?? null,
                ];
                $this->writeCache($fresh);

                return $fresh + ['stale' => false];
            }
        }

        // Downloader down or unusable answer: fall back on the cache while it is recent enough.
        if ($cache !== null && ($now - $cache['fetched_at']) <= self::STALE_MAX_AGE) {
            return $cache + ['stale' => true];
        }

        throw new Exception('unavailable', 'The module update service is not reachable, please try again later.');
    }

    /**
     * Installed modules that have a newer version in the list.
     *
     * @param array $installed Each: ['code' => string, 'name' => string, 'version' => string, 'has_license' => bool]
     * @param array $list Result of refresh()
     * @return array Each: code, name, installed, available, release_notes_url, compatible, min_siberian, license_missing
     */
    public function pending(array $installed, array $list)
    {
        $modules = $list['manifest']['modules'];
        $updates = [];
        // Modules installed from packages that predate the `code` field have none: match them to
        // the list by name, only when exactly one entry in the list carries that name.
        $byName = [];
        foreach ($modules as $listed => $entry) {
            $key = strtolower(trim((string) ($entry['name'] ?? '')));
            $byName[$key] = isset($byName[$key]) ? false : $listed;
        }
        foreach ($installed as $module) {
            $code = $module['code'] ?? null;
            if (empty($code)) {
                $match = $byName[strtolower(trim((string) ($module['name'] ?? '')))] ?? false;
                $code = $match !== false ? (string) $match : null;
            }
            if (empty($code) || !isset($modules[$code])) {
                continue;
            }
            $remote = $modules[$code];
            if (version_compare((string) $remote['version'], (string) $module['version'], '<=')) {
                continue;
            }
            $min = $remote['min_siberian'] ?? null;
            $updates[] = [
                'code' => $code,
                'name' => $remote['name'] ?? $module['name'],
                'installed' => $module['version'],
                'available' => $remote['version'],
                'release_notes_url' => $remote['release_notes_url'] ?? null,
                'min_siberian' => $min,
                'compatible' => $min === null || version_compare($this->siberianVersion, $min, '>='),
                // No key saved for this module yet (installed before keys were kept): the administrator is asked for it.
                'license_missing' => empty($module['has_license']),
            ];
        }
        usort($updates, function ($a, $b) {
            return strcasecmp($a['name'], $b['name']);
        });

        return $updates;
    }

    // ---- Terms ------------------------------------------------------------------------------

    /**
     * Live terms from the downloader (never cached: what is shown is what gets accepted).
     *
     * @return array version, hash, effective, html
     * @throws Exception
     */
    public function fetchTerms()
    {
        $this->assertEnabled();
        $response = $this->transport->request('GET', $this->baseUrl . '/api/v1/terms', ['Accept: application/json'], null, 5);
        $terms = $response['status'] === 200 ? json_decode($response['body'], true) : null;
        if (!is_array($terms) || empty($terms['hash']) || !isset($terms['html'])) {
            throw new Exception('unavailable', 'The terms of download are not reachable, please try again later.');
        }

        return $terms;
    }

    /**
     * Whether the administrator accepted the terms currently live on the downloader.
     *
     * @param array $list Result of refresh()
     * @return bool
     */
    public function termsAccepted(array $list)
    {
        $accepted = $this->store->get('module_updates_terms_hash');

        return $accepted !== null && hash_equals((string) $list['manifest']['terms']['hash'], $accepted);
    }

    /**
     * Records the acceptance. The hash must be the one the downloader serves right now, so a page
     * left open across a terms change cannot accept stale terms.
     *
     * @param string $hash Hash of the terms the administrator saw
     * @param int|string $adminId
     * @throws Exception
     */
    public function acceptTerms($hash, $adminId)
    {
        $terms = $this->fetchTerms();
        if (!hash_equals((string) $terms['hash'], (string) $hash)) {
            throw new Exception('terms_changed', 'The terms of download changed, please read them again.', ['terms' => $terms]);
        }
        $this->store->set('module_updates_terms_hash', $terms['hash']);
        $this->store->set('module_updates_terms_version', (string) ($terms['version'] ?? ''));
        $this->store->set('module_updates_terms_accepted_at', gmdate('c', $this->now()));
        $this->store->set('module_updates_terms_accepted_by', (string) $adminId);
    }

    // ---- Download ---------------------------------------------------------------------------

    /**
     * Downloads, verifies and stores one module package.
     *
     * @param string $code Module code (download key)
     * @param string $siberianKey Licence key of this Siberian
     * @param string $moduleKey Licence key of the module
     * @param string $installedVersion
     * @param string $host
     * @param string $destinationDir Existing, writable directory
     * @param string $termsHash Accepted terms hash to send
     * @return array ['path' => string, 'filename' => string, 'version' => string]
     * @throws Exception
     */
    public function download($code, $siberianKey, $moduleKey, $installedVersion, $host, $destinationDir, $termsHash)
    {
        $this->assertEnabled();
        if (!preg_match('/^[a-z0-9_-]+$/', (string) $code)) {
            throw new Exception('bad_request', 'Invalid module.');
        }
        if (empty($this->keys)) {
            throw new Exception('no_signing_key', 'Module updates are not available yet on this version.');
        }

        $payload = json_encode([
            'sk' => $siberianKey,
            'lk' => $moduleKey,
            'host' => $host,
            'terms_hash' => $termsHash,
            'installed_version' => $installedVersion,
            'ts' => $this->now(),
            'nonce' => bin2hex(random_bytes(16)),
        ]);
        $response = $this->transport->request(
            'POST',
            $this->baseUrl . '/api/v1/modules/' . $code . '/download',
            ['Content-Type: application/json', 'Accept: application/octet-stream', 'X-Siberian-Version: ' . $this->siberianVersion],
            $payload,
            120,
            157286400
        );

        $this->throwForStatus($response);

        $body = $response['body'];
        $headers = $response['headers'];
        $sha = hash('sha256', $body);
        $declared = $headers['x-package-sha256'] ?? '';
        $signature = $headers['x-package-signature'] ?? '';
        $keyId = $headers['x-package-key-id'] ?? '';
        $version = $headers['x-package-version'] ?? '';

        if ($body === '' || !hash_equals($sha, $declared)) {
            throw new Exception('bad_package', 'The downloaded package is corrupted, please try again.');
        }
        if (!isset($this->keys[$keyId]) || !$this->verify($signature, $this->keys[$keyId], $sha, $code, $version)) {
            throw new Exception('bad_signature', 'The package signature could not be verified, the update was cancelled.');
        }
        if ($version === '' || version_compare($version, (string) $installedVersion, '<=')) {
            throw new Exception('not_newer', 'The package is not newer than the installed module.');
        }

        $filename = $this->filenameFrom($headers['content-disposition'] ?? '', $code, $version);
        $path = rtrim($destinationDir, '/\\') . '/' . $filename;
        if (file_put_contents($path, $body) === false) {
            throw new Exception('write_failed', 'Unable to store the package.');
        }

        return ['path' => $path, 'filename' => $filename, 'version' => $version];
    }

    /**
     * @param string $signatureB64
     * @param string $publicKeyB64
     * @param string $sha256
     * @param string $code
     * @param string $version
     * @return bool
     */
    public function verify($signatureB64, $publicKeyB64, $sha256, $code, $version)
    {
        $signature = base64_decode((string) $signatureB64, true);
        $key = base64_decode((string) $publicKeyB64, true);
        if ($signature === false || $key === false
            || strlen($signature) !== SODIUM_CRYPTO_SIGN_BYTES
            || strlen($key) !== SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES) {
            return false;
        }

        return sodium_crypto_sign_verify_detached($signature, $sha256 . "\n" . $code . "\n" . $version, $key);
    }

    // ---- Internals --------------------------------------------------------------------------

    /**
     * @param array $response
     * @throws Exception
     */
    private function throwForStatus(array $response)
    {
        $status = $response['status'];
        if ($status === 200) {
            return;
        }
        $data = json_decode($response['body'], true);
        $message = is_array($data) && !empty($data['message']) ? (string) $data['message'] : null;

        switch ($status) {
            case 401:
                throw new Exception('siberian_license', $message ?: 'Your Siberian licence was refused.');
            case 403:
                throw new Exception('module_license', $message ?: 'The module licence was refused.');
            case 404:
                throw new Exception('not_found', 'This module is not available.');
            case 409:
                // The terms changed since they were accepted: forget the acceptance.
                $this->store->set('module_updates_terms_hash', '');
                throw new Exception('terms_changed', $message ?: 'The terms of download changed, please accept them again.', [
                    'terms' => is_array($data) ? ($data['terms'] ?? null) : null,
                ]);
            case 426:
                throw new Exception('siberian_too_old', $message ?: 'This module needs a newer Siberian.');
            case 429:
                throw new Exception('rate_limited', $message ?: 'Too many downloads, please try again later.');
            case 0:
                throw new Exception('unavailable', 'The module update service is not reachable, please try again later.');
            default:
                throw new Exception('unavailable', $message ?: 'The module update service is busy, please try again later.');
        }
    }

    /**
     * @param mixed $manifest
     * @return bool
     */
    private function isValidManifest($manifest)
    {
        return is_array($manifest)
            && ($manifest['schema'] ?? null) === self::MANIFEST_SCHEMA
            && isset($manifest['modules']) && is_array($manifest['modules'])
            && isset($manifest['terms']['hash']) && is_string($manifest['terms']['hash']);
    }

    /**
     * @return array|null
     */
    private function readCache()
    {
        if (!is_readable($this->cacheFile)) {
            return null;
        }
        $cache = json_decode((string) file_get_contents($this->cacheFile), true);

        return (is_array($cache) && isset($cache['fetched_at']) && $this->isValidManifest($cache['manifest'] ?? null))
            ? $cache
            : null;
    }

    /**
     * @param array $cache
     */
    private function writeCache(array $cache)
    {
        $dir = dirname($this->cacheFile);
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        $tmp = $this->cacheFile . '.' . getmypid() . '.tmp';
        if (@file_put_contents($tmp, json_encode($cache)) !== false) {
            @rename($tmp, $this->cacheFile);
        }
    }

    /**
     * @throws Exception
     */
    private function assertEnabled()
    {
        if (!$this->isEnabled()) {
            throw new Exception('disabled', 'Module updates are disabled.');
        }
    }

    /**
     * @return int
     */
    private function now()
    {
        return (int) call_user_func($this->clock);
    }

    /**
     * Never trusts the server for the file name: keeps a safe basename, falls back on code-version.sib.
     *
     * @param string $disposition
     * @param string $code
     * @param string $version
     * @return string
     */
    private function filenameFrom($disposition, $code, $version)
    {
        if (preg_match('/filename="?([^";]+)"?/', $disposition, $m)) {
            $name = preg_replace('/^[0-9a-f]{16}-/', '', basename($m[1]));
            if (preg_match('/^[A-Za-z0-9._-]+\.sib$/', $name)) {
                return $name;
            }
        }

        return preg_replace('/[^a-z0-9_-]/', '', $code) . '-' . preg_replace('/[^0-9A-Za-z.-]/', '', $version) . '.sib';
    }
}
