<?php
namespace Plesk;

class ListSiteAliases extends BaseRequest
{
    /**
     * @var string
     */
    public $xml_packet = <<<EOT
<?xml version="1.0"?>
<packet version="1.6.3.0">
<site-alias>
    <get>
        {FILTER}
    </get>
</site-alias>
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
        $params['filter'] = new Node('filter');

        if (isset($params['domain'])) {
            $childNode = new Node('site-name', $params['domain']);
            $params['filter'] = new Node('filter', $childNode);
        }

        if (isset($params['site_id'])) {
            $childNode = new Node('site-id', $params['site_id']);
            $params['filter'] = new Node('filter', $childNode);
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

        foreach ($xml->{"site-alias"}->get->result as $alias) {
            $result[(int)$alias->id] = (string)$alias->info->name;
        }

        return $result;
    }
}
