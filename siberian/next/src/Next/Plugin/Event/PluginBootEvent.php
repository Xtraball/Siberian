<?php

namespace App\Next\Plugin\Event;

use Symfony\Contracts\EventDispatcher\Event;

class PluginBootEvent extends Event
{
    public function __construct($plugin)
    {
        return new PluginEvent('boot', $plugin);
    }
}