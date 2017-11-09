<?php
namespace Plesk\Wordpress;

use Plesk\BaseRequest;
use SimpleXMLElement;

class GetSecurityStatus extends BaseRequest
{
    public $xml_packet = <<<EOT
<?xml version="1.0"?>
<packet>
    <wp-instance>
         <get-security-status>
            <filter>
                  <id>{ID}</id>
             </filter>
         </get-security-status>
     </wp-instance>
</packet>
EOT;

    /**
     * @param SimpleXMLElement $xml
     * @return array
     */
    protected function processResponse($xml)
    {
        $response = array();

        foreach ($xml->{'wp-instance'}->{'get-security-status'}->result as $result) {
            $response[] = array(
                'status' => (string) $result->status,
                'security-status' => (string) $result->{'security-status'},
            );
        }

        return $response;
    }
}
