<?php
namespace Plesk;

class UpdateEmailPassword extends BaseRequest
{
    /**
     * @var string
     */
    public $xml_packet = <<<EOT
<?xml version="1.0"?>
<packet version="1.6.0.2">
    <mail>
        <update>
            <add>
                <filter>
                    <domain_id>{DOMAIN_ID}</domain_id>
                    <mailname>
                        <name>{USERNAME}</name>
                        <password>{PASSWORD}</password>
                    </mailname>
                </filter>
            </add>
        </update>
    </mail>
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
        'domain_id' => null,
        'username' => null,
        'password' => null,
    );

    /**
     * @param array $config
     * @param array $params
     * @throws ApiRequestException
     */
    public function __construct($config, $params)
    {
        if (isset($params['email'])) {
            if (!filter_var($params['email'], FILTER_VALIDATE_EMAIL)) {
                throw new ApiRequestException("Invalid email submitted");
            }

            list($username, $domain) = explode("@", $params['email']);

            $request = new GetSite($config, array('domain' => $domain));
            $info = $request->process();

            $params['domain_id'] = $info['id'];
            $params['username'] = $username;
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
        $result = $xml->mail->update->result;

        if ($result->status == 'error') {
            throw new ApiRequestException($result);
        }

        $this->id = (int)$result->id;
        return true;
    }
}
