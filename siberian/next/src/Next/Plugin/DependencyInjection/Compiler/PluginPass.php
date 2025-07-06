<?php

namespace App\Next\Plugin\DependencyInjection\Compiler;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Finder\Finder;
use Twig\Environment;

class PluginPass implements CompilerPassInterface
{
    const PLUGINS_DIR = __DIR__ . '/../../../../Plugin';

    public function process(ContainerBuilder $container)
    {
        try {
            $pluginsDir = self::PLUGINS_DIR;
            $finder = new Finder();
            $finder->directories()->in($pluginsDir)->depth(0);

            $pluginNamespaces = [];

            foreach ($finder as $dir) {
                $pluginName = $dir->getBasename();
                $bundleClass = "App\\Plugin\\{$pluginName}\\{$pluginName}Bundle";

                if (class_exists($bundleClass)) {
                    $bundle = new $bundleClass();
                    $extension = $bundle->getContainerExtension();
                    $container->registerExtension($extension);
                    // Manually load the extension if needed
                    $extension->load([], $container);

                    // Make sure Twig Bundle is loaded
                    if (!$container->hasDefinition('twig')) {
                        $container->register('twig', Environment::class);
                    }

                    $twigPath = self::PLUGINS_DIR . '/' . $pluginName . '/Resources/templates/';
                    if (is_dir($twigPath)) {
                        $pluginNamespaces[$pluginName] = $twigPath;

                        // Try to register the namespace directly if Twig is available
                        if ($container->has('twig')) {
                            try {
                                $twig = $container->get('twig');
                                $this->registerTwigNamespace($twig, $pluginName);
                            } catch (\Exception $e) {
                                // Log the error but continue
                                error_log('Failed to register Twig namespace for plugin ' . $pluginName . ': ' . $e->getMessage());
                            }
                        }
                    }

                    // Add namespace alias for the plugin
                    $this->addNamespaceAlias($container, $pluginName);

                    // Load routes
//                $this->loadRoutes($container, $pluginName);

                    // Load services
//                $this->loadServices($container, $pluginName);
                }
            }

            $container->setParameter('plugin_namespaces', $pluginNamespaces);
        } catch (\Exception $e) {
            error_log($e->getMessage());
            throw $e;
        }
    }

    private function addNamespaceAlias(ContainerBuilder $container, string $pluginName)
    {
        $alias = "@{$pluginName}";
        $namespace = "App\\Plugin\\{$pluginName}\\";
        $container->addObjectResource($this);
        $container->setParameter($alias, $namespace);

//        die('Plugin namespace alias: ' . $alias . ' = ' . $namespace);

//        $container->addObjectResource($this);
//        $container->getParameterBag()->add($alias, $namespace);
    }

    private function loadRoutes(ContainerBuilder $container, string $pluginName)
    {
        $file = self::PLUGINS_DIR . '/' . $pluginName . '/Resources/config/routes.yaml';
        if (file_exists($file)) {
            $locator = new FileLocator();
            $loader = new YamlFileLoader($container, $locator);
            $routeCollection = $loader->load($file);

            // Get the current route collection from the container
            $routingConfigurator = $container->get('routing.configurator');
            $routingConfigurator($routeCollection);
        }
    }

    private function loadServices(ContainerBuilder $container, string $pluginName)
    {
        $file = self::PLUGINS_DIR . '/' . $pluginName . '/Resources/config/services.yaml';
        if (file_exists($file)) {
            $loader = new YamlFileLoader($container, new FileLocator());
            $loader->load($file);
        }
    }

    public function registerTwigNamespace(Environment $twig, string $pluginName): void
    {
        $file = self::PLUGINS_DIR . '/' . $pluginName . '/Resources/templates/';
        if (file_exists($file)) {
            $loader = $twig->getLoader();
            $loader->addPath($file, $pluginName);
        }
    }
}
