<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Form\SortieType;
use App\Repository\EtatRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/sortie', name: 'app_sortie')]
final class SortieController extends AbstractController
{
    #[Route('/creer', name: '_creer')]
    public function creer(Request $request, EntityManagerInterface $em, EtatRepository $etatRepository): Response
    {
        $sortie = new Sortie();
        $etat = $etatRepository->find(1);

        $participant = $this->getUser();

        $site = $participant->getSite();
        $sortie->setSiteOrganisateur($site);
        $sortieForm = $this->createForm(SortieType::class, $sortie);
        $sortieForm->handleRequest($request);

        if ($sortieForm->isSubmitted() && $sortieForm->isValid()) {
            $sortie->setOrganisateur($participant);
            $sortie->setEtat($etat);
            $sortie->addParticipant($participant);
            $em->persist($sortie);
            $em->flush();

            $this->addFlash('success', 'Une nouvelle sortie a été créée !');
            return $this->redirectToRoute('app_accueil');
        }

        return $this->render('sortie/edit.html.twig', [
            'sortie_form' => $sortieForm,
        ]);
    }

    #[Route('/{id}', name: '_detail', methods: ['GET'])]
    public function profil(Sortie $sortie): Response
    {
        $participants = $sortie->getParticipants();

        return $this->render('sortie/detail.html.twig', [
            'sortie' => $sortie,
            'participants' => $participants,
        ]);
    }

    //Inscription à une sortie
    #[Route('/{id}/inscription', name: '_inscription')]
    public function inscription(Request $request, EntityManagerInterface $em): Response
    {
        return $this->render('accueil/index.html.twig');
    }

}
