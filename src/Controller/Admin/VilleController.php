<?php

namespace App\Controller\Admin;

use App\Entity\Ville;
use App\Form\SearchType;
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

        // si une ville a été ajoutée: passer par ici
        if ($villesForm->isSubmitted() && $villesForm->isValid()) {
            $ville = $villesForm->getData();
            $villeExists = $villeRepository->findOneByNomAndCPO($ville->getNom(), $ville->getCpo());
            if (!$villeExists) {
                $em->persist($ville);
                $em->flush();
                $this->addFlash('success', 'La ville a été ajoutée avec succès');
            } else {
                $this->addFlash('warning', 'Cette ville existe déjà');
            }
        }
        $searchForm = $this->createForm(SearchType::class);
        $searchForm->handleRequest($request);
        // si une recherche est effectuée, passer par ici
        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $search = $searchForm->getData();
            if ($search['nom']) {
                $villes = $villeRepository->searchByName($search['nom']);
            } else {
                $villes = $villeRepository->findAll();
            }
        } // si ni recherche ni ajout, afficher toutes les villes.
        else {
            $villes = $villeRepository->findAll();
        }

        return $this->render('admin/ville.html.twig', [
            'villes' => $villes,
            'search_form' => $searchForm->createView(),
            'villes_form' => $villesForm->createView(),
        ]);
    }

    #[Route('/ville/update/{id}', name: '_ville_update', requirements: ['id' => '\d+'])]
    public function update(Ville $ville, EntityManagerInterface $em, Request $request): Response
    {
            $villeUpdateForm = $this->createForm(VilleType::class);
            $villeUpdateForm->handleRequest($request);
            if($villeUpdateForm->isSubmitted() && $villeUpdateForm->isValid()) {
                // récupèrer les nouvelles données ville
                $ville = $villeUpdateForm->getData();
                $villeFound = $em->getRepository(Ville::class)->findOneByNomAndCPO($ville->getNom(), $ville->getCpo());
                // checker si une ville sous ce combo nom/cpo existe déjà.
                if (!$villeFound) {
                    $em->flush();
                    $this->addFlash('success', 'La ville a été ajoutée avec succès');
                    return $this->redirectToRoute('app_admin_ville');
                }
                $this->addFlash('error', "La Ville n'a pas pu être mise à jour, il y en a déjà une de ce nom dans la base de données.");
                return $this->redirectToRoute('app_admin_ville');
            }
        return $this->render('admin/ville-edit.html.twig', [
            'ville_update_form' => $villeUpdateForm->createView(),
            'ville' => $ville,
        ]);
    }


    #[Route('/ville/delete/{id}', name: '_ville_delete', requirements: ['id' => '\d+'])]
    public function delete(Ville $ville, EntityManagerInterface $em, Request $request): Response
    {
        $token = $request->query->get('token');
        if ($this->isCsrfTokenValid('ville_delete' . $ville->getId(), $token)) {
            $em->remove($ville);
            $em->flush();
            $this->addFlash('success', 'La ville a été supprimée');
            return $this->redirectToRoute('app_admin_ville');
        }

        $this->addFlash('danger', 'Impossible de supprimer cette ville.');
        return $this->redirectToRoute('app_admin_ville', ['id' => $ville->getId()]);

    }

}
