<?php

namespace App\Controller\Admin;

use App\Entity\Site;
use App\Form\SearchType;
use App\Form\SiteType;
use App\Repository\SiteRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin', name: 'app_admin')]
final class SiteController extends AbstractController
{
    #[Route('/site', name: '_site')]
    public function index(Request $request, SiteRepository $siteRepository, EntityManagerInterface $em): Response
    {
        $siteForm = $this->createForm(SiteType::class);
        $siteForm->handleRequest($request);

        // si un site a été ajouté: passer par ici
        if ($siteForm->isSubmitted() && $siteForm->isValid()) {
            $site = $siteForm->getData();
            $siteExists = $siteRepository->findOneByNom($site->getNom());
            if (!$siteExists) {
                $em->persist($site);
                $em->flush();
                $this->addFlash('success', 'Le site a été ajoutée avec succès');
            } else {
                $this->addFlash('warning', 'Ce site existe déjà');
            }
        }
        $searchForm = $this->createForm(SearchType::class);
        $searchForm->handleRequest($request);
        // si une recherche est effectuée, passer par ici
        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $search = $searchForm->getData();
            if ($search['nom']) {
                $sites = $siteRepository->searchByName($search['nom']);
            } else {
                $sites = $siteRepository->findAll();
            }
        } // si ni recherche ni ajout, afficher tous les sites.
        else {
            $sites = $siteRepository->findAll();
        }

        return $this->render('admin/site.html.twig', [
            'sites' => $sites,
            'search_form' => $searchForm->createView(),
            'site_form' => $siteForm->createView(),
        ]);
    }


    #[Route('/site/update/{id}', name: '_site_update', requirements: ['id' => '\d+'])]
    public function update(Site $site, EntityManagerInterface $em, Request $request): Response
    {
        $siteUpdateForm = $this->createForm(SiteType::class);
        $siteUpdateForm->handleRequest($request);
        if ($siteUpdateForm->isSubmitted() && $siteUpdateForm->isValid()) {

            // récupèrer les nouvelles données ville
            $siteName = $siteUpdateForm->getData()->getNom();
            $site->setNom($siteName);

            // checker si un site de ce nom existe déjà.
            $siteFound = $em->getRepository(Site::class)->findOneByNom($site->getNom());
            if (!$siteFound) {
                $em->flush();
                $this->addFlash('success', 'Le site a été modifié avec succès');
                return $this->redirectToRoute('app_admin_site');
            }
            $this->addFlash('danger', "Il y a déjà un site de ce nom dans la base de données.");
            return $this->redirectToRoute('app_admin_site');
        }
        return $this->render('admin/site-edit.html.twig', [
            'site_update_form' => $siteUpdateForm->createView(),
            'site' => $site,
        ]);
    }

    #[Route('/site/delete/{id}', name: '_site_delete', requirements: ['id' => '\d+'])]
    public function delete(Site $site, EntityManagerInterface $em, Request $request): Response
    {
        $token = $request->query->get('token');
        if ($this->isCsrfTokenValid('site_delete' . $site->getId(), $token)) {
            $em->remove($site);
            $em->flush();
            $this->addFlash('success', 'Le site a été supprimé');
            return $this->redirectToRoute('app_admin_site');
        }

        $this->addFlash('danger', 'Impossible de supprimer ce site.');
        return $this->redirectToRoute('app_admin_site', ['id' => $site->getId()]);
    }

}
