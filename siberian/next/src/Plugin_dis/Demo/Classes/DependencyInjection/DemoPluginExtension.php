<?php

namespace App\Plugin\Demo\Classes\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

final class DemoPluginExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        var_dump('DemoPluginExtension');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $projectDir = $container->getParameter('kernel.project_dir');
        $loader = new YamlFileLoader($container, new FileLocator($projectDir . '/src/Plugin/Demo/Resources/config'));
        $loader->load('services.yaml');
    }

    public function getAlias(): string
    {
        return 'plugin_demo';
    }
}
