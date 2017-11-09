<?php
namespace Plesk;

class DeleteSubscription extends BaseRequest
{
    /**
     * @var string
     */
    public $xml_packet = <<<EOT
<?xml version="1.0"?>
<packet>
<webspace>
	<del>
		<filter>
			<id>{ID}</id>
		</filter>
	</del>
</webspace>
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
        $webspace = $xml->webspace->del;

        if ($webspace->result->status == 'error') {
            throw new ApiRequestException($webspace->result);
        }

        return true;
    }
}
