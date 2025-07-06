<?php

namespace App\Plugin\Demo;

use App\Plugin\Demo\Classes\DependencyInjection\DemoPluginExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Bundle\TwigBundle\TwigBundle;

class DemoBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }

    public function getContainerExtension(): DemoPluginExtension
    {
        return new DemoPluginExtension();
    }

}
