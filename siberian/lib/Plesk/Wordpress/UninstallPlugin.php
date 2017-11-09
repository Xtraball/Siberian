<?php
namespace Plesk\Wordpress;

use Plesk\ApiRequestException;
use Plesk\BaseRequest;
use SimpleXMLElement;

class UninstallPlugin extends BaseRequest
{
    public $xml_packet = <<<EOT
<?xml version="1.0"?>
<packet>
    <wp-instance>
         <uninstall-plugin>
             <filter>
                  <id>{ID}</id>
             </filter>
             <asset-id>{PLUGIN_ID}</asset-id>
         </uninstall-plugin>
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
        if ((string) $xml->{'wp-instance'}->{'uninstall-plugin'}->result->status === 'error') {
            throw new ApiRequestException($xml->{'wp-instance'}->{'uninstall-plugin'}->result);
        }

        return true;
    }
}
