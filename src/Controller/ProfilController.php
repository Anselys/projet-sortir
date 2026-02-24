<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ProfilType;
use App\Helper\FileManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
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

            return $this->redirectToRoute('app_profil_detail');
        }

        return $this->render('profil/edit.html.twig', [
            'profil_form' => $profilForm,
        ]);
    }

}
