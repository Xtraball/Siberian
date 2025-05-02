<?php

namespace App\Next\Plugin\Routing;

use App\Next\Plugin\Service\PluginManager;
use ReflectionClass;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Routing\AttributeRouteControllerLoader;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\RouteCollection;

class PluginLoader extends Loader
{
    private bool $isLoaded = false;
    private PluginManager $pluginManager;

    public function __construct(PluginManager $pluginManager, ?string $env = null)
    {
        $this->pluginManager = $pluginManager;
        parent::__construct($env);
    }

    /**
     * @inheritDoc
     */
    public function load(mixed $resource, ?string $type = null): mixed
    {
        if ($this->isLoaded) {
            throw new RuntimeException('Do not add the "plugin" loader twice');
        }

        $routes = new RouteCollection();


        $plugins = $this->pluginManager->getEnabledPlugins();

        /*
        $loader = new AttributeRouteControllerLoader();

        foreach ($plugins as $plugin) {
            $pluginInstance = $this->pluginManager->getPluginInstance($plugin);
            $reflectionClass = new ReflectionClass($pluginInstance);

            $classRoutes = $loader->load($reflectionClass->getName());

            foreach ($classRoutes as $routeName => $route) {
                $routes->add($routeName, $route);
            }
        }
         * */

        $this->isLoaded = true;
        return $routes;
    }

    /**
     * @inheritDoc
     */
    public function supports(mixed $resource, ?string $type = null): bool
    {
        return $type === 'plugin';
    }
}