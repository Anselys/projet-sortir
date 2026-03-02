<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Form\LieuType;
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
        $lieuForm = $this->createForm(LieuType::class);
        $lieuForm->handleRequest($request);

        if ($lieuForm->isSubmitted() && $lieuForm->isValid()) {
            $lieu = $lieuForm->getData();
            if (!$lieu) {
                $em->persist($lieu);
                $em->flush();
                $this->addFlash('success', 'Un nouveau lieu a été ajouté.');
            } else {
                $this->addFlash('danger', 'Ce lieu existe déjà !');
            }
        }

        $lieux = $em->getRepository(Lieu::class)->findAll();

        return $this->render('lieu/lieu.html.twig', [
            'lieux' => $lieux,
            'lieux_form' => $lieuForm->createView(),
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/update', name: '_modifier')]
    public function modifier(): Response
    {
        return $this->render('lieu/lieu.html.twig', [
            'controller_name' => 'LieuController',
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/delete', name: '_supprimer')]
    public function supprimer(): Response
    {
        return $this->render('lieu/lieu.html.twig', [
            'controller_name' => 'LieuController',
        ]);
    }
}
