<?php
namespace Plesk\Wordpress;

use Plesk\ApiRequestException;
use Plesk\BaseRequest;
use SimpleXMLElement;

class ActivateTheme extends BaseRequest
{
    public $xml_packet = <<<EOT
<?xml version="1.0"?>
<packet>
    <wp-instance>
         <activate-theme>
             <filter>
                  <id>{ID}</id>
             </filter>
             <asset-id>{THEME_ID}</asset-id>
         </activate-theme>
     </wp-instance>
</packet>
EOT;

    /**
     * @var array
     */
    protected $default_params = array(
        'id' => null,
        'theme_id' => null,
    );

    /**
     * @param SimpleXMLElement $xml
     * @return bool
     * @throws ApiRequestException
     */
    protected function processResponse($xml)
    {
        if ((string) $xml->{'wp-instance'}->{'activate-theme'}->result->status === 'error') {
            throw new ApiRequestException($xml->{'wp-instance'}->{'activate-theme'}->result);
        }

        return true;
    }
}
