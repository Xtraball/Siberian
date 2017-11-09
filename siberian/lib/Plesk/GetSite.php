<?php
namespace Plesk;

class GetSite extends BaseRequest
{
    /**
     * @var string
     */
    public $xml_packet = <<<EOT
<?xml version="1.0"?>
<packet version="1.6.0.0">
<domain>
	<get>
		<filter>
			<domain-name>{DOMAIN}</domain-name>
		</filter>
		<dataset>
			<hosting/>
		</dataset>
	</get>
</domain>
</packet>
EOT;

    /**
     * @var array
     */
    protected $default_params = array(
        'domain' => null,
    );

    /**
     * @param $xml
     * @return array
     * @throws ApiRequestException
     */
    protected function processResponse($xml)
    {
        $site = $xml->domain->get->result;

        if ((string)$site->status == 'error') {
            throw new ApiRequestException($site);
        }
        if ((string)$site->result->status == 'error') {
            throw new ApiRequestException($site->result);
        }

        $hosting_type = (string)$site->data->gen_info->htype;

        return array(
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
