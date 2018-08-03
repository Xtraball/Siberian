<?php
namespace Plesk;

use Plesk\Helper\Xml;

class ListSubscriptions extends BaseRequest
{
    /**
     * @var string
     */
    public $xml_packet = <<<EOT
<?xml version="1.0"?>
<packet version="1.6.3.0">
<webspace>
    <get>
        {FILTER}
        <dataset>
			<hosting/>
			<subscriptions/>
		</dataset>
    </get>
</webspace>
</packet>
EOT;

    /**
     * @var array
     */
    protected $default_params = array(
        'filter' => null,
    );

    /**
     * @param array $config
     * @param array $params
     * @throws ApiRequestException
     */
    public function __construct($config, $params = array())
    {
        $this->default_params['filter'] = new Node('filter');

        if (isset($params['client_id'])) {
            $ownerIdNode = new Node('owner-id', $params['client_id']);
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

        for ($i = 0; $i < count($xml->webspace->get->result); $i++) {
            $webspace = $xml->webspace->get->result[$i];

            $hosting = array();
            foreach ($webspace->data->hosting->children() as $host) {
                $hosting[$host->getName()] = Xml::getProperties($host);
            }

            $subscriptions = array();
            foreach ($webspace->data->subscriptions->children() as $subscription) {
                $subscriptions[] = array(
                    'locked' => (bool)$subscription->locked,
                    'synchronized' => (bool)$subscription->synchronized,
                    'plan-guid' => (string)$subscription->plan->{"plan-guid"},
                );
            }

            $result[] = array(
                'id' => (string)$webspace->id,
                'status' => (string)$webspace->status,
                'subscription_status' => (int)$webspace->data->gen_info->status,
                'created' => (string)$webspace->data->gen_info->cr_date,
                'name' => (string)$webspace->data->gen_info->name,
                'owner_id' => (string)$webspace->data->gen_info->{"owner-id"},
                'hosting' => $hosting,
                'real_size' => (int)$webspace->data->gen_info->real_size,
                'dns_ip_address' => (string)$webspace->data->gen_info->dns_ip_address,
                'htype' => (string)$webspace->data->gen_info->htype,
                'subscriptions' => $subscriptions,
            );
        }

        return $result;
    }
}
