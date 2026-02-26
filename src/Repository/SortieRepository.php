<?php

namespace App\Repository;

use App\Entity\Etat;
use App\Entity\Participant;
use App\Entity\Site;
use App\Entity\Sortie;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
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

    /**
     * @return Sortie[] Returns an array of Sortie objects
     */
    public function findBySiteAndEtat(Site $site, Etat $etat): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.siteOrganisateur = :site')
            ->andWhere('s.etat = :etat')
            ->setParameter('etat', $etat)
            ->setParameter('site', $site)
            ->orderBy('s.dateDebut', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findAllByEtat(Etat $etat): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.etat = :etat')
            ->setParameter('etat', $etat)
            ->orderBy('s.dateDebut', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function archiverSorties(): array
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
                }
            }
        }
        return $sortiesArchiveesToday;
    }


    public function findByTriCustomUtilisateur(FormInterface $triForm, UserInterface $participant, array $etats): array
    {

        $tri = $triForm->getData();

        $qb = $this->createQueryBuilder('s');

        if ($tri == null) {
            // return que les états "OUVERTE"
            foreach ($etats as $etat) {
                if ($etat->getLibelle() == 'OUVERTE') {
                    return $this->findAllByEtat($etat);
                }
            }
        }

        if ($tri['Site'] != null) {
            $qb->andWhere('s.siteOrganisateur = :orgaSite')
                ->setParameter('orgaSite', $tri['Site']);
        }

        if ($tri['etat'] != null) {
            $qb->andWhere('s.etat = :etat')
                ->setParameter('etat', $tri['etat']);
        }

        if ($tri['recherche'] != null) {
            $qb->andWhere('s.nom LIKE :recherche')
                ->setParameter('recherche', '%' . $tri['recherche'] . '%');
        }

        if ($tri['dateDebut'] != null) {
            $qb->andWhere('s.dateDebut >= :dateDebut')
                ->setParameter('dateDebut', $tri['dateDebut']);
        }

        if ($tri['dateCloture'] != null) {
            $qb->andWhere('s.dateCloture <= :dateCloture')
                ->setParameter('dateCloture', $tri['dateCloture']);
        }

        if ($tri['organisateur'] != 0) {
            $qb->andWhere('s.organisateur = :organisateur');
            $qb->setParameter('organisateur', $participant);
        }

        if ($tri['inscrit'] != 0) {
            // if connected participant is in sortie.participants > add to filter
            // TODO: chercher comment faire les join. ça ne marche pas
//            $qb->addSelect('s.participants')
//                ->innerJoin('participant', 'participant')
//                ->andWhere('participant.id = :id')
//                ->setParameter('id', $participant->getId());

        }

        if ($tri['non_inscrit'] != 0) {
            // if connected participant is not in sortie.participants > add to filter
            // TODO: chercher comment faire les join. ça ne marche pas
//            $qb->addSelect('s.participants')
//                ->innerJoin('participant', 'participant')
//                ->andWhere('participant.id != :id')
//                ->setParameter('id', $participant->getId());
        }

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

        return $qb->getQuery()->getResult();
    }

}
