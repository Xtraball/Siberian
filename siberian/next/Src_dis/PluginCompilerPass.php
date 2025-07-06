<?php

declare(strict_types=1);

namespace App\_dis;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class PluginCompilerPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     * @return void
     * @throws \Exception
     */
    public function process(ContainerBuilder $container): void
    {
        var_dump('plugin compiler pass');

//        $container->setParameter('kernel.project_dir', $container->getParameter('kernel.project_dir'));

        $projectDir = $container->getParameter('kernel.project_dir');
        $loader = new YamlFileLoader($container, new FileLocator());

        $loader->load($projectDir . '/src/Plugin/Demo/Resources/config/services.yaml');
    }
}