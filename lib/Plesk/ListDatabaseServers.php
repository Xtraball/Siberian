<?php
namespace Plesk;

class ListDatabaseServers extends BaseRequest
{
    /**
     * @var string
     */
    public $xml_packet = <<<EOT
<?xml version="1.0"?>
<packet>
<db_server>
	<get-local>
		<filter />
	</get-local>
</db_server>
</packet>
EOT;

    /**
     * @param $xml
     * @return array
     */
    protected function processResponse($xml)
    {
        $result = array();

        foreach ($xml->db_server->{'get-local'}->children() as $node) {
            $result[] = array(
                'status' => (string)$node->status,
                'id' => (int)$node->id,
                'type' => (string)$node->type,
            );
        }

        return $result;
    }
}
