<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\InscriptionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;


#[IsGranted('ROLE_USER')]
#[Route('/profil', name: 'app_profil')]
class ProfilController extends AbstractController
{

    #[Route('/', name: '_detail')]
    public function profil(): Response
    {
        $participant = $this->getUser();

        return $this->render('profil/profil.html.twig', [
            'participant' => $participant,
        ]);
    }

    #[Route('/{id}/edit', name: '_edit')]
    #[IsGranted('ROLE_USER')]
    public function edit(Request $request, EntityManagerInterface $em, Participant $participant): Response
    {
        if ($this->getUser() !== $participant) {
            throw $this->createAccessDeniedException();
        }

        $participantForm = $this->createForm(InscriptionType::class, $participant);
        $participantForm->handleRequest($request);
        if ($participantForm->isSubmitted() && $participantForm->isValid()) {
            $em->flush();
            $this->addFlash('success', "Le profil de l'utilisateur {$participant->getPseudo()} a été modifié");
            return $this->redirectToRoute('app_profil', ['id' => $participant->getId()]);
        }

        return $this->render('profil/profil.html.twig', [
            'participant_form' => $participantForm,
            'participant' => $participant,
        ]);
    }

}
