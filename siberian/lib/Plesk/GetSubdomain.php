<?php
namespace Plesk;

class GetSubdomain extends BaseRequest
{
    /**
     * @var string
     */
    public $xml_packet = <<<EOT
<?xml version="1.0"?>
<packet version="1.6.0.0">
<subdomain>
    <get>
        <filter>
            <name>{NAME}</name>
        </filter>
    </get>
</subdomain>
</packet>
EOT;

    /**
     * @var array
     */
    protected $default_params = array(
        'name' => null,
    );

    /**
     * @param $xml
     * @return array
     * @throws ApiRequestException
     */
    protected function processResponse($xml)
    {
        $subdomain = $xml->subdomain->get->result;

        if ((string)$subdomain->status == 'error') {
            throw new ApiRequestException($subdomain);
        }

        if ((string)$subdomain->result->status == 'error') {
            throw new ApiRequestException($subdomain->result);
        }

        return array(
            'id' => (int)$subdomain->id,
            'status' => (string)$subdomain->status,
            'parent' => (string)$subdomain->data->parent,
            'name' => (string)$subdomain->data->name,
            'php' => (string)$this->findHostingProperty($subdomain->data, 'php'),
            'php_handler_type' => (string)$this->findHostingProperty($subdomain->data, 'php_handler_type'),
            'www_root' => (string)$this->findHostingProperty($subdomain->data, 'www_root'),
        );
    }

    /**
     * @param $node
     * @param $key
     * @return null|string
     */
    protected function findHostingProperty($node, $key)
    {
        foreach ($node->children() as $property) {
            if ($property->name == $key) {
                return (string)$property->value;
            }
        }
        return null;
    }
}
