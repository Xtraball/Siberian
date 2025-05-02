<?php

namespace App;

use App\Next\Core\Event\SystemEvent;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    public function boot(): void
    {
        # Custom kernel boot event
        $this->container->get('event_dispatcher')->dispatch(new SystemEvent(), SystemEvent::BOOT);
        parent::boot();
    }
}
