<?php
namespace Plesk;

class ListDNSRecords extends BaseRequest
{
    /**
     * @var string
     */
    public $xml_packet = <<<EOT
<?xml version="1.0"?>
<packet version="1.6.3.0">
<dns>
        <get_rec>
                <filter>
                        <site-id>{SITE_ID}</site-id>
                </filter>
        </get_rec>
</dns>
</packet>
EOT;

    /**
     * @var array
     */
    protected $default_params = array(
        'site_id' => null,
    );

    /**
     * @param array $config
     * @param array $params
     * @throws ApiRequestException
     */
    public function __construct($config, $params)
    {
        if (isset($params['domain'])) {
            $request = new GetSite($config, array('domain' => $params['domain']));
            $info = $request->process();

            $params['site_id'] = $info['id'];
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

        foreach ($xml->dns->get_rec->children() as $node) {
            $result[] = array(
                'status' => (string)$node->status,
                'id' => (int)$node->id,
                'type' => (string)$node->data->type,
                'host' => (string)$node->data->host,
                'value' => (string)$node->data->value,
                'opt' => (string)$node->data->opt,
            );
        }

        return $result;
    }
}