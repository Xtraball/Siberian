<?php

namespace App\Next\Core\Controller;

use App\Legacy\Auth\Repository\AdminRepository;
use App\Legacy\Auth\Repository\CustomerRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Attribute\Route;

final class IndexController extends AbstractCoreController
{
    #[Route('/', name: 'app_index')]
    public function index(): Response
    {
        return $this->render('index.html.twig', [
            'controller_name' => 'IndexController',
        ]);
    }

    #[Route('/requirements', name: 'app_requirements')]
    public function requirements(AdminRepository $adminRepository, CustomerRepository $customerRepository): Response
    {
        // Get all admins
        $admins = $adminRepository->findAll();
        $customers = $customerRepository->findAll();

        return $this->render('requirements.html.twig', [
            'controller_name' => 'IndexController',
            'php_version' => phpversion(),
            'admins' => $admins,
            'customers' => $customers,
        ]);
    }

    #[Route('/turbo', name: 'app_turbo')]
    public function turbo(Session $session, Request $request): Response
    {
        if ($request->query->has('reset_counter')) {
            $session->set('session_counter', 0);
            $session->save();
        }

        $session->set('session_counter', $session->get('session_counter', 0) + 1);
        $session->save();

        return $this->render('turbo.html.twig', [
            'controller_name' => 'IndexController',
            'session_counter' => $session->get('session_counter'),
        ]);
    }
}
