<?php
namespace Plesk;

class GetServerInfo extends BaseRequest
{
    /**
     * @var string
     */
    public $xml_packet = <<<EOT
<?xml version="1.0"?>
<packet version="1.4.2.0">
<server>
	<get>
		<gen_info/>
		<stat/>
		<prefs/>
	</get>
</server>
</packet>
EOT;

    /**
     * @param $xml
     * @return array
     * @throws ApiRequestException
     */
    protected function processResponse($xml)
    {
        $server = $xml->server->get->result;

        if ((string)$server->status === 'error') {
            throw new ApiRequestException($server);
        }

        if ((string)$server->result->status === 'error') {
            throw new ApiRequestException($server->result);
        }

        $diskspace = array();
        foreach ($server->stat->diskspace->children() as $device) {
            $diskspace[] = array(
                'name' => (string)$device->name,
                'total' => (int)$device->total,
                'used' => (int)$device->used,
                'free' => (int)$device->free,
            );
        }

        return array(
            'status' => (string)$server->status,
            'server_name' => (string)$server->gen_info->server_name,
            'stats' => array(
                'clients' => (int)$server->stat->objects->clients,
                'domains' => (int)$server->stat->objects->domains,
                'active_domains' => (int)$server->stat->objects->active_domains,
                'mail_boxes' => (int)$server->stat->objects->mail_boxes,
                'databases' => (int)$server->stat->objects->databases,
                'database_users' => (int)$server->stat->objects->database_users,
                'web_users' => (int)$server->stat->objects->web_users,
            ),
            'version' => array(
                'plesk_name' => (string)$server->stat->version->plesk_name,
                'plesk_version' => (string)$server->stat->version->plesk_version,
                'plesk_build' => (string)$server->stat->version->plesk_build,
                'os' => (string)$server->stat->version->plesk_os,
                'os_version' => (string)$server->stat->version->plesk_os_version,
                'os_release' => (string)$server->stat->version->os_release,
            ),
            'load_average' => array(
                '1min' => (int)$server->stat->load_avg->l1,
                '5min' => (int)$server->stat->load_avg->l5,
                '15min' => (int)$server->stat->load_avg->l15,
            ),
            'memory' => array(
                'total' => (int)$server->stat->mem->total,
                'used' => (int)$server->stat->mem->used,
                'free' => (int)$server->stat->mem->free,
                'shared' => (int)$server->stat->mem->shared,
                'buffer' => (int)$server->stat->mem->buffer,
                'cached' => (int)$server->stat->mem->cached,
            ),
            'swap' => array(
                'total' => (int)$server->stat->swap->total,
                'used' => (int)$server->stat->swap->used,
                'free' => (int)$server->stat->swap->free,
            ),
            'diskspace' => $diskspace,
        );
    }
}
