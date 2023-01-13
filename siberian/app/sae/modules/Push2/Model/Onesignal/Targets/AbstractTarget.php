<?php

namespace Push2\Model\Onesignal\Targets;

require_once path('/lib/onesignal/vendor/autoload.php');

/**
 * Class AbstractTarget
 * @package Push2\Model\Onesignal\Targets
 */
abstract class AbstractTarget {

    /**
     * @var array
     */
    public $targets;

    /**
     * AbstractTarget constructor.
     * @param $targets
     */
    public function __construct($targets)
    {
        $this->targets = !is_array($targets) ? [$targets] : $targets;
    }

    /**
     * @return array
     */
    public function getTargets()
    {
        return $this->targets;
    }
}