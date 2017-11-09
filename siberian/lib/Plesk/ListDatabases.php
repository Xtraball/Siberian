<?php
namespace Plesk;

class ListDatabases extends BaseRequest
{
    /**
     * @var string
     */
    public $xml_packet = <<<EOT
<?xml version="1.0"?>
<packet>
<database>
	<get-db>
		<filter>
			<webspace-id>{SUBSCRIPTION_ID}</webspace-id>
		</filter>
	</get-db>
</database>
</packet>
EOT;

    /**
     * @var array
     */
    protected $default_params = array(
        'subscription_id' => null,
    );

    /**
     * @param $xml
     * @return array
     */
    protected function processResponse($xml)
    {
        $result = array();

        foreach ($xml->database->{'get-db'}->children() as $node) {
            $result[] = array(
                'status' => (string)$node->status,
                'id' => (int)$node->id,
                'name' => (string)$node->name,
                'subscription_id' => (int)$node->{'webspace-id'},
                'db_server_id' => (int)$node->{'db-server-id'},
                'default_user_id' => (int)$node->{'default-user-id'},
            );
        }

        return $result;
    }
}
