<?php
namespace Plesk\Wordpress;

use Plesk\ApiRequestException;
use Plesk\BaseRequest;
use SimpleXMLElement;

class DisableAutoUpdates extends BaseRequest
{
    public $xml_packet = <<<EOT
<?xml version="1.0"?>
<packet>
    <wp-instance>
         <disable-auto-updates>
             <filter>
                  <id>{ID}</id>
             </filter>
         </disable-auto-updates>
     </wp-instance>
</packet>
EOT;

    /**
     * @var array
     */
    protected $default_params = array(
        'id' => null,
    );

    /**
     * @param SimpleXMLElement $xml
     * @return bool
     * @throws ApiRequestException
     */
    protected function processResponse($xml)
    {
        if ((string) $xml->{'wp-instance'}->{'disable-auto-updates'}->result->status === 'error') {
            throw new ApiRequestException($xml->{'wp-instance'}->{'disable-auto-updates'}->result);
        }

        return true;
    }
}
