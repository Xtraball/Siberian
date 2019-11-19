<?php

namespace Siberian\ACME;

/**
 * Class Exception
 * @package Siberian\ACME
 */
class Exception extends \Exception
{
    /**
     * @var
     */
    /**
     * @var array
     */
    private $type, $subproblems;

    /**
     * Exception constructor.
     * @param $type
     * @param $detail
     * @param array $subproblems
     */
    function __construct($type, $detail, $subproblems = [])
    {
        $this->type = $type;
        $this->subproblems = $subproblems;
        parent::__construct($detail . ' (' . $type . ')');
    }

    /**
     * @return array
     */
    function getType()
    {
        return $this->type;
    }

    /**
     * @return array
     */
    function getSubproblems()
    {
        return $this->subproblems;
    }
}