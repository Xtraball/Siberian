<?php

declare(strict_types=1);

namespace App\Next\Plugin\Routing;

//use App\_dis\PluginManager;
//use App\Next\Plugin\Entity\Plugin;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Routing\RouteCollection;

class PluginLoader extends Loader
{
    const PLUGINS_DIR = __DIR__ . '/../../../Plugin';

    private bool $isLoaded = false;

    public function __construct(
        private FileLocator     $fileLocator,
        private LoggerInterface $logger,
        ?string                 $env = null)
    {
    }

    /**
     * @inheritDoc
     */
    public function load(mixed $resource, ?string $type = null): RouteCollection
    {
        $this->logger->info('Plugin loader');

        if ($this->isLoaded) {
            throw new RuntimeException('Do not add the "plugin" loader twice');
        }

        //$plugins = $this->pluginManager->getEnabledPlugins();
        $routes = new RouteCollection();

        $pluginsDir = self::PLUGINS_DIR;
        $finder = new Finder();
        $finder->directories()->in($pluginsDir)->depth(0);

        foreach ($finder as $dir) {
            $pluginName = $dir->getBasename();
            $file = self::PLUGINS_DIR . '/' . $pluginName . '/Resources/config/routes.yaml';

//            $configDir = $this->pluginManager->getResourcePathForKey($plugin, "config");

            $this->logger->info('Plugin loader: ' . $file);

            $importedRoutes = $this->import($file);
            $routes->addCollection($importedRoutes);
        }

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