<?php
namespace Plesk;

class DeleteEmailAddress extends BaseRequest
{
    /**
     * @var string
     */
    public $xml_packet = <<<EOT
<?xml version="1.0"?>
<packet version="1.6.3.0">
    <mail>
        <remove>
            <filter>
                <site-id>{SITE_ID}</site-id>
                <name>{USERNAME}</name>
            </filter>
        </remove>
    </mail>
</packet>
EOT;

    /**
     * @var array
     */
    protected $default_params = array(
        'email' => null,
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
            throw new ApiRequestException("Invalid email submitted");
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
     */
    protected function processResponse($xml)
    {
        if ($xml->mail->remove->result->status == 'error') {
            return false;
        }

        return true;
    }
}
