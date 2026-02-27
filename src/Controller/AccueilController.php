<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ShowAllType;
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
        $triForm = $this->createForm(TriSortiesType::class);
        $afficherToutForm = $this->createForm(ShowAllType::class);

        $participant = $this->getUser();
        $etats = $etatRepository->findAll();
        $sorties = [];

        $triForm->handleRequest($request);
        // si un filtre de tri est soumis, sorties est rempli via le tri
        if ($triForm->isSubmitted() && $triForm->isValid()) {

            $sorties = $sortieRepository->findByTriCustomUtilisateur($triForm, $participant, $etats);


            return $this->render('accueil/index.html.twig', [
                'sorties' => $sorties,
                'participant' => $participant,
                'tri_form' => $triForm->createView(),
                'afficher_tout_form' => $afficherToutForm->createView(),
            ]);
        }


        $afficherToutForm->handleRequest($request);
        // si on clique sur 'afficher tout' ça défiltre tout
        if ($afficherToutForm->isSubmitted() && $afficherToutForm->isValid()) {
            $sorties = $sortieRepository->findAll();
            return $this->render('accueil/index.html.twig', [
                'sorties' => $sorties,
                'participant' => $participant,
                'tri_form' => $triForm->createView(),
                'afficher_tout_form' => $afficherToutForm->createView(),
            ]);
        }
        // par défaut les sorties sont filtrées sur le site de l'utilisateur connecté, si ouvertes à l'inscription
        // et par triées date de début la plus proche.
        // si pas d'utilisateur: toutes les sorties ouvertes, triées par date la plus proche
        if ($participant != null) {
//                $sorties = $sortieRepository->findBySiteAndEtat($participantSite, $etatOuvert);
                $sorties = $sortieRepository->customFindAccueil($participant, $etats);
            }



        return $this->render('accueil/index.html.twig', [
            'sorties' => $sorties,
            'participant' => $participant,
            'tri_form' => $triForm->createView(),
            'afficher_tout_form' => $afficherToutForm->createView(),
        ]);
    }

}
