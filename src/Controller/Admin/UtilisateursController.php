<?php

namespace App\Controller\Admin;

use App\Entity\Participant;
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
final class UtilisateursController extends AbstractController
{
    #[Route('/utilisateurs', name: '_utilisateurs')]
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

        return $this->render('admin/utilisateurs.html.twig', [
            'search_form' => $searchForm->createView(),
            'utilisateurs' => $utilisateurs,
        ]);
    }

    #[Route('/rendre-actif/{id}', name: '_rendre_actif', requirements: ['id' => '\d+'])]
    public function rendreActif(Participant $participant, EntityManagerInterface $em): Response
    {
        $utilisateurConnecte = $this->getUser();

        if (!$utilisateurConnecte) {
            throw $this->createAccessDeniedException();
        }

        $participant->setIsActif(true);
        $em->persist($participant);
        $em->flush();

        $message = sprintf("%s %s est désormais actif", $participant->getPrenom(), $participant->getNom());

        $this->addFlash('success', $message);

        return $this->redirectToRoute('app_admin_utilisateurs');

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

        return $this->redirectToRoute('app_admin_utilisateurs');

    }

}
