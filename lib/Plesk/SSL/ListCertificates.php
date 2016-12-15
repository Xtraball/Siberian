<?php
namespace Plesk\SSL;

use Plesk\ApiRequestException;
use Plesk\BaseRequest;
use Plesk\HttpRequestContract;
use Plesk\Node;
use SimpleXMLElement;

class ListCertificates extends BaseRequest
{
    /**
     * @var string
     */
    public $xml_packet = <<<EOT
<?xml version="1.0"?>
<packet version="1.6.3.2">
    <certificate>
       <get-pool>
           <filter>
               {DOMAIN-NAME}
               {DOMAIN-ID}
           </filter>
       </get-pool>
    </certificate>
</packet>
EOT;

    /**
     * @var array
     */
    protected $default_params = array(
        'domain-id' => null,
        'domain-name' => null,
    );

    /**
     * @param array $config
     * @param array $params
     * @param HttpRequestContract $http
     * @throws ApiRequestException
     */
    public function __construct(array $config, array $params = array(), HttpRequestContract $http = null)
    {
        if (!isset($params['domain-id']) && !isset($params['domain-name'])) {
            throw new ApiRequestException('domain-id or domain-name parameter is required');
        }

        if(isset($params['domain-id'])) {
            $params['domain-name'] = "";
            $params['domain-id'] = new Node('domain-id', $params['domain-id']);
        } elseif(isset($params['domain-name'])) {
            $params['domain-id'] = "";
            $params['domain-name'] = new Node('domain-name', $params['domain-name']);
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
        if ((string) $xml->{'certificate'}->{'get-pool'}->result->status === 'error') {
            throw new ApiRequestException($xml->{'certificate'}->{'get-pool'}->result);
        }

        return true;
    }
}
