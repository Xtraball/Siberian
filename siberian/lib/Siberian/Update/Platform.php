<?php

namespace Siberian\Update;

/**
 * The two requests of a platform update: ask the update server what is available, then download the package.
 *
 * The back office (Installer_Backoffice_ModuleController) and the command line (`php cli update`) both go through this
 * class, so a licence, a channel or a message is decided in one place. Everything that touches the world is handed to
 * the constructor (the HTTP call, the file write, the translation), which keeps the rules testable without a server.
 *
 *  - the channel decides the server (production, stable, beta, preview; anything else is stable);
 *  - SAE sends `sae=1`, the other editions send their licence key and the host, and refuse to ask without a key;
 *  - the answer carries `url` and `filename` when something is available, and nothing else when the system is up to date.
 */
class Platform
{
    public const DEFAULT_SERVER = 'https://updates02.siberiancms.com';

    public const SERVERS = [
        'production' => 'https://production-updates02.siberiancms.com',
        'stable' => 'https://updates02.siberiancms.com',
        'beta' => 'https://beta-updates02.siberiancms.com',
        'preview' => 'https://preview-updates02.siberiancms.com',
    ];

    /** @var string SAE, MAE or PE */
    private $edition;

    /** @var string */
    private $version;

    /** @var string|null */
    private $channel;

    /** @var string|null */
    private $licenseKey;

    /** @var string|null */
    private $host;

    /** @var callable function (string $url, array $fields, int $timeout): array [int $status, string|false $body] */
    private $post;

    /** @var callable function (string $path, string $content): void */
    private $write;

    /** @var callable function (string $text): string */
    private $translate;

    /**
     * @param string $edition SAE, MAE or PE
     * @param string $version the installed version
     * @param string|null $channel the configured update channel
     * @param string|null $licenseKey the licence key (not needed by SAE)
     * @param string|null $host the domain of the installation, sent with the licence key
     * @param array $services post, write and translate callables
     */
    public function __construct(
        string  $edition,
        string  $version,
        ?string $channel,
        ?string $licenseKey,
        ?string $host,
        array   $services)
    {
        $this->edition = $edition;
        $this->version = $version;
        $this->channel = $channel;
        $this->licenseKey = $licenseKey;
        $this->host = $host;
        $this->post = $services['post'];
        $this->write = $services['write'];
        $this->translate = $services['translate'] ?? static function (string $text): string {
            return $text;
        };
    }

    /**
     * @param string|null $channel
     * @return string the update server of the channel
     */
    public static function server(?string $channel): string
    {
        return self::SERVERS[$channel ?? ''] ?? self::DEFAULT_SERVER;
    }

    /**
     * @return array the fields every request to the update server carries
     * @throws \Exception when an edition that needs a licence has none
     */
    public function fields(): array
    {
        if ($this->edition === 'SAE') {
            return ['sae' => '1'];
        }

        if (empty($this->licenseKey)) {
            throw new \Exception($this->t('There is no CMS license key set.'));
        }

        return ['licenseKey' => $this->licenseKey, 'host' => $this->host];
    }

    /**
     * Asks the update server.
     *
     * @return array the answer: `url`, `filename` and `version` when an update is available, `message` when not
     * @throws \Exception
     */
    public function check(): array
    {
        $url = self::server($this->channel) . '/check.php?version=' . $this->version;
        [$status, $body] = ($this->post)($url, $this->fields(), 30);

        if (empty($body)) {
            throw new \Exception($this->t('An error occurred while loading. Please, try again later.'));
        }

        $content = json_decode((string) $body, true);

        if ((int) $status !== 200 || !is_array($content)) {
            $message = $this->t('Unable to check for updates now. Please, try again later.');
            if (is_array($content) && !empty($content['error']) && !empty($content['message'])) {
                $message = $this->t((string) $content['message']);
            }

            throw new \Exception($message);
        }

        if (empty($content['url'])) {
            $content['message'] = $this->t('Your system is up to date.');
        }

        return $content;
    }

    /**
     * Downloads the package an answer of check() points to.
     *
     * @param array $available the answer of check()
     * @param string $directory where the package is written
     * @return string the path of the downloaded package
     * @throws \Exception
     */
    public function download(array $available, string $directory): string
    {
        if (empty($available['url']) || empty($available['filename'])) {
            throw new \Exception($this->t('Your system is up to date.'));
        }

        // The name comes from the server: only a file name is accepted, never a path
        $filename = basename((string) $available['filename']);
        if ($filename === '' || $filename === '.' || $filename === '..') {
            throw new \Exception($this->t('#102: Unable to fetch the update. Please, try again later.'));
        }

        $path = rtrim($directory, '/') . '/' . $filename;

        [$status, $body] = ($this->post)((string) $available['url'], $this->fields(), 1800);

        if ((int) $status !== 200) {
            throw new \Exception($this->t('#101: Unable to fetch the update. Please, try again later.'));
        }

        if (empty($body)) {
            throw new \Exception($this->t('#100: Unable to fetch the update. Please, try again later.'));
        }

        ($this->write)($path, (string) $body);

        if (!file_exists($path)) {
            throw new \Exception($this->t('#102: Unable to fetch the update. Please, try again later.'));
        }

        return $path;
    }

    /**
     * @param string $text
     * @return string
     */
    private function t(string $text): string
    {
        return ($this->translate)($text);
    }
}
