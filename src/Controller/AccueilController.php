<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\TriSortiesType;
use App\Repository\EtatRepository;
use App\Repository\SiteRepository;
use App\Repository\SortieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AccueilController extends AbstractController
{
    #[Route('/', name: 'app_accueil')]
    public function index(Request $request, SortieRepository $sortieRepository, EtatRepository $etatRepository): Response
    {
        $participant = $this->getUser();
        $etatOuvert = $etatRepository->findOneByLibelle('OUVERTE');
        $today = new \DateTime();
        $triForm = $this->createForm(TriSortiesType::class);
        $triForm->handleRequest($request);

        // si un filtre de tri est soumis, sorties est rempli via le tri
        if ($triForm->isSubmitted() && $triForm->isValid()) {
            $sorties = $sortieRepository->findByTriCustomUtilisateur($triForm, $participant);

            $this->addFlash('success', 'Tri activé');
            return $this->render('accueil/index.html.twig', [
                'sorties' => $sorties,
                'today' => $today,
                'participant' => $participant,
                'tri_form' => $triForm->createView(),
            ]);
        }

        // par défaut les sorties sont filtrées sur le site de l'utilisateur connecté, si ouvertes à l'inscription
        // et par triées date de début la plus proche.
        // si pas d'utilisateur: toutes les sorties ouvertes, triées par date la plus proche
        if($participant != null){
            $participantSite = $participant->getSite();
            if($participantSite != null){
                $sorties = $sortieRepository->findBySite($participantSite, $etatOuvert);
            }
        }
        else{
            return $this->redirectToRoute('app_login');
        }

        return $this->render('accueil/index.html.twig', [
            'sorties' => $sorties,
            'today' => $today,
            'participant' => $participant,
            'tri_form' => $triForm->createView(),
        ]);
    }
}
