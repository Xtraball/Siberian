<?php
namespace Plesk;

/**
 * Class UpdateIPAddress
 * @package Plesk
 */
class UpdateIPAddress extends BaseRequest
{
    /**
     * @var string
     */
    public $xml_packet = <<<EOT
<?xml version="1.0"?>
<packet>
<ip>
    <set>
        <filter>
            <ip_address>{IP_ADDRESS}</ip_address>
        </filter>
        <certificate_name>{CERTIFICATE_NAME}</certificate_name>
    </set>
</ip>
</packet>
EOT;

    /**
     * @var int
     */
    public $id;

    /**
     * @var array
     */
    protected $default_params = array(
        'ip_address' => null,
        'certificate_name' => '',
    );

    /**
     * @param $xml
     * @return bool
     * @throws ApiRequestException
     */
    protected function processResponse($xml)
    {
        if ($xml->ip->set->result->status == 'error') {
            throw new ApiRequestException($xml->ip->set->result);
        }

        return true;
    }

}
