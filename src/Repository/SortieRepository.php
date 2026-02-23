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




        public function findByTri($result): array{
            $task = $result->getData();
            $qb = $this->createQueryBuilder('s');

            if($task == null){
                return $this->findAll();
            }

            if($task['Site'] != null){
                $qb->andWhere('s.site_organisateur_Id = :siteId')
                    ->setParameter('siteId', $task['Site']->getId());
            }

            if($task['recherche'] != null){
                $qb->andWhere('s.nom LIKE :recherche')
                    ->setParameter('recherche', '%'.$task['recherche'].'%');
            }

            if($task['startDate'] != null){
                $qb->andWhere('s.dateDebut >= :dateDebut')
                    ->setParameter('dateDebut', $task['dateDebut']);
            }

            if($task['endDate'] != null){
                $qb->andWhere('s.dateFin <= :dateFin')
                    ->setParameter('dateFin', $task['dateFin']);
            }

            if($task['organisateur'] != 0){
                // TODO: get connected user here
//                $qb->andWhere('s.organisateur = :organisateur')
//                    ->setParameter('organisateur', );
            }

            if($task['inscrit'] != 0){
                // TODO: get connected user here
//                $qb->andWhere('s.inscrit = :inscrit')
//                    ->setParameter('inscrit', );
            }

            if($task['non_inscrit'] != 0){
                // TODO: get connected user here
//                $qb->andWhere('s.non_inscrit = :inscrit')
//                    ->setParameter('inscrit', );
            }

            if($task['passees'] != 0){
                $qb->andWhere('s.dateDebut < new /DateTime()');
            }

            return $qb->getQuery()->getResult();
        }

            /**
             * si site != null: filter by site (else: all)
             * & si sortie != '': filter by tout ce qui contient cette recherche (else: all)
             * & si date début != null : filter by tout ce qui a lieu APRES cette date
             * & date fin != null : filter by tout ce qui a lieu AVANT cette date
             * si organisateur coché: filter par seulement organisateur = user (else: all)
             * si inscrit coché: filter par seulement les inscrits (else: all)
             * si non_inscrit coché: filter par seulement là ou user n'est PAS inscrit
             * sorties passées: afficher que les sorties dont la date est passée. (else: all)
             */
}
