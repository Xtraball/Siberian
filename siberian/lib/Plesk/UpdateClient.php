<?php
namespace Plesk;

class UpdateClient extends BaseRequest
{
    /**
     * @var string
     */
    public $xml_packet = <<<EOT
<?xml version="1.0"?>
<packet version="1.6.3.0">
    <customer>
        <set>
            <filter>
                <login>{USERNAME}</login>
            </filter>
            <values>
            	<gen_info>
				   {NODES}
   				</gen_info>
            </values>
        </set>
    </customer>
</packet>
EOT;

    /**
     * @var array
     */
    protected $default_params = array(
        'username' => '',
        'company_name' => '',
        'contact_name' => '',
        'password' => '',
        'status' => 0,
        'phone' => '',
        'fax' => '',
        'email' => '',
        'address' => '',
        'city' => '',
        'state' => '',
        'post_code' => '',
        'country' => '',
    );

    /**
     * @var array
     */
    protected $node_mapping = array(
        'password' => 'passwd',
        'status' => 'status',
        'phone' => 'phone',
        'fax' => 'fax',
        'email' => 'email',
        'address' => 'address',
        'city' => 'city',
        'state' => 'state',
        'post_code' => 'pcode',
        'country' => 'country',
    );

    /**
     * UpdateClient constructor.
     * @param array $config
     * @param array $params
     */
    public function __construct(array $config, array $params)
    {
        $params['nodes'] = $this->generateNodeList($params);
        parent::__construct($config, $params);
    }

    /**
     * @param $xml
     * @return bool
     * @throws ApiRequestException
     */
    protected function processResponse($xml)
    {
        $result = $xml->customer->set->result;

        if ($result->status == 'error') {
            throw new ApiRequestException($result);
        }

        return true;
    }
}
