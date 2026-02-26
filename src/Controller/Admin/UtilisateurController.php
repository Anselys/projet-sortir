<?php

namespace App\Controller\Admin;

use App\Entity\Participant;
use App\Entity\Site;
use App\Entity\Sortie;
use App\Form\SearchType;
use App\Repository\ParticipantRepository;
use App\Repository\SiteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin', name: 'app_admin')]
final class UtilisateurController extends AbstractController
{
    #[Route('/utilisateur', name: '_utilisateur')]
    public function index(Request $request, ParticipantRepository $participantRepository, EntityManagerInterface $em): Response
    {
        $searchForm = $this->createForm(SearchType::class);
        $searchForm->handleRequest($request);

        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $search = $searchForm->getData();
            if ($search['nom']) {
                $utilisateurs = $participantRepository->searchByName($search['nom']);
            } else {
                $utilisateurs = $participantRepository->findAll();
            }
        }
        else {
            $utilisateurs = $participantRepository->findAll();
        }

        return $this->render('admin/utilisateur.html.twig', [
            'search_form' => $searchForm->createView(),
            'utilisateurs' => $utilisateurs,
        ]);
    }

    #[Route('/changer-statut-utilisateur/{id}', name: '_changer_statut_utilisateur', requirements: ['id' => '\d+'])]
    public function changerStatutUtilisateur(Participant $participant, EntityManagerInterface $em): Response
    {
        $utilisateurConnecte = $this->getUser();

        if (!$utilisateurConnecte) {
            throw $this->createAccessDeniedException();
        }

        $participant->setIsActif(!$participant->isActif());
        $em->persist($participant);
        $em->flush();

        $statutString = $participant->isActif() ? 'actif' : 'inactif';
        $message = sprintf("%s %s est désormais %s", $participant->getPrenom(), $participant->getNom(), $statutString);

        $this->addFlash('success', $message);

        return $this->redirectToRoute('app_admin_utilisateur');
    }

    #[Route('/utilisateur/delete/{id}', name: '_utilisateur_delete', requirements: ['id' => '\d+'])]
    public function delete(Participant $utilisateur, EntityManagerInterface $em, Request $request): Response
    {
        $token = $request->query->get('token');
        if ($this->isCsrfTokenValid('utilisateur_delete' . $utilisateur->getId(), $token)) {
            $em->remove($utilisateur);
            $em->flush();

            $this->addFlash('success', 'L\'utilisateur a été supprimé');

            return $this->redirectToRoute('app_admin_utilisateur');
        }

        $this->addFlash('danger', 'Impossible de supprimer cet utilisateur.');
        return $this->redirectToRoute('app_admin_utilisateur', ['id' => $utilisateur->getId()]);
    }

}
