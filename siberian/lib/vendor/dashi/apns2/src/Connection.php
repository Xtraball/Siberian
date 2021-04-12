<?php
namespace Dashi\Apns2;

/**
 * An http2 curl connection
 * @package Apns2
 */
class Connection extends BaseDataObject
{
    public $sandbox = true;
    public $certPath;
    private $ch;


    /**
     * Connection constructor.
     * @param array $data example: ['sandbox' => true, 'cert-path' => '/var/www/config/http2.pem']
     */
    public function __construct($data = [])
    {
        $this->loadFromJSON($data);
    }

    /**
     * @param string $token
     * @param Message $message
     * @param Options $options
     * @return Response
     * @throws \Exception
     */
    protected function sendOne($token, $message, $options)
    {
        if (!$message->aps || is_scalar($message->aps)) {
            throw new \Exception("invalid message: " . json_encode($message));
        }

        if (!defined('CURL_HTTP_VERSION_2_0')) {
            define('CURL_HTTP_VERSION_2_0', 3);
        }
        if (!$this->ch) {
            $this->ch = curl_init();
        }
        curl_setopt($this->ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2_0);

        $beginTime = microtime(true);

        $host = $this->sandbox ? 'https://api.development.push.apple.com' : 'https://api.push.apple.com';

        if (!$this->certPath) {
            throw new \Exception("certPath is required");
        }

        $cert = realpath($this->certPath);
        if (!$cert) {
            throw new \Exception("cert path invalid: {$this->certPath}");
        }
        curl_setopt_array($this->ch, [
            CURLOPT_URL => "$host/3/device/$token",
            CURLOPT_PORT => 443,
            CURLOPT_HTTPHEADER => $options->getHeadersForHttp2API(),
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($message),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $options->curlTimeout,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSLCERT => $cert,
            CURLOPT_HEADER => 1
        ]);

        $result = curl_exec($this->ch);
        if ($result === false) {
            throw new \Exception('Curl failed with error: ' . curl_error($this->ch));
        }
        $response = new Response($result, curl_getinfo($this->ch, CURLINFO_HTTP_CODE), microtime(true) - $beginTime,
            $token);
        return $response;
    }

    /**
     * send notification to multiple tokens
     * @param array $tokens
     * @param array|Message $message example: ['aps' => ['alert' => 'hello', 'sound' => 'default', 'content-available' => 1]]
     * @param array|Options $options example: ['apns-topic' => 'your.company', 'user-agent' => 'YourAgent/1.0', 'curl-timeout' => 10]
     * @return Response[] an array of response
     * @throws \Exception
     */
    public function send($tokens, $message, $options)
    {
        $message = $message instanceof Message ? $message : new Message($message);
        $options = $options instanceof Options ? $options : new Options($options);

        $result = [];
        foreach ($tokens as $token) {
            $result [] = $this->sendOne($token, $message, $options);
        }
        return $result;
    }

    /**
     * close the connection
     */
    public function close()
    {
        if ($this->ch !== null) {
            curl_close($this->ch);
            $this->ch = null;
        }
    }
}
