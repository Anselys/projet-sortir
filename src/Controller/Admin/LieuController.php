<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/lieu', name: 'app_lieu')]
final class LieuController extends AbstractController
{
    #[Route('/creer', name: '_creer')]
    public function lieu(): Response
    {
        return $this->render('lieu/lieu.html.twig', [
            'controller_name' => 'LieuController',
        ]);
    }
}
