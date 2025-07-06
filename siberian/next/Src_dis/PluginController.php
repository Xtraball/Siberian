<?php

namespace App\_dis;

use App\Next\Plugin\Controller\AbstractPluginController;
use App\Next\Plugin\Entity\Plugin;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/plugins')]
class PluginController extends AbstractPluginController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        /** @var Plugin[] $plugins */
        $plugins = $this->pluginManager->getPlugins();

        return $this->render('index.html.twig', [
            'plugins' => $plugins,
        ]);
    }

    #[Route('/{plugin}', name: 'manage', requirements: ['plugin' => '\d+'], methods: ['GET', 'POST'])]
    public function manage(Plugin $plugin, Request $request): Response
    {
        $response = $this->pluginManager->manage($plugin, $request);
        if ($response !== null) {
            return $response;
        }
        $this->addFlash('warning', 'Plugin management not implemented for ' . $plugin->getName());
        return $this->redirectToRoute('plugin_index');
    }

    #[Route('/{plugin}/{action}', name: 'action', requirements: ['plugin' => '\d+'], methods: ['GET'])]
    public function action(Plugin $plugin, string $action, Request $request): Response
    {
        switch ($action) {
            case 'install':
                if ($plugin->isInstalled()) {
                    $this->addFlash('warning', 'Plugin "' . $plugin->getName() . '" is already installed.');
                    break;
                }
                $this->pluginManager->install($plugin);
                $this->addFlash('success', 'Plugin "' . $plugin->getName() . '" installed successfully.');
                break;
            case 'uninstall':
                if (!$plugin->isInstalled()) {
                    $this->addFlash('warning', 'Plugin "' . $plugin->getName() . '" is not installed.');
                    break;
                }
                if ($plugin->isEnabled()) {
                    $this->addFlash('warning', 'Plugin "' . $plugin->getName() . '" is enabled. Disable it first.');
                    break;
                }
                $this->pluginManager->uninstall($plugin);
                $this->addFlash('success', 'Plugin "' . $plugin->getName() . '" uninstalled successfully.');
                break;
            case 'enable':
                if ($plugin->isEnabled()) {
                    $this->addFlash('warning', 'Plugin "' . $plugin->getName() . '" is already enabled.');
                    break;
                }
                $this->pluginManager->enable($plugin);
                $this->addFlash('success', 'Plugin "' . $plugin->getName() . '" enabled successfully.');
                break;
            case 'disable':
                if (!$plugin->isEnabled()) {
                    $this->addFlash('warning', 'Plugin "' . $plugin->getName() . '" is not enabled.');
                    break;
                }
                $this->pluginManager->disable($plugin);
                $this->addFlash('success', 'Plugin "' . $plugin->getName() . '" disabled successfully.');
                break;
            case 'upgrade':
                if (!$plugin->isInstalled()) {
                    $this->addFlash('warning', 'Plugin "' . $plugin->getName() . '" is not installed.');
                    break;
                }
                if (!$plugin->isUpgradable()) {
                    $this->addFlash('warning', 'Plugin "' . $plugin->getName() . '" is already up to date.');
                    break;
                }
                $this->pluginManager->upgrade($plugin);
                $this->addFlash('success', 'Plugin "' . $plugin->getName() . '" upgraded successfully.');
                break;
            case 'test':
                $this->addFlash('info', 'Test action not implemented for ' . $plugin->getName());
                break;
            default:
                $this->addFlash('warning', 'Invalid action.');
        }
        return $this->redirectToRoute('plugin_index');
    }
}