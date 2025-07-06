<?php

declare(strict_types=1);

namespace App\Plugin\Demo\Classes\Controller;

use App\Next\Plugin\Controller\AbstractPluginController;
use App\Plugin\Demo\Classes\Entity\Demo;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/demo')]
class DemoController extends AbstractPluginController
{
    #[Route('', name: 'demo_index')]
    public function index(): Response
    {
        $demo = new Demo();
        $demo->name = 'Demo';
        $demo->description = 'This is a demo of a plugin';
//        return $this->render('index.html.twig', [
//            'demo' => $demo,
//        ]);


        // render basic html without twig
        return new Response('<h1>Demo</h1><p>This is a demo of a plugin</p>');
    }

    #[Route('/{id}', name: 'demo_show')]
    public function show(int $id): Response
    {
        $demo = new Demo();
        $demo->name = 'Demo';
        $demo->description = 'This is a demo of a plugin';

        return $this->render('demo_factor/index.html.twig', [
            'demo' => $demo,
            'id' => $id,
        ]);
    }
}