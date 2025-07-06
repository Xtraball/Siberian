<?php

namespace App\Next\Core\Event;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Contracts\EventDispatcher\Event;

class SystemEvent extends Event
{
    public const BOOT = 'app.next.core.event.boot';
    public const BUILD = 'app.next.core.event.build';

    private ContainerInterface $container;

    public function setContainers(ContainerInterface $container): void
    {
        $this->container = $container;
    }

    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }
}