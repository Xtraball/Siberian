<?php
namespace Plesk;

class ListEmailAddresses extends BaseRequest
{
    /**
     * @var string
     */
    public $xml_packet = <<<EOT
<?xml version="1.0"?>
<packet version="1.6.3.0">
<mail>
	<get_info>
		<filter>
			<site-id>{SITE_ID}</site-id>
		</filter>
		<mailbox/>
	</get_info>
</mail>
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

        foreach ($xml->mail->get_info->children() as $node) {
            $result[] = array(
                'status' => (string)$node->status,
                'id' => (int)$node->mailname->id,
                'username' => (string)$node->mailname->name,
                'enabled' => (bool)$node->mailname->mailbox->enabled,
                'password' => (string)$node->mailname->password->value,
            );
        }

        return $result;
    }
}
