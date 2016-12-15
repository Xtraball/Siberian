<?php
namespace Plesk;

class RenameSubdomain extends BaseRequest
{
    /**
     * @var string
     */
    public $xml_packet = <<<EOT
<?xml version="1.0"?>
<packet version="1.5.2.0">
<subdomain>
    <rename>
        <id>{ID}</id>
        <name>{NAME}</name>
    </rename>
</subdomain>
</packet>
EOT;

    /**
     * @var array
     */
    protected $default_params = array(
        'id' => null,
        'name' => null,
    );

    /**
     * @param array $config
     * @param array $params
     * @throws ApiRequestException
     */
    public function __construct($config, $params)
    {
        if (isset($params['subdomain'])) {
            $request = new GetSubdomain($config, array('name' => $params['subdomain']));
            $info = $request->process();

            $this->params['id'] = $info['id'];
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
        $result = $xml->subdomain->rename->result;

        if ($result->status == 'error') {
            throw new ApiRequestException($result);
        }

        return true;
    }
}
