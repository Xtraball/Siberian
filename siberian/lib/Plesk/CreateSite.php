<?php
namespace Plesk;

class CreateSite extends BaseRequest
{
    /**
     * @var string
     */
    public $xml_packet = <<<EOT
<?xml version="1.0"?>
<packet version="1.6.3.5">
<site>
	<add>
		<gen_setup>
			<name>{DOMAIN}</name>
			<webspace-id>{SUBSCRIPTION_ID}</webspace-id>
		</gen_setup>
		<hosting>
			<vrt_hst>
				<property>
					<name>php</name>
					<value>{PHP}</value>
				</property>
				<property>
					<name>php_handler_id</name>
					<value>{PHP_HANDLER_ID}</value>
				</property>
				<property>
					<name>webstat</name>
					<value>{WEBSTAT}</value>
				</property>
				<property>
					<name>www_root</name>
					<value>{WWW_ROOT}</value>
				</property>
			</vrt_hst>
		</hosting>
	</add>
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
        'domain' => null,
        'subscription_id' => null,
        'php' => true,
        'php_handler_id' => 'fastcgi',
        'webstat' => 'none',
        'www_root' => null,
    );

    /**
     * @param array $config
     * @param array $params
     * @throws ApiRequestException
     */
    public function __construct($config, $params = array())
    {
        if (!isset($params['www_root'])) {
            $params['www_root'] = $params['domain'];
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
        if ($xml->site->add->result->status == 'error') {
            throw new ApiRequestException($xml->site->add->result);
        }

        $this->id = (int)$xml->site->add->result->id;
        return true;
    }
}
