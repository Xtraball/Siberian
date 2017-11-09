<?php
namespace Plesk\Wordpress;

use Plesk\ApiRequestException;
use Plesk\BaseRequest;
use SimpleXMLElement;

class DeactivatePlugin extends BaseRequest
{
    public $xml_packet = <<<EOT
<?xml version="1.0"?>
<packet>
    <wp-instance>
         <deactivate-plugin>
             <filter>
                  <id>{ID}</id>
             </filter>
             <asset-id>{PLUGIN_ID}</asset-id>
         </deactivate-plugin>
     </wp-instance>
</packet>
EOT;

    /**
     * @var array
     */
    protected $default_params = array(
        'id' => null,
        'plugin_id' => null,
    );

    /**
     * @param SimpleXMLElement $xml
     * @return bool
     * @throws ApiRequestException
     */
    protected function processResponse($xml)
    {
        if ((string) $xml->{'wp-instance'}->{'deactivate-plugin'}->result->status === 'error') {
            throw new ApiRequestException($xml->{'wp-instance'}->{'deactivate-plugin'}->result);
        }

        return true;
    }
}
