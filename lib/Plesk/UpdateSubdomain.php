<?php
namespace Plesk;

class UpdateSubdomain extends BaseRequest
{
    /**
     * @var string
     */
    public $xml_packet = <<<EOT
<?xml version="1.0"?>
<packet version="1.5.2.0">
    <subdomain>
        <set>
            <filter>
                {FILTER}
            </filter>
            {PROPERTIES}
        </set>
    </subdomain>
</packet> 
EOT;

    /**
     * @var array
     */
    protected $default_params = array(
        'filter' => null,
        'properties' => null,
    );

    /**
     * @param array $config
     * @param array $params
     * @throws ApiRequestException
     */
    public function __construct(array $config, array $params)
    {
        if (isset($params['subdomain'])) {
            $params['filter'] = new Node('name', $params['subdomain']);
        }

        if (isset($params['id'])) {
            $params['filter'] = new Node('id', $params['id']);
        }

        $params['properties'] = $this->generatePropertyList($params['properties']);

        parent::__construct($config, $params);
    }

    /**
     * @param $xml
     * @return bool
     * @throws ApiRequestException
     */
    protected function processResponse($xml)
    {
        $result = $xml->subdomain->set->result;

        if ($result->status == 'error') {
            throw new ApiRequestException($result);
        }

        return true;
    }
}
