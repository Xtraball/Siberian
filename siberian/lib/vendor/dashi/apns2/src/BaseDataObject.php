<?php
namespace Dashi\Apns2;

use JsonSerializable;

/**
 * base class for dealing with json data
 * @package Apns2
 */
abstract class BaseDataObject implements JsonSerializable
{

    public abstract function __construct($array = []);

    /**
     * @param array|object $json data to load from
     * @param array $childClasses definition of child objects, schema [ $varName => $className ]
     */
    public function loadFromJSON($json, $childClasses = [])
    {
        if ($json) {
            foreach ($json as $k => $v) {
                $varName = Utils::hyphenJoinedToCamelcase($k, false);
                if (!empty($childClasses[$varName]) && (is_object($v) || is_array($v))) {
                    $subObjectClassName = $childClasses[$varName];
                    // sub-object
                    $this->$varName = new $subObjectClassName($v);
                } else {
                    $this->$varName = $v;
                }
            }
        }
    }

    /**
     * default Serialize implementation
     * @return array
     */
    public function jsonSerialize()
    {
        $result = [];
        foreach ($this as $k => $v) {
            if ($v instanceof JsonSerializable) {
                $result[Utils::camelcaseToHyphenJoined($k)] = $v->jsonSerialize();
            } else {
                $result[Utils::camelcaseToHyphenJoined($k)] = $v;
            }
        }
        return array_filter($result, function (&$v) {
            return $v !== null;
        });
    }
}