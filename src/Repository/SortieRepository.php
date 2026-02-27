<?php

namespace App\Repository;

use App\Entity\Etat;
use App\Entity\Participant;
use App\Entity\Site;
use App\Entity\Sortie;
use App\Entity\Ville;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends ServiceEntityRepository<Sortie>
 */
class SortieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sortie::class);
    }

    ////
    // FONCTIONS DE TRI
    ////


    public function customFindAccueil($participant, array $etats): array
    {
        $etatOuverte = '';
        foreach ($etats as $etat) {
            if ($etat->getLibelle() == 'OUVERTE') {
                $etatOuverte = $etat;
            }
            if ($etat->getLibelle() == 'CREEE') {
                $etatCreee = $etat;
            }
        }
        $sorties = $this->createQueryBuilder('s')
            // prendre que les sorties ouvertes
            ->andWhere('s.etat = :etatOuverte')
            ->setParameter('etatOuverte', $etatOuverte)
            // du meme site que l'utilisateur connecté
            ->andWhere('s.siteOrganisateur = :site')
            ->setParameter('site', $participant->getSite())
            // sorties qui ne sont PAS archivées
            ->andWhere('s.isArchivee != :archive')
            ->setParameter('archive', true)
            ->orderBy('s.dateDebut', "ASC")
            ->getQuery()
            ->getResult();

        return $sorties;
    }


    public function findAllByEtat(Etat $etat, $participant): array
    {
        $qb = $this->createQueryBuilder('s')
            ->andWhere('s.isArchivee != :archive')
            ->setParameter('archive', true)
            ->orderBy('s.dateDebut', 'ASC');

        if ($etat->getLibelle() != 'CREEE') {
            $qb->andWhere('s.etat = :etat')
                ->setParameter('etat', $etat);
        } else {
            $qb->andWhere('s.etat = :etat')
                ->setParameter('etat', $etat)
                ->andWhere('s.organisateur = :organisateur')
                ->setParameter('organisateur', $participant);
        }
        return $qb->getQuery()->getResult();
    }


    public function findByTriCustomUtilisateur(FormInterface $triForm, UserInterface $participant, array $etats): array
    {
        $tri = $triForm->getData();
        $qb = $this->createQueryBuilder('s');


        // si un site est renseigné, trier sur le site. sinon afficher tout
        if ($tri['Site'] != null) {
            $qb->andWhere('s.siteOrganisateur = :orgaSite')
                ->setParameter('orgaSite', $tri['Site']);
        }

        // si un etat est renseigné, trier sur le site. sinon afficher tout (sauf CREEE)
        // TODO: pourquoi ça marche pas?????
//        if ($tri['etat'] == null){
//            $etatCreee = null;
//            foreach ($etats as $etat) {
//                if ($etat->getLibelle() == 'CREEE') {
//                    $etatCreee = $etat;
//                    break;
//                }
//            }
//            $qb->andWhere('s.etat != :etatNotCreee')
//                ->setParameter('etatNotCreee', $etatCreee);
//        }

        if ($tri['etat'] != null) {
            if ($tri['etat']->getLibelle() != 'CREEE') {
                $qb->andWhere('s.etat = :etatNotCreee')
                    ->setParameter('etatNotCreee', $tri['etat']);
            } else {
                $qb->andWhere('s.etat = :etatCreee')
                    ->setParameter('etatCreee', $tri['etat'])
                    ->andWhere('s.organisateur = :organisateur')
                    ->setParameter('organisateur', $participant);
            }
        }

        // si le champ de recherche n'est pas vide, rechercher le texte dans les titres
        if ($tri['recherche'] != null) {
            $qb->andWhere('s.nom LIKE :recherche')
                ->setParameter('recherche', '%' . $tri['recherche'] . '%');
        }

        // s'il y a une date de début de renseignée, trier dessus
        if ($tri['dateDebut'] != null) {
            $qb->andWhere('s.dateDebut >= :dateDebut')
                ->setParameter('dateDebut', $tri['dateDebut']);
        }

        // s'il y a une date de cloture de renseignée, trier dessus
        if ($tri['dateCloture'] != null) {
            $qb->andWhere('s.dateCloture <= :dateCloture')
                ->setParameter('dateCloture', $tri['dateCloture']);
        }

        // si l'utilisateur connecté est l'organisateur, afficher ses sorties, sinon afficher tout
        if ($tri['organisateur'] != 0) {
            $qb->andWhere('s.organisateur = :organisateur');
            $qb->setParameter('organisateur', $participant);
        }

        // si l'utilisateur connecté est inscrit, afficher ses sorties, sinon afficher tout
        if ($tri['inscrit'] != 0) {
            $qb->where(':participantInscrit MEMBER OF s.participants')
                ->setParameter('participantInscrit', $participant);
        }

        // si l'utilisateur connecté n'est PAS inscrit, afficher ses sorties, sinon afficher tout
        if ($tri['non_inscrit'] != 0) {
            $qb->where(':participantNotInscrit NOT MEMBER OF s.participants')
                ->setParameter('participantNotInscrit', $participant);
        }

        // afficher les sorties déjà passées
        if ($tri['passees'] != 0) {
            foreach ($etats as $etat) {
                if ($etat->getLibelle() == 'PASSEE') {
                    $etatPasse = $etat;
                    break;
                }
            }
            $qb->andWhere('s.etat = :etat');
            $qb->setParameter('etat', $etatPasse);
        }

        // par défaut, afficher les sorties NON archivées, et toujours les trier par date de début la plus proche en premier
        $qb->andWhere('s.isArchivee != :archive')
            ->setParameter('archive', true);
        return $qb->getQuery()->getResult();
    }


    public function findAll(): array
    {
        $qb = $this->createQueryBuilder('s')
            ->andWhere('s.isArchivee != :archive')
            ->setParameter('archive', true)
            ->orderBy('s.dateDebut', "ASC");

        return $qb->getQuery()->getResult();
    }


    public function getSortiesArchivees(): array
    {
        $qb = $this->createQueryBuilder('s')
            ->andWhere('s.isArchivee = :archive')
            ->setParameter('archive', true);
        return $qb->getQuery()->getResult();
    }

    public function getSortiesByVille(Ville $ville): array
    {
        $qb = $this->createQueryBuilder('s')
            ->andWhere('s.isArchivee != :archive')
            ->setParameter('archive', true)
            ->andWhere('s.ville = :ville')
            ->setParameter('ville', $ville);
        return $qb->getQuery()->getResult();
    }

    ////
    // ARCHIVAGE
    ////

    public function archiverSorties(EntityManagerInterface $em): array
    {
        $now = new DateTime();
        $sorties = $this->findAll();
        $sortiesArchiveesToday = [];
        foreach ($sorties as $sortie) {
            if (!$sortie->isArchivee()) {
                $result = $sortie->getDateDebut()->diff($now);
                if ($result->days > 30 && $result->invert != 1) {
                    $sortie->setIsArchivee(true);
                    $sortiesArchiveesToday[] = $sortie;
                    echo 'SORTIE ARCHIVEE: ' . $sortie->getNom() . " - ID: " . $sortie->getId() . "\n";
                }
            }
        }
        $em->flush();

        return $sortiesArchiveesToday;
    }

}
