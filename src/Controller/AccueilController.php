<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\TriSortiesType;
use App\Repository\SiteRepository;
use App\Repository\SortieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AccueilController extends AbstractController
{
    #[Route('/', name: 'app_accueil')]
    public function index(Request $request, SortieRepository $sortieRepository, SiteRepository $siteRepository): Response
    {
        $sorties = [];
        $today = new \DateTime();
        $triForm = $this->createForm(TriSortiesType::class);
        $triForm->handleRequest($request);

        if ($triForm->isSubmitted() && $triForm->isValid()) {
            $this->$sorties = $sortieRepository->findByTri($triForm);

            $this->addFlash('success', 'Tri activé');
            return $this->redirectToRoute('', [
                'sorties' => $sorties,
                'today' => $today,
                'tri_form' => $triForm->createView(),
            ]);
        }
//
//        // par défaut les sorties sont filtrées sur le site de l'utilisateur connecté
//        $sorties = $sortieRepository->findBySite($participant->getSite());


        // if result != null:
        // $this->$sorties  = $sortieRepository->findByTri();
        // else:
//        $this->$sorties = $sortieRepository->findAll();


        return $this->render('accueil/edit.html.twig', [
            'sorties' => $sorties,
            'today' => $today,
            'tri_form' => $triForm->createView(),
        ]);
    }
}
