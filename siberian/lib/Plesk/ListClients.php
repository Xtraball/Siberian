<?php
namespace Plesk;

class ListClients extends BaseRequest
{
    /**
     * @var string
     */
    public $xml_packet = <<<EOT
<?xml version="1.0"?>
<packet version="1.6.0.0">
<client>
    <get>
        <filter/>
        <dataset>
			<gen_info/>
			<stat/>
		</dataset>
    </get>
</client>
</packet>
EOT;

    /**
     * @param $xml
     * @return array
     */
    protected function processResponse($xml)
    {
        $result = array();

        for ($i = 0; $i < count($xml->client->get->result); $i++) {
            $client = $xml->client->get->result[$i];

            $result[] = array(
                'id' => (string)$client->id,
                'status' => (string)$client->status,
                'created' => (string)$client->data->gen_info->cr_date,
                'name' => (string)$client->data->gen_info->cname,
                'contact_name' => (string)$client->data->gen_info->pname,
                'username' => (string)$client->data->gen_info->login,
                'phone' => (string)$client->data->gen_info->phone,
                'email' => (string)$client->data->gen_info->email,
                'address' => (string)$client->data->gen_info->address,
                'city' => (string)$client->data->gen_info->city,
                'state' => (string)$client->data->gen_info->state,
                'post_code' => (string)$client->data->gen_info->pcode,
                'country' => (string)$client->data->gen_info->country,
                'locale' => (string)$client->data->gen_info->locale,
                'stat' => array(
                    'domains' => (int)$client->data->stat->active_domains,
                    'subdomains' => (int)$client->data->stat->subdomains,
                    'disk_space' => (int)$client->data->stat->disk_space,
                    'web_users' => (int)$client->data->stat->web_users,
                    'databases' => (int)$client->data->stat->data_bases,
                    'traffic' => (int)$client->data->stat->traffic,
                    'traffic_prevday' => (int)$client->data->stat->traffic_prevday,
                ),
            );
        }

        return $result;
    }
}
