<?php
/**
 * Created by PhpStorm.
 * User: muntashir
 * Date: 1/10/17
 * Time: 12:04 PM
 *
 * @author      Muntashir Al-Islam <muntashir.islam96@gmail.com>
 * @version     2.0.0
 * @copyright   2017 (c) Muntashir Al-Islam
 * @license     MIT License
 */

namespace PListEditor;


/**
 * Class PListProperty
 *
 *
 * @package PListEditor
 */
class PListProperty
{
    const PL_ARRAY = "array",
        PL_DATA    = "data",
        PL_DATE    = "date",
        PL_DICT    = "dict",
        PL_REAL    = "real",
        PL_INTEGER = "integer",
        PL_STRING  = "string",
        PL_TRUE    = "true",
        PL_FALSE   = "false",
        PL_KEY     = "key";

    public $PListEditor,
        $DOMNode,
        $keyDOMNode;

    protected $types = [self::PL_ARRAY, self::PL_DATA, self::PL_DATE, self::PL_DICT, self::PL_REAL, self::PL_INTEGER, self::PL_STRING, self::PL_TRUE, self::PL_FALSE],
        $collections = [self::PL_ARRAY, self::PL_DICT],
        $hasKey      = false;

    /**
     * PListProperty constructor.
     * @param PListEditor $PListEditor
     * @param \DOMNode|\DOMNode[] $DOMNode Two DOMNode ('key', 'value') if dict otherwise only one DOMNode
     */
    public function __construct(PListEditor $PListEditor, $DOMNode){
        $this->PListEditor = $PListEditor;
        $this->DOMNode     = $DOMNode;
        $this->_checkIfDict();
    }

    /**
     * Get a certain property using a key
     *
     * NOTE: only applied inside a dict type
     *
     * @param string $key
     * @return null|PListProperty
     * @since 1.1.0
     */
    public function getProperty($key){
        if(!$this->hasProperties() && $this->type() != self::PL_DICT) return null;

        $innerProperties = $this->innerProperties();
        foreach($innerProperties as $property){
            if($property->key() == $key) return $property;
        }
        return null;
    }

    /**
     * Get a certain property using a index
     *
     * NOTE: only applied inside an array type
     *
     * @param string $index
     * @return null|PListProperty
     * @since 1.1.0
     */
    public function getItem($index){
        if(!$this->hasProperties() && $this->type() != self::PL_ARRAY) return null;

        $innerProperties = $this->innerProperties();
        $i = 0;
        foreach($innerProperties as $property){
            if($index == $i) return $property;
            $i++;
        }
        return null;
    }

    /**
     * Adds a new property under the selected property
     *
     * @param string $type
     * @param mixed $value
     * @param null $key
     * @return null|PListProperty
     */
    public function addProperty($type, $value = null, $key = null){ // TODO: need further enhancement
        if (!in_array($type, $this->types)) {
            return null;
        }

        if ($this->type() == self::PL_DICT) {
            $keyNode = $this->PListEditor->plist->createElement("key");
            $keyNode->nodeValue = $key;
            $this->DOMNode->appendChild($keyNode);
        }

        if ($type == self::PL_ARRAY) {
            $arrayNode = $this->PListEditor->plist->createElement("array");
            foreach ($value as $val) {
                $stringNode = $this->PListEditor->plist->createElement("string");
                $stringNode->nodeValue = $val;
                $arrayNode->appendChild($stringNode);
            }
            $this->DOMNode->appendChild($arrayNode);
            $valueNode = null;
        } else {
            $valueNode = $this->PListEditor->plist->createElement($type);
            if(!in_array($type, [self::PL_TRUE, self::PL_FALSE])) $valueNode->nodeValue = $value;
            $this->DOMNode->appendChild($valueNode);
        }

        /** @noinspection PhpUndefinedVariableInspection */
        return new self($this->PListEditor, ($this->type() == self::PL_DICT) ? ["key" => $keyNode, "value" => $valueNode] : $valueNode);
    }

    /**
     * Removes a property from the selected property
     *
     * @param string $name can either be key name (for DICT type) or value
     * @return bool true on success, false on failure
     */
    public function removeProperty($name){
        if(!$this->hasProperties()) return false;
        $innerProperties = $this->value();
        if($this->type() == self::PL_DICT){     // $name = key name
            foreach($innerProperties as $property){
                if($property->key() == $name){
                    $this->DOMNode->removeChild($property->keyDOMNode);
                    $this->DOMNode->removeChild($property->DOMNode);
                    return true;
                }
            }
        }else{                                  // $name = value
            foreach($innerProperties as $property){
                if($property->value() == $name){
                    $this->DOMNode->removeChild($property->DOMNode);
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Get key if it has any (Only for Dict)
     *
     * @return null|string
     */
    public function key(){
        if($this->hasKey()){
            return $this->keyDOMNode->nodeValue;
        }
        return null;
    }

    public function value(){
        return ($this->hasProperties()) ? $this->innerProperties() : $this->DOMNode->nodeValue;
    }

    public function type(){
        return $this->DOMNode->nodeName;
    }

    /**
     * Checks whether the property has properties
     *
     * Only $collections has properties
     *
     * @param bool $isRoot
     * @return bool
     */
    public function hasProperties($isRoot = false){
        if($isRoot) array_push($this->collections, "plist");
        return (in_array(self::type(), $this->collections)) ? $this->DOMNode->hasChildNodes() : false;
    }

    public function innerProperties(){
        return self::hasProperties() ? (self::type() == self::PL_DICT ? new PListPropertyList($this->PListEditor, $this->DOMNode->childNodes, true) : new PListPropertyList($this->PListEditor, $this->DOMNode->childNodes)) : null;
    }

    //

    public function hasKey(){
        return $this->hasKey;
    }

    /**
     * Special modifications for DICT type
     */
    private function _checkIfDict(){
        if(is_array($this->DOMNode)){
            $this->keyDOMNode = $this->DOMNode['key'];
            $this->DOMNode    = $this->DOMNode['value'];
            $this->hasKey     = true;
        }
    }
}