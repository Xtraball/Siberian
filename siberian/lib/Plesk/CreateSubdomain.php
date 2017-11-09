<?php
namespace Plesk;

class CreateSubdomain extends BaseRequest
{
    /**
     * @var string
     */
    public $xml_packet = <<<EOT
<?xml version="1.0" encoding="UTF-8"?>
<packet version="1.6.3.5">
    <subdomain>
        <add>
            <parent>{DOMAIN}</parent>
            <name>{SUBDOMAIN}</name>
            <property>
            	<name>www_root</name>
            	<value>{WWW_ROOT}</value>
            </property>
           	<property>
				<name>ftp_login</name>
				<value>{FTP_USERNAME}</value>
            </property>
            <property>
				<name>ftp_password</name>
				<value>{FTP_PASSWORD}</value>
            </property>
            <property>
				<name>ssl</name>
				<value>{SSL}</value>
            </property>
            <property>
				<name>php</name>
				<value>{PHP}</value>
            </property>
        </add>
    </subdomain>
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
        'subdomain' => null,
        'www_root' => null,
        'ftp_username' => '',
        'ftp_password' => '',
        'ssl' => true,
        'php' => true,
    );

    /**
     * @param array $config
     * @param array $params
     * @throws ApiRequestException
     */
    public function __construct($config, $params)
    {
        parent::__construct($config, $params);

        if (substr($this->params['www_root'], 0, 1) !== '/') {
            $this->params['www_root'] = '/' . $this->params['www_root'];
        }
    }

    /**
     * @param $xml
     * @return bool
     * @throws ApiRequestException
     */
    protected function processResponse($xml)
    {
        $result = $xml->subdomain->add->result;

        if ($result->status == 'error') {
            throw new ApiRequestException($result);
        }

        $this->id = (int)$result->id;
        return true;
    }
}
