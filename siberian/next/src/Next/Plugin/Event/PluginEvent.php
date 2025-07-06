<?php

declare(strict_types=1);

namespace App\Next\Plugin\Event;

use Symfony\Contracts\EventDispatcher\Event;

class PluginEvent extends Event
{
    public const BOOT = 'app.next.plugin.event.boot';
    public const REGISTER = 'app.next.plugin.event.register';
    public const INSTALL = 'app.next.plugin.event.install';
    public const UPGRADE = 'app.next.plugin.event.upgrade';
    public const ENABLE = 'app.next.plugin.event.enable';
    public const DISABLE = 'app.next.plugin.event.disable';
    public const UNINSTALL = 'app.next.plugin.event.uninstall';

    public function __construct(private readonly string $eventName, private readonly object $plugin)
    {
    }

    public function getPlugin(): object
    {
        return $this->plugin;
    }

    # Not sure this is useful, have to check
    public function getEventName(): string
    {
        return $this->eventName;
    }
}