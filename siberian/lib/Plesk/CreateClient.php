<?php
namespace Plesk;

class CreateClient extends BaseRequest
{
    /**
     * @var string
     */
    public $xml_packet = <<<EOT
<?xml version="1.0" encoding="UTF-8"?>
<packet version="1.6.3.0">
<customer>
<add>
   <gen_info>
       {NODES}
   </gen_info>
</add>
</customer>
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
        'company_name' => '',
        'contact_name' => null,
        'username' => null,
        'password' => null,
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
        'company_name' => 'cname',
        'contact_name' => 'pname',
        'username' => 'login',
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
     * CreateClient constructor.
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
        $result = $xml->customer->add->result;

        if ($result->status == 'error') {
            throw new ApiRequestException($result);
        }

        $this->id = (int)$result->id;
        return true;
    }
}
