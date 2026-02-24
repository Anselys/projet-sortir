<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin', name: 'app_admin')]
final class VilleController extends AbstractController
{
    #[Route('/ville', name: '_ville')]
    public function index(): Response
    {
        return $this->render('admin/ville.html.twig', [
            'controller_name' => 'Admin/VilleController',
        ]);
    }
}
