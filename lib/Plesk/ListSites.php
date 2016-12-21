<?php
namespace Plesk;

class ListSites extends BaseRequest
{
    /**
     * @var string
     */
    public $xml_packet = <<<EOT
<?xml version="1.0"?>
<packet version="1.6.3.0">
<site>
	<get>
		{FILTER}
		<dataset>
			<hosting/>
		</dataset>
	</get>
</site>
</packet>
EOT;

    /**
     * @var array
     */
    protected $default_params = array(
        'filter' => '<filter/>',
    );

    /**
     * @param array $config
     * @param array $params
     * @throws ApiRequestException
     */
    public function __construct(array $config, $params = array())
    {
        $this->default_params['filter'] = new Node('filter');

        if (isset($params['subscription_id'])) {
            $ownerIdNode = new Node('parent-id', $params['subscription_id']);
            $params['filter'] = new Node('filter', $ownerIdNode);
        }

        if (isset($params['name'])) {
            $ownerIdNode = new Node('name', $params['name']);
            $params['filter'] = new Node('filter', $ownerIdNode);
        }

        parent::__construct($config, $params);
    }

    /**
     * @param $xml
     * @return array
     */
    protected function processResponse($xml)
    {
        $result = array();
        $itemCount = count($xml->site->get->result);


        for ($i = 0; $i < $itemCount; $i++) {
            $site = $xml->site->get->result[$i];
            $hosting_type = (string)$site->data->gen_info->htype;

            $result[] = array(
                'id' => (string)$site->id,
                'status' => (string)$site->status,
                'created' => (string)$site->data->gen_info->cr_date,
                'name' => (string)$site->data->gen_info->name,
                'ip' => (string)$site->data->gen_info->dns_ip_address,
                'hosting_type' => $hosting_type,
                'ip_address' => (string)$site->data->hosting->{$hosting_type}->ip_address,
                'www_root' => $this->findHostingProperty($site->data->hosting->{$hosting_type}, 'www_root'),
                'ftp_username' => $this->findHostingProperty($site->data->hosting->{$hosting_type}, 'ftp_login'),
                'ftp_password' => $this->findHostingProperty($site->data->hosting->{$hosting_type}, 'ftp_password'),
            );
        }

        return $result;
    }

    /**
     * @param $node
     * @param $key
     * @return null|string
     */
    protected function findHostingProperty($node, $key)
    {
        foreach ($node->children() as $property) {
            if ($property->name == $key) {
                return (string)$property->value;
            }
        }

        return null;
    }
}
