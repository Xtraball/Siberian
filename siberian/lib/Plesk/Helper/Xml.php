<?php
namespace Plesk\Helper;

use Plesk\ApiRequestException;
use Plesk\Node;
use Plesk\NodeList;
use SimpleXMLElement;

class Xml
{
    /**
     * @param string $string
     *
     * @return SimpleXMLElement
     * @throws ApiRequestException
     */
    public static function convertStringToXml($string)
    {
        $xml = new SimpleXMLElement($string);

        if (!$xml instanceof SimpleXMLElement) {
            throw new ApiRequestException("Cannot parse server response: {$string}");
        }

        return $xml;
    }

    /**
     * @param $node
     * @param $key
     * @param string $node_name
     * @return null|string
     */
    public static function findProperty($node, $key, $node_name = 'property')
    {
        foreach ($node->children() as $property) {
            if ($property->getName() == $node_name && $property->name == $key) {
                return (string)$property->value;
            }
        }

        return null;
    }

    /**
     * @param $node
     * @param string $node_name
     * @return array
     */
    public static function getProperties($node, $node_name = 'property')
    {
        $result = array();

        foreach ($node->children() as $property) {
            if ($property->getName() == $node_name) {
                $result[(string)$property->name] = (string)$property->value;
            }
        }

        return $result;
    }

    /**
     * Generates the xml for a standard property list
     *
     * @param array $properties
     * @return string
     */
    public static function generatePropertyList(array $properties)
    {
        $nodes = array();

        foreach ($properties as $key => $value) {
            $nodeList = new NodeList(array(
                new Node('name', $key),
                new Node('value', $value),
            ));

            $nodes[] = new Node('property', $nodeList);
        }

        return new NodeList($nodes);
    }

    /**
     * @param array $nodeMapping
     * @param array $properties
     * @return NodeList
     */
    public static function generateNodeList(array $nodeMapping, array $properties)
    {
        $nodes = array();

        foreach ($properties as $key => $value) {
            if (isset($nodeMapping[$key])) {
                $tag = $nodeMapping[$key];
                $nodes[] = new Node($tag, $value);
            }
        }

        return new NodeList($nodes);
    }

    /**
     * @param $input
     * @return string
     */
    public static function sanitize($input)
    {
        return htmlspecialchars($input);
    }

    /**
     * @param array $array
     * @return array
     */
    public static function sanitizeArray(array $array)
    {
        foreach ($array as &$value) {
            if (is_string($value)) {
                $value = self::sanitize($value);
            }
        }

        return $array;
    }
}
