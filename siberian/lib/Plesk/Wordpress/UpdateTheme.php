<?php
namespace Plesk\Wordpress;

use Plesk\ApiRequestException;
use Plesk\BaseRequest;
use SimpleXMLElement;

class UpdateTheme extends BaseRequest
{
    public $xml_packet = <<<EOT
<?xml version="1.0"?>
<packet>
    <wp-instance>
         <update-theme>
             <filter>
                  <id>{ID}</id>
             </filter>
             <asset-id>{THEME_ID}</asset-id>
         </update-theme>
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
        if ((string) $xml->{'wp-instance'}->{'update-theme'}->result->status === 'error') {
            throw new ApiRequestException($xml->{'wp-instance'}->{'update-theme'}->result);
        }

        return true;
    }
}
