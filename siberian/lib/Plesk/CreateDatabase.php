<?php
namespace Plesk;

class CreateDatabase extends BaseRequest
{
    /**
     * @var string
     */
    public $xml_packet = <<<EOT
<?xml version="1.0"?>
<packet version="1.6.3.0">
    <database>
        <add-db>
            <webspace-id>{SUBSCRIPTION_ID}</webspace-id>
            <name>{NAME}</name>
            <type>{TYPE}</type>
            <db-server-id>{SERVER_ID}</db-server-id>
        </add-db>
    </database>
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
        'subscription_id' => null,
        'server_id' => null,
        'name' => null,
        'type' => 'mysql'
    );

    /**
     * @param $xml
     * @return bool
     * @throws ApiRequestException
     */
    protected function processResponse($xml)
    {
        if ($xml->database->{'add-db'}->result->status == 'error') {
            throw new ApiRequestException($xml->database->{'add-db'}->result);
        }

        $this->id = (int)$xml->database->{'add-db'}->result->id;
        return true;
    }
}