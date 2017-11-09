<?php
namespace Plesk;

class DeleteSecretKey extends BaseRequest
{
    /**
     * @var string
     */
    public $xml_packet = <<<EOT
<?xml version="1.0"?>
<packet>
<secret_key>
   <delete>
      <filter>
         {KEYS}
      </filter>
   </delete>
</secret_key>
</packet>
EOT;

    /**
     * @param array $config
     * @param array $params
     * @throws ApiRequestException
     */
    public function __construct(array $config, array $params = array())
    {
        $keys = array();

        if (isset($params['key'])) {
            $keys[] = new Node('key', $params['key']);
        }

        if (isset($params['key']) && is_array($params['key'])) {
            foreach ($params['key'] as $key) {
                $keys[] = new Node('key', $key);
            }
        }

        $params['keys'] = new NodeList($keys);

        parent::__construct($config, $params);
    }

    /**
     * @param $xml
     * @return bool
     */
    protected function processResponse($xml)
    {
        return true;
    }
}
