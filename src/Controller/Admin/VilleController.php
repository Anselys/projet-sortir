<?php

namespace App\Controller\Admin;

use App\Entity\Ville;
use App\Form\VilleType;
use App\Repository\VilleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin', name: 'app_admin')]
final class VilleController extends AbstractController
{
    #[Route('/ville', name: '_ville')]
    public function index(Request $request, VilleRepository $villeRepository, EntityManagerInterface $em): Response
    {
        $villesForm = $this->createForm(VilleType::class);
        $villesForm->handleRequest($request);
        // si un filtre de tri est soumis, sorties est rempli via le tri
        if ($villesForm->isSubmitted() && $villesForm->isValid())
        {
            $ville = $villesForm->getData();
            $villeExists = $villeRepository->findOne($ville);
            if(!$villeExists)
            {
                $em->persist($ville);
                $em->flush();
                $this->addFlash('success', 'La ville a été ajoutée avec succès');
            }
            else
            {
                $this->addFlash('warning', 'Cette ville existe déjà');
            }
        }
        $villes = $villeRepository->findAll();

        return $this->render('admin/ville.html.twig', [
            'villes' => $villes,
            'villes_form' => $villesForm->createView(),
        ]);
    }
}
