<?php
namespace Plesk;

class DeleteClient extends BaseRequest
{
    /**
     * @var string
     */
    public $xml_packet = <<<EOT
<?xml version="1.0"?>
<packet version="1.6.3.0">
<customer>
	<del>
		<filter>
			{FILTER}
		</filter>
	</del>
</customer>
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
        if (isset($params['username'])) {
            $params['filter'] = new Node('login', $params['username']);
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
        $result = $xml->customer->del->result;

        if ($result->status == 'error') {
            throw new ApiRequestException($result);
        }

        return true;
    }
}
