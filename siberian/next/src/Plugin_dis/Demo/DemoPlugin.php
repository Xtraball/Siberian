<?php

declare(strict_types=1);

namespace App\Plugin\Demo;

use App\Next\Plugin\Entity\Plugin;
use App\Next\Plugin\Service\AbstractPlugin;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

#[AutoconfigureTag('app.plugin')]
class DemoPlugin extends AbstractPlugin
{
    public ContainerInterface $container;

    public static function getName(): string
    {
        return 'Demo';
    }

    public static function getVersion(): string
    {
        return '1.0.0';
    }

    public static function getAuthor(): string
    {
        return 'Anders';
    }

    public static function getDescription(): string
    {
        return 'This is a demo of a plugin';
    }

    public function install(): void
    {
//        $this->addBundle(new Bundle\DemoBundle());
    }

    public function uninstall(): void
    {
//        $this->removeBundle(new Bundle\DemoBundle());
    }

    public function enable(): void
    {
//        $this->addBundle(new Bundle\DemoBundle());
    }

    public function disable(): void
    {
//        $this->removeBundle(new Bundle\DemoBundle());
    }

    public function upgrade(): void
    {
    }

    public function boot(): void
    {
        // $cb = $this->container->get('kernel')->getContainer();
//
//        $projectDir = $this->container->getParameter('kernel.project_dir');
//        $loader = new YamlFileLoader($cb, new FileLocator($projectDir . '/src/Plugin/Demo/Resources/config'));
//        $loader->load('services.yaml');
    }

    public function manage(Request $request, Plugin $plugin): ?Response
    {
        return null;
    }

    private function addBundle(BundleInterface $bundle): void
    {
        $this->container->get('kernel')->addBundle($bundle);
    }

    private function removeBundle(BundleInterface $bundle): void
    {
        $this->container->get('kernel')->removeBundle($bundle);
    }

    private function addRoute(string $name, string $path, string $controller): void
    {
        $this->container->get('router')->addRoute($name, $path, ['_controller' => $controller]);
    }
}