<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ProfilType;
use App\Form\UpdatePasswordType;
use App\Helper\FileManager;
use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/profil', name: 'app_profil')]
final class ProfilController extends AbstractController
{

//    #[Route('/', name: '_detail')]
//    public function profil(): Response
//    {
//        $participant = $this->getUser();
//
//        return $this->render('profil/profil.html.twig', [
//            'participant' => $participant,
//        ]);
//    }


    #[Route('/{id}', name: '_detail', methods: ['GET'])]
    public function profil(Participant $participant): Response
    {
        return $this->render('profil/profil.html.twig', [
            'participant' => $participant,
        ]);
    }

    #[Route('/edit/{id}', name: '_edit')]
    public function edit(Request $request, EntityManagerInterface $em, FileManager $fileManager): Response
    {
        /** @var Participant $participant */
        $participant = $this->getUser();

        $profilForm = $this->createForm(ProfilType::class, $participant);
        $profilForm->handleRequest($request);

        if ($profilForm->isSubmitted() && $profilForm->isValid()) {

            $file = $profilForm->get('urlPhoto')->getData();

            if ($file instanceof UploadedFile) {
                $url = $fileManager->upload($file, $this->getParameter('photos_directory'), $profilForm->getName());
                $participant->setUrlPhoto($url);
            }

            $em->flush();

            $this->addFlash('success', 'Votre profil a été mis à jour.');

            return $this->redirectToRoute('app_profil_detail', ['id' => $participant->getId()]);
        }

        return $this->render('profil/edit.html.twig', [
            'profil_form' => $profilForm,
        ]);
    }

    #[Route('/{id}/update-password', name: '_update_password')]
    public function updatePassword(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $participantPasswordHasher, ParticipantRepository $participantRepository): Response
    {
        /** @var Participant $participant */
        $participant = $this->getUser();

        $updatePasswordForm = $this->createForm(UpdatePasswordType::class);
        $updatePasswordForm->handleRequest($request);

        if ($updatePasswordForm->isSubmitted() && $updatePasswordForm->isValid()) {
            $oldPassword = $updatePasswordForm ->get('oldPassword')->getData();
            $newPassword = $updatePasswordForm ->get('newPassword')->getData();

            if(!$participantPasswordHasher->isPasswordValid($participant, $oldPassword)) {
                $this->addFlash('danger', 'Votre ancien mot de passe est incorrect.');

                return $this->redirectToRoute('app_profil_update_password', ['id' => $participant->getId()]);
            }

            $newHashedPassword = $participantPasswordHasher->hashPassword($participant, $newPassword);
            $participantRepository->upgradePassword($participant, $newHashedPassword);

            $this->addFlash('success', 'Votre mot de passe a été mis à jour.');

            return $this->redirectToRoute('app_profil_detail', ['id' => $participant->getId()]);
        }

        return $this->render('profil/update_password.html.twig', [
            'update_password_form' => $updatePasswordForm,
        ]);
    }

}
