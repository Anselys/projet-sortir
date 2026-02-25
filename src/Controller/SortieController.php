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
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
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
    #[Route('/inscription/{id}', name: '_inscription')]
    public function inscription(Request $request, EntityManagerInterface $em): Response
    {
        return $this->render('accueil/index.html.twig');
    }


    #[Route('/edit/{id}', name: '_edit', requirements: ['id' => '\d+'])]
    public function update(Request $request, EntityManagerInterface $em, Sortie $sortie): Response
    {
        $sortieForm = $this->createForm(SortieType::class, $sortie);
        $sortieForm->handleRequest($request);
        if ($sortieForm->isSubmitted() && $sortieForm->isValid()) {

            $em->flush();
            $this->addFlash('success', "La sortie a été modifiée");
            return $this->redirectToRoute('app_sortie_detail', ['id' => $sortie->getId()]);
        }

        return $this->render('sortie/edit.html.twig', [
            'sortie_form' => $sortieForm,
            'sortie' => $sortie,
        ]);
    }

    #[Route('/delete/{id}', name: '_delete', requirements: ['id' => '\d+'])]
    public function delete(Sortie $sortie, EntityManagerInterface $em, Request $request): Response
    {
        $token = $request->query->get('token');

        if ($this->isCsrfTokenValid('sortie_delete' . $sortie->getId(), $token)) {
            $em->remove($sortie);
            $em->flush();

            $this->addFlash('success', 'La sortie a été supprimée.');
            return $this->redirectToRoute('app_accueil');
        }

        $this->addFlash('danger', 'Impossible de supprimer cette sortie.');
        return $this->redirectToRoute('app_sortie_detail', ['id' => $sortie->getId()]);
    }

}
