<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Participant;
use App\Entity\Sortie;
use App\Form\SortieType;
use App\Repository\EtatRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/sortie', name: 'app_sortie')]
final class SortieController extends AbstractController
{
    #[Route('/creer', name: '_creer')]
    public function creer(Request $request, EntityManagerInterface $em, EtatRepository $etatRepository): Response
    {
        $sortie = new Sortie();
        $etatCree = $etatRepository->findOneByLibelle('CREEE');
        $etatOuverte = $etatRepository->findOneByLibelle('OUVERTE');

        $participant = $this->getUser();

        if (!$participant) {
            throw $this->createAccessDeniedException();
        }

        $site = $participant->getSite();
        $sortie->setSiteOrganisateur($site);
        $sortieForm = $this->createForm(SortieType::class, $sortie);
        $sortieForm->handleRequest($request);

        if ($sortieForm->isSubmitted() && $sortieForm->isValid()) {
            $sortie->setOrganisateur($participant);
            $isPubliee = $sortieForm->get('publier')->getData();
            $sortie->setEtat($isPubliee ? $etatOuverte : $etatCree);

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
    public function detail(Sortie $sortie): Response
    {
        $participants = $sortie->getParticipants();

        return $this->render('sortie/detail.html.twig', [
            'sortie' => $sortie,
            'participants' => $participants,
        ]);
    }

    #[Route('/inscription/{id}', name: '_inscription', requirements: ['id' => '\d+'])]
    public function inscription(Sortie $sortie, EntityManagerInterface $em): Response
    {
        if (!$sortie->isOuverte()) {
            $this->addFlash('danger', 'Impossible de s\'inscrire à cette sortie.');
            return $this->redirectToRoute('app_sortie_detail', [
                'id' => $sortie->getId()
            ]);
        }

        $participant = $this->getUser();

        if (!$participant) {
            throw $this->createAccessDeniedException();
        }

        if (!($sortie->getParticipants()->contains($participant))) {

            if ($sortie->isComplete()) {
                $this->addFlash('danger', 'Inscription impossible : le nombre maximum de participants est atteint.');
                return $this->redirectToRoute('app_sortie_detail', [
                    'id' => $sortie->getId()
                ]);
            }

            $sortie->addParticipant($participant);
            $em->flush();
            $this->addFlash('success', 'Inscription réussie.');
        }

        return $this->redirectToRoute('app_sortie_detail', [
            'id' => $sortie->getId()
        ]);
    }

    #[Route('/desinscription/{id}', name: '_desinscription', requirements: ['id' => '\d+'])]
    public function desinscription(Sortie $sortie, EntityManagerInterface $em): Response
    {
        $participant = $this->getUser();

        if (!$participant) {
            throw $this->createAccessDeniedException();
        }

        if ($sortie->getParticipants()->contains($participant)) {
            $sortie->removeParticipant($participant);
            $em->flush();
            $this->addFlash('success', 'Votre inscription à cette sortie a été annulée.');
        }

        return $this->redirectToRoute('app_accueil');
    }

    #[Route('/edit/{id}', name: '_edit', requirements: ['id' => '\d+'])]
    public function edit(Request $request, EntityManagerInterface $em, Sortie $sortie): Response
    {
        if ($sortie->isEnCours()) {
            $this->addFlash('danger', 'Impossible de modifier une sortie en cours.');
            return $this->redirectToRoute('app_sortie_detail', [
                'id' => $sortie->getId()
            ]);
        }

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
        dump($sortie->isEnCours());

        if ($sortie->isEnCours()) {
            $this->addFlash('danger', 'Impossible d\'annuler une sortie en cours.');
            return $this->redirectToRoute('app_sortie_detail', [
                'id' => $sortie->getId()
            ]);
        }

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
