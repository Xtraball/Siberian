<?php

declare(strict_types=1);

namespace App\Next\Plugin\Service;

use App\_dis\PluginManager;
use App\Next\Plugin\Entity\Plugin;
use App\Next\Plugin\Event\PluginEvent;
use App\Next\Plugin\Interface\PluginInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use ReflectionObject;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Service\Attribute\Required;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

abstract class AbstractPlugin implements PluginInterface
{
    protected EntityManagerInterface $entityManager;
    protected LoggerInterface $logger;
    protected Environment $twig;
    protected FormFactoryInterface $formFactory;
    protected EventDispatcherInterface $eventDispatcher;
    protected Security $security;
    protected PluginManager $pluginManager;
    protected ?Plugin $plugin = null;
    protected Content $contentService;
    protected CacheInterface $cache;
    protected ContainerInterface $container;

    #[Required]
    public function setCache(CacheInterface $cache): void
    {
        $this->cache = $cache;
    }

    #[Required]
    public function setSecurity(Security $security): void
    {
        $this->security = $security;
    }

    #[Required]
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher): void
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    #[Required]
    public function setPluginManager(PluginManager $pluginManager): void
    {
        $this->pluginManager = $pluginManager;
    }

    #[Required]
    public function setContainer(ContainerInterface $container): void
    {
        $this->container = $container;
    }

    #[Required]
    public function setTwig(Environment $twig): void
    {
        $this->twig = $twig;
        $object = new ReflectionObject($this);
        $dir = dirname($object->getFileName());
        $className = $object->getShortName();
        $dir .= '/' . $className . '/templates';
        if (is_dir($dir)) {
            /** @var FilesystemLoader $loader */
            $loader = $this->twig->getLoader();
            $loader->addPath($dir, $className);
        }
    }

    #[Required]
    public function setFormFactory(FormFactoryInterface $formFactory): void
    {
        $this->formFactory = $formFactory;
    }

    #[Required]
    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    #[Required]
    public function setEntityManager(EntityManagerInterface $entityManager): void
    {
        $this->entityManager = $entityManager;
    }

    protected function addEventListener(string $event, string|callable $method, int $priority = 0): void
    {
        if (is_string($method)) {
            $method = [$this, $method];
        }
        $this->eventDispatcher->addListener($event, $method, $priority);
    }

    public function install(): void
    {
        return;
    }

    public function uninstall(): void
    {
        return;
    }

    public function enable(): void
    {
        return;
    }

    public function disable(): void
    {
        return;
    }

    public function upgrade(): void
    {
        return;
    }

    public function boot(): void
    {
        return;
    }

    public function manage(Request $request, Plugin $plugin): ?Response
    {
        return null;
    }

    // this event will be dispatched by the plugin manager
    #[AsEventListener(PluginEvent::BOOT, priority: -255)]
    public function onPluginBoot(PluginEvent $event): void
    {
        var_dump('PluginEvent::BOOT');

        /** @var Plugin $plugin */
        $plugin = $event->getPlugin();
        $object = new ReflectionObject($this);
        $className = $object->getShortName();
        if ($plugin->getHandle() === $className) {
            $this->plugin = $plugin;
            $this->boot();
        }
    }

    public static function getVersion(): string
    {
        return '1.0.0';
    }

    abstract public static function getName(): string;

    abstract public static function getAuthor(): string;

    abstract public static function getDescription(): string;
}