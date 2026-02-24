<?php

namespace App\Repository;

use App\Entity\Etat;
use App\Entity\Participant;
use App\Entity\Site;
use App\Entity\Sortie;
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
    public function findBySite(Site $site, Etat $etat): array
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

    public function findByTriCustomUtilisateur(FormInterface $triForm, UserInterface $participant): array
    {
        $tri = $triForm->getData();

        $qb = $this->createQueryBuilder('s');

        if ($tri == null) {
            // TODO: return pas tout mais que les etat : ouverte
            return $this->findAll();
        }

        if ($tri['Site'] != null) {
            $qb->andWhere('s.siteOrganisateur = :orgaSite')
                ->setParameter('orgaSite', $tri['Site']);
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
            // TODO: dépatouiller ça
//                $participant->getSortiesOrganisees()->findFirst((int)$tri['id'])->setDateFin(new \DateTime($tri['dateFin']));
//                $qb->andWhere('s.inscrit = :inscrit')
//                    ->setParameter('inscrit', );
        }

        if ($tri['non_inscrit'] != 0) {
            // TODO: dépatouiller ça
//                $qb->andWhere('s.non_inscrit = :inscrit')
//                    ->setParameter('inscrit', );
        }

        if ($tri['passees'] != 0) {
            // TODO: dépatouiller ça
            // $qb->andWhere('s.dateDebut < new /DateTime()');
        }

        return $qb->getQuery()->getResult();
    }

}
