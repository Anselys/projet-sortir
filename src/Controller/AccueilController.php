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
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
final class AccueilController extends AbstractController
{
    #[Route('/', name: 'app_accueil')]
    public function index(Request $request, SortieRepository $sortieRepository, EtatRepository $etatRepository): Response
    {

        $participant = $this->getUser();
        $etats = $etatRepository->findAll();
        $today = new \DateTime();
        $sorties = [];
        $triForm = $this->createForm(TriSortiesType::class);
        $triForm->handleRequest($request);

        // si un filtre de tri est soumis, sorties est rempli via le tri
        if ($triForm->isSubmitted() && $triForm->isValid()) {
            $sorties = $sortieRepository->findByTriCustomUtilisateur($triForm, $participant, $etats);

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
                foreach ($etats as $etat){
                    if($etat->getLibelle() == 'OUVERTE'){
                        $etatOuvert = $etat;
                    }
                }
                $sorties = $sortieRepository->findBySiteAndEtat($participantSite, $etatOuvert);
            }
        }

        return $this->render('accueil/index.html.twig', [
            'sorties' => $sorties,
            'today' => $today,
            'participant' => $participant,
            'tri_form' => $triForm->createView(),
        ]);
    }

    #[Route('/reset', name: 'app_tri_reset')]
    public function reset_tri(): Response{
        return $this->redirectToRoute('app_accueil');

    }
}
