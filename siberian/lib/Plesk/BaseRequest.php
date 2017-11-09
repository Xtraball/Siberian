<?php
namespace Plesk;

use Plesk\Helper\Xml;
use SimpleXMLElement;

abstract class BaseRequest
{
    /**
     * @var HttpRequestContract
     */
    protected $http;

    /**
     * @var int
     */
    protected $port = 8443;

    /**
     * @var array
     */
    protected $config = array();

    /**
     * @var array
     */
    protected $params = array();

    /**
     * @var array
     */
	protected $default_params = array();

    /**
     * @var array
     */
	protected $node_mapping = array();

    /**
     * @var string
     */
	protected $property_template = <<<EOT
<property>
	<name>{KEY}</name>
	<value>{VALUE}</value>
</property>
EOT;

    /**
     * @var string
     */
    public $xml_filename;

    /**
     * @var string
     */
    public $xml_packet;

    /**
     * @var string
     */
    public $request_header;

    /**
     * @var string|\Exception
     */
    public $error;

    /**
     * @var string
     */
    public $xml_response;

    /**
     * @param $xml
     * @return string|bool|array
     */
    abstract protected function processResponse($xml);

    /**
     * @param array $config
     * @param array $params
     * @param HttpRequestContract|null $http
     * @throws ApiRequestException
     */
    public function __construct(array $config, array $params = array(), HttpRequestContract $http = null)
    {
        $this->config = $config;
        $this->params = $params;

        if (!$this->check_params()) {
            throw new ApiRequestException("Error: Incorrect request parameters submitted");
        }

        $parsed_url = parse_url($this->config['host']);

        /** Extract port if different from default */
        if (isset($parsed_url["port"])) {
            $this->port = $parsed_url["port"];
        }

        /** Extract host whether the format */
        if(isset($parsed_url["host"])) {
            $this->config['host'] = $parsed_url["host"];
        } elseif(isset($parsed_url["path"])) {
            $this->config['host'] = $parsed_url["path"];
        }

        $this->params = Xml::sanitizeArray($this->params);

        $this->http = is_null($http) ? new CurlHttpRequest($this->config['host'], $this->port) : $http;
        if (isset($this->config['username']) && isset($this->config['password'])) {
            $this->http->setCredentials($this->config['username'], $this->config['password']);
        }

        if (isset($this->config['key'])) {
            $this->http->setSecretKey($this->config['key']);
        }

        if (is_null($this->xml_packet) && file_exists($this->xml_filename)) {
            $this->xml_packet = file_get_contents($this->xml_filename);
        }

        if (is_null($this->xml_packet)) {
            throw new ApiRequestException("Error: No XML Packet supplied");
        }
    }

    /**
     * Checks the required parameters were submitted. Optional parameters are specified with a non NULL value in the
     * class declaration
     *
     * @return bool
     * @throws ApiRequestException
     */
    protected function check_params()
    {
        if (!is_array($this->default_params)) {
            return false;
        }

        foreach ($this->default_params as $key => $value) {
            if (!isset($this->params[$key])) {
                if (is_null($value)) {
                    return false;
                } else {
                    $this->params[$key] = $value;
                }
            }
        }

        return true;
    }

    /**
     * Generates the xml for a standard property list
     *
     * @param array $properties
     * @return string
     */
    protected function generatePropertyList(array $properties)
    {
        return Xml::generatePropertyList($properties);
    }

    /**
     * Generates the xml for a list of nodes
     *
     * @param array $properties
     * @return string
     */
    protected function generateNodeList(array $properties)
    {
        return Xml::generateNodeList($this->node_mapping, $properties);
    }

    /**
     * Submits the xml packet to the Plesk server and forwards the response on for processing
     *
     * @return object
     */
    public function process()
    {
        try {
            $response = $this->sendRequest($this->getPacket());

            if ($response !== false) {
                $this->xml_response = $response;
                $responseXml = Xml::convertStringToXml($response);
                $this->checkResponse($responseXml);

                return $this->processResponse($responseXml);
            }
        } catch (ApiRequestException $e) {
            $this->error = $e;
        }

        return false;
    }

    /**
     * Inserts the submitted parameters into the xml packet
     *
     * @return string
     */
    protected function getPacket()
    {
        $packet = $this->xml_packet;

        foreach ($this->params as $key => $value) {
            if (is_bool($value)) {
                $value = $value ? 'true' : 'false';
            }

            $packet = str_replace('{'.strtoupper($key).'}', $value, $packet);
        }

        return $packet;
    }

    /**
     * Performs a Plesk API request, returns raw API response text
     *
     * @param string $packet
     *
     * @return string
     * @throws ApiRequestException
     */
    private function sendRequest($packet)
    {
        $domdoc = new \DomDocument('1.0', 'UTF-8');
        if ($domdoc->loadXml($packet) === false) {
            $this->error = 'Failed to load payload';
            return false;
        }

        $body = $domdoc->saveHTML();
        return $this->http->sendRequest($body);
    }

    /**
     * Check data in API response
     *
     * @param SimpleXMLElement $response
     *
     * @return void
     * @throws ApiRequestException
     */
    private function checkResponse(SimpleXMLElement $response)
    {
        if ((string)$response->system->status === 'error') {
            throw new ApiRequestException($response->system);
        }
    }
}
