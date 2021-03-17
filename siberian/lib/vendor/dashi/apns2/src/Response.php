<?php
namespace Dashi\Apns2;

/**
 * Response for sending message to specific device via APNs
 * @see https://developer.apple.com/library/content/documentation/NetworkingInternet/Conceptual/RemoteNotificationsPG/CommunicatingwithAPNs.html#//apple_ref/doc/uid/TP40008194-CH11-SW17
 * @package Apns2
 */
class Response
{
    /** @var string */
    const REASON_BAD_COLLAPSE_ID = 'BadCollapseId';
    /** @var string */
    const REASON_BAD_DEVICE_TOKEN = 'BadDeviceToken';
    /** @var string */
    const REASON_BAD_EXPIRATION_DATE = 'BadExpirationDate';
    /** @var string */
    const REASON_BAD_MESSAGE_ID = 'BadMessageId';
    /** @var string */
    const REASON_BAD_PRIORITY = 'BadPriority';
    /** @var string */
    const REASON_BAD_TOPIC = 'BadTopic';
    /** @var string */
    const REASON_DEVICE_TOKEN_NOT_FOR_TOPIC = 'DeviceTokenNotForTopic';
    /** @var string */
    const REASON_DUPLICATE_HEADERS = 'DuplicateHeaders';
    /** @var string */
    const REASON_IDLE_TIMEOUT = 'IdleTimeout';
    /** @var string */
    const REASON_MISSING_DEVICE_TOKEN = 'MissingDeviceToken';
    /** @var string */
    const REASON_MISSING_TOPIC = 'MissingTopic';
    /** @var string */
    const REASON_PAYLOAD_EMPTY = 'PayloadEmpty';
    /** @var string */
    const REASON_TOPIC_DISALLOWED = 'TopicDisallowed';
    /** @var string */
    const REASON_BAD_CERTIFICATE = 'BadCertificate';
    /** @var string */
    const REASON_BAD_CERTIFICATE_ENVIRONMENT = 'BadCertificateEnvironment';
    /** @var string */
    const REASON_EXPIRED_PROVIDER_TOKEN = 'ExpiredProviderToken';
    /** @var string */
    const REASON_FORBIDDEN = 'Forbidden';
    /** @var string */
    const REASON_INVALID_PROVIDER_TOKEN = 'InvalidProviderToken';
    /** @var string */
    const REASON_MISSING_PROVIDER_TOKEN = 'MissingProviderToken';
    /** @var string */
    const REASON_BAD_PATH = 'BadPath';
    /** @var string */
    const REASON_METHOD_NOT_ALLOWED = 'MethodNotAllowed';
    /** @var string */
    const REASON_UNREGISTERED = 'Unregistered';
    /** @var string */
    const REASON_PAYLOAD_TOO_LARGE = 'PayloadTooLarge';
    /** @var string */
    const REASON_TOO_MANY_PROVIDER_TOKEN_UPDATES = 'TooManyProviderTokenUpdates';
    /** @var string */
    const REASON_TOO_MANY_REQUESTS = 'TooManyRequests';
    /** @var string */
    const REASON_INTERNAL_SERVER_ERROR = 'InternalServerError';
    /** @var string */
    const REASON_SERVICE_UNAVAILABLE = 'ServiceUnavailable';
    /** @var string */
    const REASON_SHUTDOWN = 'Shutdown';


    /**
     * request device id for this response
     * @var string
     */
    public $deviceId;
    /**
     * The apns-id value from the request. If no value was included in the request, the server creates a new UUID and returns it in this header.
     * @var string|null
     */
    public $apnsId;

    /**
     * response status code
     * @var int
     */
    public $status;

    /**
     * response json body
     * @var \stdClass|null
     */
    public $body;
    /**
     * response headers
     * @var array
     */
    public $headers;

    /**
     * reason for error response, see REASON_* constants of this class
     * @var string
     */
    public $reason;
    /**
     * time used from request been sent
     * @var float
     */
    public $duration;
    /**
     * If :status is 410, the value of this key is the last time at which APNs confirmed that the device token was no longer valid for the topic.
     * @var null
     */
    public $timestamp;


    public function __construct($responseHeaderAndBody, $code, $duration, $deviceId)
    {
        $this->duration = $duration;
        $this->status = $code;
        $this->deviceId = $deviceId;

        if (preg_match('/^\S+ (\d+)[^\n]*\n(.*?)\r*\n\r*\n(.*)$/s', $responseHeaderAndBody, $m)) {
            $this->status = intval($m[1]);

            $this->headers = [];
            foreach (explode("\n", trim($m[2])) as $line) {
                $line = trim($line);
                if (!$line) {
                    continue;
                }
                $kv = explode(":", $line);
                if (count($kv) <= 1) {
                    continue;
                }
                $this->headers[trim($kv[0])] = trim($kv[1]);
            }

            if (isset($this->headers['apns-id'])) {
                $this->apnsId = $this->headers['apns-id'];
            }


            $body = trim($m[3]);
            if ($body) {
                $this->body = json_decode($body);
                $this->reason = isset($this->body) ? $this->body->reason : null;
                $this->timestamp = isset($this->timestamp) ? $this->body->timestamp : null;
            }
        }
    }
}