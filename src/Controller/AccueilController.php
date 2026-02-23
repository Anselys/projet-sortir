<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\TriSortiesType;
use App\Repository\SiteRepository;
use App\Repository\SortieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AccueilController extends AbstractController
{
    #[Route('/', name: 'app_accueil')]
    public function index(SortieRepository $sortieRepository, SiteRepository $siteRepository): Response
    {
        $today = new \DateTime();
        $triForm = $this->createForm(TriSortiesType::class);
        // TODO: get connected user here
//        $participant = new Participant();
//        $participant->setSite($siteRepository->findOneByNom('NANTES'));
//
//        // par défaut les sorties sont filtrées sur le site de l'utilisateur connecté
//        $sorties = $sortieRepository->findBySite($participant->getSite());

            $sorties = $sortieRepository->findAll();


        return $this->render('accueil/edit.html.twig', [
            'sorties' => $sorties,
            'today' => $today,
            'tri_form' => $triForm->createView(),
        ]);
    }
}
