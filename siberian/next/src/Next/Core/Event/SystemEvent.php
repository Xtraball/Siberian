<?php

namespace App\Next\Core\Event;

use Symfony\Contracts\EventDispatcher\Event;

class SystemEvent extends Event
{
    public const BOOT = 'app.next.core.event.boot';
}