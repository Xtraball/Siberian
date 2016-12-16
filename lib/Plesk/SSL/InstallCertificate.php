<?php
namespace Plesk\SSL;

use Plesk\ApiRequestException;
use Plesk\BaseRequest;
use Plesk\HttpRequestContract;
use Plesk\Node;
use SimpleXMLElement;

class InstallCertificate extends BaseRequest
{
    /**
     * @var string
     */
    public $xml_packet = <<<EOT
<?xml version="1.0"?>
<packet version="1.6.3.0">
    <certificate>
        <install>
            <name>{NAME}</name>
            {DESTINATION}
            <content>
                <csr>{CSR}</csr>
                <pvt>{PVT}</pvt>
                <cert>{CERT}</cert>
                <ca>{CA}</ca>
            </content>
            <ip_address>{IP-ADDRESS}</ip_address>
        </install>
     </certificate>
</packet>
EOT;

    /**
     * @var array
     */
    protected $default_params = array(
        'name' => null,
        'csr' => null,
        'pvt' => null,
        'ip-address' => null,
    );

    /**
     * @param array $config
     * @param array $params
     * @param HttpRequestContract $http
     * @throws ApiRequestException
     */
    public function __construct(array $config, array $params = array(), HttpRequestContract $http = null)
    {
        if (isset($params['admin']) && $params['admin'] === true) {
            $params['destination'] = new Node('admin');
        }

        if (isset($params['webspace'])) {
            $params['destination'] = new Node('webspace', $params['webspace']);
        }

        if (!isset($params['destination'])) {
            throw new ApiRequestException('admin or webspace parameter is required');
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
        if ((string) $xml->{'certificate'}->{'install'}->result->status === 'error') {
            throw new ApiRequestException($xml->{'certificate'}->{'install'}->result);
        }

        return true;
    }
}
