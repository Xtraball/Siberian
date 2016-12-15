<?php
namespace Plesk;

class CreateDatabaseUser extends BaseRequest
{
    /**
     * @var string
     */
    public $xml_packet = <<<EOT
<?xml version="1.0"?>
<packet version="1.6.5.0">
<database>
    <add-db-user>
        {OPTIONS}
        <login>{USERNAME}</login>
        <password>{PASSWORD}</password>
    </add-db-user>
</database>
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
        'options' => null,
        'username' => null,
        'password' => null
    );

    public function __construct($config, $params)
    {
        if (isset($params['server_id']) && isset($params['subscription_id'])) {
            $params['options'] = new NodeList(array(
                new Node('webspace-id', $params['subscription_id']),
                new Node('db-server-id', $params['server_id']),
            ));
        } elseif (isset($params['database_id'])) {
            $params['options'] = new Node('db-id', $params['database_id']);
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
        if ($xml->database->{'add-db-user'}->result->status == 'error') {
            throw new ApiRequestException($xml->database->{'add-db-user'}->result);
        }

        $this->id = (int)$xml->database->{'add-db-user'}->result->id;
        return true;
    }

}