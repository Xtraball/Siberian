<?php
namespace Plesk;

class DeleteSiteAlias extends BaseRequest
{
    /**
     * @var string
     */
    public $xml_packet = <<<EOT
<?xml version="1.0"?>
<packet version="1.6.3.5">
    <site-alias>
        <delete>
            <filter>
                {FILTER}
            </filter>
        </delete>
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
        if (isset($params['domain'])) {
            $params['filter'] = new Node('name', $params['domain']);
        }

        if (isset($params['alias'])) {
            $params['filter'] = new Node('name', $params['alias']);
        }

        if (isset($params['id'])) {
            $params['filter'] = new Node('id', $params['id']);
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
        $result = $xml->{'site-alias'}->delete->result;

        if ($result->status == 'error') {
            throw new ApiRequestException($result);
        }

        return true;
    }
}
