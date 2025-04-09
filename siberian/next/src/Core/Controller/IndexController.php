<?php

namespace App\Core\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class IndexController extends AbstractController
{
    #[Route('/', name: 'app_index')]
    public function index(): Response
    {
        return $this->render('index/index.html.twig', [
            'controller_name' => 'IndexController',
        ]);
    }

    #[Route('/requirements', name: 'app_requirements')]
    public function requirements(): Response
    {
        return $this->render('index/requirements.html.twig', [
            'controller_name' => 'IndexController',
            'php_version' => phpversion(),
        ]);
    }

    #[Route('/turbo', name: 'app_turbo')]
    public function turbo(): Response
    {
        return $this->render('index/turbo.html.twig', [
            'controller_name' => 'IndexController',
        ]);
    }
}
