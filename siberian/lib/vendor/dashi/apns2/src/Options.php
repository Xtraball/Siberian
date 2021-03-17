<?php
namespace Dashi\Apns2;

class Options extends BaseDataObject
{
    /**
     * @var string|null
     */
    public $apnsId;
    /**
     * @var int|null
     */
    public $apnsExpiration;
    /**
     * @var int|null
     */
    public $apnsPriority;
    /**
     * @var string
     */
    public $apnsTopic;

    /**
     * @var string|null
     */
    public $apnsCollapseId;

    /**
     * @var int
     */
    public $curlTimeout = 30;

    /**
     * @var string
     */
    public $userAgent = 'apns2(php)';

    public function __construct($data = [])
    {
        $this->loadFromJSON($data);
    }

    public function getHeadersForHttp2API()
    {
        $headers = array_filter($this->jsonSerialize(), function ($k) {
            return preg_match('/(^user-agent$|^apns-)/i', $k);
        }, ARRAY_FILTER_USE_KEY);
        $result = [];
        foreach ($headers as $k => $v) {
            if (is_scalar($v)) {
                $result[] = "$k: $v";
            }
        }
        return $result;
    }


}