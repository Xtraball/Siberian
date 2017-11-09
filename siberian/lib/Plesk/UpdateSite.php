<?php
namespace Plesk;

class UpdateSite extends BaseRequest
{
    /**
     * @var string
     */
    public $xml_packet = <<<EOT
<?xml version="1.0"?>
<packet version="1.6.3.5">
<site>
    <set>
        <filter>
            <id>{ID}</id>
        </filter>
        <values>
			{NODES}
			{PROPERTIES}
		</values>
    </set>
</site>
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
        'id' => null,
        'nodes' => '',
        'properties' => '',
    );

    /**
     * @var array
     */
    protected $node_mapping = array(
        'status' => 'status',
        'domain' => 'name',
    );

    /**
     * UpdateSite constructor.
     * @param array $config
     * @param array $params
     */
    public function __construct(array $config, $params = array())
    {
        $properties = array();

        foreach (array('php', 'php_handler_type', 'webstat', 'www_root', 'php', 'php_handler_id', 'php_version') as $key) {
            if (isset($params[$key])) {
                $properties[$key] = $params[$key];
            }
        }

        if (count($properties) > 0) {
            $childNode = new Node('vrt_hst', $this->generatePropertyList($properties));
            $params['properties'] = new Node('hosting', $childNode);
        }

        if (count($params) > 0) {
            $params['nodes'] = new Node('gen_setup', $this->generateNodeList($params));
        }

        parent::__construct($config, $params);
    }

    /**
     * @param $xml
     * @return bool
     * @throws ApiRequestException
     */
    protected function processResponse($xml)
    {
        if ($xml->site->set->result->status == 'error') {
            throw new ApiRequestException($xml->site->set->result);
        }

        $this->id = (int)$xml->site->set->result->id;
        return true;
    }
}
