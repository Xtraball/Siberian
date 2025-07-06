<?php

namespace App\Next\Plugin\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class TwigNamespaceSubscriber implements EventSubscriberInterface
{
    private $twig;
    private $pluginNamespaces;

    public function __construct(Environment $twig, array $pluginNamespaces)
    {
        $this->twig = $twig;
        $this->pluginNamespaces = $pluginNamespaces;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
        ];
    }

    public function onKernelRequest(RequestEvent $event)
    {
        $loader = $this->twig->getLoader();
        if ($loader instanceof FilesystemLoader) {
            foreach ($this->pluginNamespaces as $namespace => $path) {
                $loader->addPath($path, $namespace);
            }
        }
    }
}