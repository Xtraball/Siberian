<?php
// src/Controller/NextController.php
namespace App\Controller\Cms;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class NextController extends AbstractController
{
    #[Route('/next/cms')]
    public function index(): Response
    {
        return $this->render('cms/next/index.html.twig');
    }
}