<?php

namespace App\Plugin\Demo\Classes\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Routing\Loader\YamlFileLoader as RoutingYamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

final class DemoPluginExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        // Check if Twig Bundle is available
        if (!class_exists('Symfony\Bundle\TwigBundle\TwigBundle')) {
            throw new \RuntimeException('The Twig Bundle is not available. Try running "composer require symfony/twig-bundle".');
        }

        // Ensure twig service is registered
        if (!$container->hasDefinition('twig') && !$container->hasAlias('twig')) {
            throw new \RuntimeException('The twig service is not registered. Make sure the Twig Bundle is properly configured.');
        }

        $projectDir = $container->getParameter('kernel.project_dir');
        $configPath = $projectDir . '/src/Plugin/Demo/Resources/config';

        $loader = new YamlFileLoader($container, new FileLocator($configPath));
        $loader->load('services.yaml');

        $serviceIds = $container->getServiceIds();
        error_log(print_r($serviceIds, true));

        // Verify and load routes
//        $routingLoader = new RoutingYamlFileLoader(new FileLocator($configPath));
//        $routeCollection = $routingLoader->load('routes.yaml');
//        $container->get('router')->addCollection($routeCollection);
    }

    public function getAlias(): string
    {
        return 'plugin_demo';
    }
}
