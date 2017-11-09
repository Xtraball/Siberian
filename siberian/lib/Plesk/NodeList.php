<?php
namespace Plesk;

class NodeList
{
    /**
     * @param array $array
     * @return NodeList
     */
    public static function createFromKeyValueArray(array $array)
    {
        $nodes = array();

        foreach ($array as $key => $value) {
            $nodes[] = new Node($key, $value);
        }

        return new NodeList($nodes);
    }

    /**
     * @var array|Node[]
     */
    protected $nodes = array();

    /**
     * NodeList constructor.
     * @param array Node[] $nodes
     */
    public function __construct(array $nodes = array())
    {
        $this->nodes = $nodes;
    }

    /**
     * @return array
     */
    public function getNodes()
    {
        return $this->nodes;
    }

    /**
     * @param array $nodes
     */
    public function setNodes($nodes)
    {
        $this->nodes = $nodes;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $result = '';

        foreach ($this->nodes as $node) {
            $result .= (string) $node;
        }

        return $result;
    }
}
