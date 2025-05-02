<?php

namespace App\Next\Plugin\Service;

use App\Next\Core\Event\SystemEvent;
use App\Next\Plugin\Entity\Plugin;
use App\Next\Plugin\Event\PluginEvent;
use App\Next\Plugin\Repository\PluginRepository;
use App\Next\Plugin\Interface\PluginInterface;

use Exception;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Log\LoggerInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use Symfony\Component\DependencyInjection\Attribute\TaggedLocator;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PluginManager
{
    private array $enabledPlugins = [];

    public function __construct(
        private readonly PluginRepository $repository,
        #[TaggedLocator('app.plugin')]
        private readonly ServiceLocator $pluginLocator,
        private readonly LoggerInterface $logger,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    /**
     * @return Plugin[]
     */
    public function getEnabledPlugins(): array
    {
        return $this->repository->getEnabledPlugins();
    }

    #[AsEventListener(SystemEvent::BOOT)]
    public function onBoot(): void
    {
        $plugins = $this->getEnabledPlugins();

        // log info in debug bar
        $this->logger->info('Plugins enabled: ' . count($plugins));

        foreach ($plugins as $plugin) {
            $this->enabledPlugins[] = $plugin;
            $event = new PluginEvent('boot', $plugin);
            $this->eventDispatcher->dispatch($event, PluginEvent::BOOT);
        }
    }

    public function enable(string|Plugin $plugin): void
    {
        $plugin = is_string($plugin) ? $this->getPluginByHandle($plugin) : $plugin;
        if (!$plugin) {
            return;
        }
        $pluginInstance = $this->getPluginInstance($plugin);
        $pluginInstance->enable();
        $plugin->setEnabled(true);
        $this->eventDispatcher->dispatch(
            new PluginEvent('enable', $plugin),
            PluginEvent::ENABLE
        );
        $this->repository->save($plugin, true);
    }

    public function disable(string|Plugin $plugin): void
    {
        $plugin = is_string($plugin) ? $this->getPluginByHandle($plugin) : $plugin;
        if (!$plugin) {
            return;
        }
        $pluginInstance = $this->getPluginInstance($plugin);
        $pluginInstance->disable();
        $plugin->setEnabled(false);
        $this->eventDispatcher->dispatch(
            new PluginEvent('disable', $plugin),
            PluginEvent::DISABLE
        );
        $this->repository->save($plugin, true);
    }

    public function upgrade(Plugin $plugin): void
    {
        $pluginInstance = $this->getPluginInstance($plugin);
        if (!$pluginInstance) {
            return;
        }
        try {
            $pluginInstance->upgrade();
        } catch (Exception $e) {
            $this->logger->error('Plugin upgrade failed: ' . $plugin->getHandle(), ['exception' => [
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
            ]]);
            return;
        }
        $plugin->setVersion($plugin->getLatestVersion());
        $plugin->setUpgradable(false);
        $this->eventDispatcher->dispatch(
            new PluginEvent('upgrade', $plugin),
            PluginEvent::UPGRADE
        );
        $this->repository->save($plugin, true);
    }

    public function getPluginByHandle(string $handle): ?Plugin
    {
        return $this->repository->findOneByHandle($handle);
    }

    public function install(Plugin $plugin): void
    {
        $pluginInstance = $this->getPluginInstance($plugin);
        if (!$pluginInstance) {
            return;
        }
        try {
            $pluginInstance->install();
        } catch (Exception $e) {
            $this->logger->error('Plugin installation failed: ' . $plugin->getHandle(), ['exception' => [
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
            ]]);
            return;
        }
        $plugin->setInstalled(true);
        $this->eventDispatcher->dispatch(
            new PluginEvent('install', $plugin),
            PluginEvent::INSTALL
        );
        $this->repository->save($plugin, true);
    }

    public function uninstall(Plugin $plugin): void
    {
        $pluginInstance = $this->getPluginInstance($plugin);
        if (!$pluginInstance) {
            return;
        }
        try {
            $pluginInstance->uninstall();
        } catch (Exception $e) {
            $this->logger->error('Plugin uninstallation failed: ' . $plugin->getHandle(), ['exception' => [
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
            ]]);
            return;
        }
        $plugin->setInstalled(false);
        $plugin->setEnabled(false);
        $plugin->setSettings([]);
        $this->eventDispatcher->dispatch(
            new PluginEvent('uninstall', $plugin),
            PluginEvent::UNINSTALL
        );
        $this->repository->save($plugin, true);
    }

    public function getPlugins(): array
    {
        $plugins = [];
        $pluginFiles = $this->getPluginFiles();

        foreach ($pluginFiles as $pluginFile) {
            $className = $this->getClassName($pluginFile);

            if ($this->isValidPluginClass($className)) {
                $pluginData = $this->getPluginData($className);
                $pluginEntity = $this->getPluginEntity($pluginData);
                $plugins[] = $pluginEntity;
            }
        }

        return $plugins;
    }

    public function getPluginInstance(string|Plugin $plugin): ?PluginInterface
    {
        $pluginHandle = is_string($plugin) ? $plugin : $plugin->getHandle();
        $pluginClass = 'App\\Plugin\\' . $pluginHandle;
        try {
            return $this->pluginLocator->get($pluginClass);
        } catch (ContainerExceptionInterface | NotFoundExceptionInterface) {
            $this->logger->error('Plugin not found: ' . $pluginClass);
            return null;
        }
    }

    private function getPluginFiles(): array
    {
        return glob(__DIR__ . '/../Plugin/*.php');
    }

    private function getClassName(string $pluginFile): string
    {
        return 'App\\Plugin\\' . basename($pluginFile, '.php');
    }

    private function isValidPluginClass(string $className): bool
    {
        return class_exists($className) && in_array(PluginInterface::class, class_implements($className));
    }

    private function getPluginData(string $className): array
    {
        $reflectionClass = new ReflectionClass($className);
        $methods = $reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC);
        $pluginData = [
            'name' => '',
            'version' => '0.0.1',
            'description' => '',
        ];

        foreach ($methods as $method) {
            if ($this->isPluginDataMethod($method)) {
                $methodName = $method->getName();
                try {
                    $field = strtolower(str_replace('get', '', $methodName));
                    $pluginData[$field] = $reflectionClass->getMethod($methodName)->invoke(null);
                } catch (ReflectionException) {
                    continue;
                }
            }
        }

        $pluginData['handle'] = $reflectionClass->getShortName();

        return $pluginData;
    }

    private function isPluginDataMethod(ReflectionMethod $method): bool
    {
        return $method->isStatic() &&
            $method->isPublic() &&
            $method->getNumberOfRequiredParameters() == 0 &&
            in_array($method->getName(), ['getName', 'getVersion', 'getDescription']);
    }

    private function getPluginEntity(array $pluginData): Plugin
    {
        $pluginEntity = $this->repository->findOneBy(['handle' => $pluginData['handle']]);

        if (!$pluginEntity) {
            $pluginEntity = new Plugin();
            $pluginEntity->setHandle($pluginData['handle']);
            $pluginEntity->setName($pluginData['name'] ?? $pluginData['handle']);
            $pluginEntity->setVersion($pluginData['version'] ?? '1.0.0');
            $pluginEntity->setDescription($pluginData['description'] ?? '');
            $pluginEntity->setInstalled(false);
            $pluginEntity->setEnabled(false);
            $this->repository->save($pluginEntity, true);
            $this->eventDispatcher->dispatch(
                new PluginEvent('register', $pluginEntity),
                PluginEvent::REGISTER
            );
        }

        $pluginEntity->setUpgradable(
            version_compare($pluginData['version'], $pluginEntity->getVersion(), '>')
        );
        $pluginEntity->setLatestVersion($pluginData['version']);

        return $pluginEntity;
    }

    public function manage(Plugin $plugin, Request $request): ?Response
    {
        $pluginInstance = $this->getPluginInstance($plugin);
        return $pluginInstance?->manage($request, $plugin);
    }

    // Example of a plugin
//    public function manage(Request $request, Plugin $plugin): Response
//    {
//        $settings = $plugin->getSettings();
//        $form = $this->formFactory->create(Settings::class, $settings);
//        $form->handleRequest($request);
//        if ($form->isSubmitted() && $form->isValid()) {
//            $plugin->setSettings($form->getData());
//            $this->entityManager->flush();
//            /** @var FlashBagAwareSessionInterface $session */
//            $session = $request->getSession();
//            $session->getFlashBag()->add('success', 'Settings saved.');
//        }
//        $content = $this->twig->render('@SamplePlugin/settings.html.twig', [
//            'plugin' => $plugin,
//            'form' => $form->createView(),
//        ]);
//        return new Response($content);
//    }
}