<?php

namespace App;

use App\Next\Plugin\DependencyInjection\Compiler\PluginPass;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    public function boot(): void
    {
        // Dispatch the custom kernel boot event
        // $this->container?->get('event_dispatcher')?->dispatch(new SystemEvent(), SystemEvent::BOOT);

        parent::boot();
    }

    protected function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new PluginPass());
    }

//    protected function build(ContainerBuilder $container): void
//    {
//        parent::build($container);
//
//        $container->addCompilerPass(new PluginCompilerPass());
//    }
}
