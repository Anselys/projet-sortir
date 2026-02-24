<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\InscriptionType;
use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\MakerBundle\FileManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route('/profil', name: 'app_profil')]
final class ProfilController extends AbstractController
{
    #[Route('/profil', name: 'app_profil')]
    public function index(): Response
    {
        return $this->render('profil/profil.html.twig', [
            'controller_name' => 'ProfilController',
        ]);
    }

    #[Route('/{id}', name: '_profil', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function detail(Participant $participant): Response
    {
        return $this->render('profil/profil.html.twig', [
            'participant' => $participant,
        ]);
    }

    // TODO
//    #[Route('/update/{id}', name: '_update', requirements: ['id' => '\d+'])]
//    public function update(Request $request, EntityManagerInterface $em, Participant $participant): Response
//    {
//        $participantForm = $this->createForm(InscriptionType::class, $participant);
//        $participantForm->handleRequest($request);
//        if ($participantForm->isSubmitted() && $participantForm->isValid()) {
//                       $em->flush();
//            $this->addFlash('success', "Le profil de l'utilisateur {$participant->getPseudo()} a été modifié");
//            return $this->redirectToRoute('app_profil', ['id' => $participant->getId()]);
//        }
//
//        return $this->render('profil/profil.html.twig', [
//            'participant_form' => $participantForm,
//            'participant' => $participant,
//        ]);
//    }

}
