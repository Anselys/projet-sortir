<?php

namespace App\Repository;

use App\Entity\Site;
use App\Entity\Sortie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

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
        public function findBySite(Site $site): array
        {
            // ajouter le tri par état plus tard???
            // TODO: fixme
            return $this->createQueryBuilder('s')
                ->andWhere('s.site_organisateur_id = :siteId')
                ->setParameter('siteId', $site->getId())
                ->orderBy('s.dateDebut', 'ASC')
                ->getQuery()
                ->getResult()
            ;
        }

    //    public function findOneBySomeField($value): ?Sortie
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
