<?php
/**
 * Created by PhpStorm.
 * User: muntashir
 * Date: 1/10/17
 * Time: 11:19 AM
 *
 * @author      Muntashir Al-Islam <muntashir.islam96@gmail.com>
 * @version     2.0.0
 * @copyright   2017 (c) Muntashir Al-Islam
 * @license     MIT License
 */

namespace PListEditor;
/**
 * Class PListPropertyList
 * @package PListEditor
 */


class PListPropertyList implements \Iterator
{
    public $PListEditor,
        $DOMNodeList;

    /** @var \DOMNode[] $nodes */
    protected $nodes = [],
        $index = 0,
        $isDict,
        $isRoot,
        $types = ["array", "data", "date", "dict", "real", "integer", "string", "true", "false"];

    public function __construct(PListEditor $PListEditor, \DOMNodeList $DOMNodeList, $isDict = false, $isRoot = false){
        $this->PListEditor = $PListEditor;
        $this->DOMNodeList = $DOMNodeList;
        $this->index       = 0;
        $this->isDict      = $isDict;
        $this->isRoot      = $isRoot;
        $this->_sanitize();
    }

    /**
     * @return int
     */
    public function length(){
        return count($this->nodes);
    }

    /**
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return PListProperty
     * @since 5.0.0
     */
    public function current(){
        return new PListProperty($this->PListEditor, $this->isDict ? ["key" => $this->nodes[$this->index], "value" => $this->nodes[++$this->index]] : $this->nodes[$this->index]);
    }

    /**
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function next(){
        ++$this->index;
    }

    /**
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return int
     * @since 5.0.0
     */
    public function key(){
        return $this->index;
    }

    /**
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     * @since 5.0.0
     */
    public function valid(){
        return $this->index < $this->length();
    }

    /**
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function rewind(){
        $this->index = 0;
    }


    protected function _sanitize(){
        if($this->isDict) array_push($this->types, "key");
        if($this->isRoot) array_push($this->types, "plist");
        /** @var \DOMNode $DOMNode */
        foreach($this->DOMNodeList as $DOMNode){
            if(in_array($DOMNode->nodeName, $this->types)) array_push($this->nodes, $DOMNode);
        }
    }
}