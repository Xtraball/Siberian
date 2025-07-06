<?php

declare(strict_types=1);

namespace App\Plugin\Demo\Classes\Controller;

use App\Next\Plugin\Controller\AbstractPluginController;
use App\Plugin\Demo\Classes\Entity\Demo;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

#[Route('/demo')]
class DemoController extends AbstractPluginController
{
    public string $templateNamespace = '';

    #[Route('', name: 'demo_index', methods: ['GET'])]
    public function index(): Response
    {
        $demo = new Demo();
        $demo->name = 'Demo';
        $demo->description = 'This is a demo of a plugin';

        try {
            return $this->render('@Demo/demo/index.html.twig', [
                'demo' => $demo,
            ]);
        } catch (\Exception $e) {
            // If all else fails, return a basic HTML response
            return new Response('<h1>Demo</h1><p>This is a demo of a plugin</p><p>Error: ' . $e->getMessage() . '</p>');
        }
    }

    #[Route('/{id}', name: 'demo_show')]
    public function show(int $id): Response
    {
        $demo = new Demo();
        $demo->name = 'Demo';
        $demo->description = 'This is a demo of a plugin';

        try {
            return $this->render('demo_factor/index.html.twig', [
                'demo' => $demo,
                'id' => $id,
            ]);
        } catch (\Exception $e) {
            // If all else fails, return a basic HTML response
            return new Response('<h1>Demo</h1><p>This is a demo of a plugin</p><p>ID: ' . $id . '</p><p>Error: ' . $e->getMessage() . '</p>');
        }
    }
}
