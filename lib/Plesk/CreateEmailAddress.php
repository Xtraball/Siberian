<?php
namespace Plesk;

class CreateEmailAddress extends BaseRequest
{
    /**
     * @var string
     */
    public $xml_packet = <<<EOT
<?xml version="1.0" encoding="UTF-8"?>
<packet version="1.6.3.5">
    <mail>
        <create>
            <filter>
                <site-id>{SITE_ID}</site-id>
                <mailname>
                    <name>{USERNAME}</name>
                    <mailbox>
                        <enabled>{ENABLED}</enabled>
                    </mailbox>
                    <password>
                        <value>{PASSWORD}</value>
                        <type>plain</type>
                    </password>
                </mailname>
            </filter>
        </create>
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
        'email' => null,
        'password' => null,
        'enabled' => true,
    );

    /**
     * @param array $config
     * @param array $params
     * @throws ApiRequestException
     */
    public function __construct($config, $params)
    {
        parent::__construct($config, $params);

        if (!filter_var($this->params['email'], FILTER_VALIDATE_EMAIL)) {
            throw new ApiRequestException("Error: Invalid email submitted");
        }

        list($username, $domain) = explode("@", $this->params['email']);

        $request = new GetSite($config, array('domain' => $domain));
        $info = $request->process();

        $this->params['site_id'] = $info['id'];
        $this->params['username'] = $username;
    }

    /**
     * @param $xml
     * @return bool
     * @throws ApiRequestException
     */
    protected function processResponse($xml)
    {
        $result = $xml->mail->create->result;

        if ($result->status == 'error') {
            throw new ApiRequestException($result);
        }

        $this->id = (int)$result->mailname->id;
        return true;
    }
}
