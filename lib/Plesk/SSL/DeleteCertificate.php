<?php
namespace Plesk\SSL;

use Plesk\ApiRequestException;
use Plesk\BaseRequest;
use Plesk\HttpRequestContract;
use Plesk\Node;
use SimpleXMLElement;

class DeleteCertificate extends BaseRequest
{
    /**
     * @var string
     */
    public $xml_packet = <<<EOT
<?xml version="1.0"?>
<packet version="1.6.3.0">
    <certificate>
        <remove>
            <filter>
                <name>{CERT-NAME}</name>
            </filter>
            <webspace>{WEBSPACE}</webspace>
        </remove>
     </certificate>
</packet>
EOT;

    /**
     * @var array
     */
    protected $default_params = array(
        'webspace' => "",
        'cert-name' => null,
    );

    /**
     * @param array $config
     * @param array $params
     * @param HttpRequestContract $http
     * @throws ApiRequestException
     */
    public function __construct(array $config, array $params = array(), HttpRequestContract $http = null)
    {
        if (!isset($params['webspace'])) {
            throw new ApiRequestException('webspace parameter is required');
        }

        if (!isset($params['cert-name'])) {
            throw new ApiRequestException('cert-name parameter is required');
        }

        parent::__construct($config, $params, $http);
    }

    /**
     * @param SimpleXMLElement $xml
     * @return bool
     * @throws ApiRequestException
     */
    protected function processResponse($xml)
    {
        if ((string) $xml->{'certificate'}->{'remove'}->result->status === 'error') {
            throw new ApiRequestException($xml->{'certificate'}->{'remove'}->result);
        }

        return true;
    }
}
