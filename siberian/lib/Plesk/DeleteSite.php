<?php
namespace Plesk;

class DeleteSite extends BaseRequest
{
    /**
     * @var string
     */
    public $xml_packet = <<<EOT
<?xml version="1.0"?>
<packet version="1.6.3.0">
<site>
	<del>
		<filter>
			<id>{ID}</id>
		</filter>
	</del>
</site>
</packet>
EOT;

    /**
     * @var array
     */
    protected $default_params = array(
        'id' => null,
    );

    /**
     * @param $xml
     * @return bool
     * @throws ApiRequestException
     */
    protected function processResponse($xml)
    {
        if ($xml->site->del->result->status == 'error') {
            throw new ApiRequestException($xml->site->del->result);
        }

        return true;
    }
}
