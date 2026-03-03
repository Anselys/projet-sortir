<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Entity\Ville;
use App\Form\LieuType;
use App\Form\VilleType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/lieu', name: 'app_lieu')]
final class LieuController extends AbstractController
{
    #[IsGranted('ROLE_USER')]
    #[Route('/creer', name: '_creer')]
    public function lieu(Request $request, EntityManagerInterface $em): Response
    {
        $lieu = new Lieu();
        $lieuForm = $this->createForm(LieuType::class, $lieu);
        $lieuForm->handleRequest($request);

        if ($lieuForm->isSubmitted() && $lieuForm->isValid()) {

            $existingLieu = $em->getRepository(Lieu::class)->findOneBy([
                'nom' => $lieu->getNom(),
                'rue' => $lieu->getRue(),
                'ville' => $lieu->getVille(),
            ]);

            if ($existingLieu) {
                $this->addFlash('danger', 'Ce lieu existe déjà !');
            } else {
                $em->persist($lieu);
                $em->flush();
                $this->addFlash('success', 'Un nouveau lieu a été ajouté.');
            }
        }

        $lieux = $em->getRepository(Lieu::class)->findAll();

        return $this->render('lieu/lieu.html.twig', [
            'lieux' => $lieux,
            'lieux_form' => $lieuForm,
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/update/{id}', name: '_modifier', requirements: ['id' => '\d+'])]
    public function modifier(Lieu $lieu, EntityManagerInterface $em, Request $request): Response
    {

        // Si la sortie est en cours ou passée/annulée/archivée, la sortie ne doit pas pouvoir être modifiée
        if (!$lieu->isModifiable()) {
            $this->addFlash('danger', 'Impossible de modifier ce lieu, des sorties y sont liées.');
            return $this->redirectToRoute('app_lieu_creer', [
                'id' => $lieu->getId()
            ]);
        }

        $lieuUpdateForm = $this->createForm(LieuType::class);
        $lieuUpdateForm->handleRequest($request);
        if ($lieuUpdateForm->isSubmitted() && $lieuUpdateForm->isValid()) {
            // récupèrer les nouvelles données lieu
            $newLieu = $lieuUpdateForm->getData();
            $lieu->setNom($newLieu->getNom());
            $lieu->setRue($newLieu->getRue());
            $lieu->setVille($newLieu->getVille());
            $lieu->setLatitude($newLieu->getLatitude());
            $lieu->setLongitude($newLieu->getLongitude());
            $existingLieu = $em->getRepository(Lieu::class)->findOneBy([
                'nom' => $lieu->getNom(),
                'rue' => $lieu->getRue(),
                'ville' => $lieu->getVille(),
            ]);
            if (!$existingLieu) {
                $em->flush();
                $this->addFlash('success', 'Le lieu a été modifié avec succès.');
                return $this->redirectToRoute('app_lieu_creer');
            }
        }
        return $this->render('lieu/edit.html.twig', [
            'lieu_update_form' => $lieuUpdateForm->createView(),
            'lieu' => $lieu,
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/delete/{id}', name: '_supprimer', requirements: ['id' => '\d+'])]
    public function supprimer(Lieu $lieu, EntityManagerInterface $em, Request $request): Response
    {
        $token = $request->query->get('token');
        if ($this->isCsrfTokenValid('lieu_delete' . $lieu->getId(), $token)) {

            if($lieu->isModifiable()){
                $em->remove($lieu);
                $em->flush();
                $this->addFlash('success', 'Le lieu a été supprimé');
                return $this->redirectToRoute('app_lieu_creer');
            }
        }

        $this->addFlash('danger', 'Impossible de supprimer ce lieu : des sorties y sont associées.');
        return $this->redirectToRoute('app_lieu_creer');

    }
}
