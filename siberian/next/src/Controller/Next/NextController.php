<?php
// src/Controller/NextController.php
namespace App\Controller\Next;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class NextController
{
    #[Route('/next')]
    public function index(): Response
    {
        return new Response(
            '<html><body>Lucky number: ' . random_int(0, 100) . '</body></html>'
        );
    }
}