<?php
namespace Plesk\Wordpress;

use Plesk\ApiRequestException;
use Plesk\BaseRequest;
use SimpleXMLElement;

class UpdateCore extends BaseRequest
{
    public $xml_packet = <<<EOT
<?xml version="1.0"?>
<packet>
    <wp-instance>
         <update-core>
             <filter>
                  <id>{ID}</id>
             </filter>
         </update-core>
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
        if ((string) $xml->{'wp-instance'}->{'update-core'}->result->status === 'error') {
            throw new ApiRequestException($xml->{'wp-instance'}->{'update-core'}->result);
        }

        return true;
    }
}
