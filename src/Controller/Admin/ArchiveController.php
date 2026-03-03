<?php

namespace App\Controller\Admin;

use App\Repository\SortieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class ArchiveController extends AbstractController
{
    #[IsGranted("ROLE_ADMIN")]
    #[Route('/admin/archive', name: 'app_admin_archive')]
    public function index(SortieRepository $sortieRepository): Response
    {
        $sorties = $sortieRepository->getSortiesArchivees();

        return $this->render('admin/archive.html.twig', [
            'sorties' => $sorties,
        ]);
    }
}
