<?php

namespace App\Repository;

use App\Entity\Participant;
use App\Entity\Site;
use App\Entity\Sortie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Form\FormInterface;

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
        public function findBySite(Site $site, Participant $participant): array
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

        public function findByTri(FormInterface $result, $participant): array{
            $tri = $result->getData();
            $qb = $this->createQueryBuilder('s');

            if($tri == null){
                return $this->findAll();
            }

            if($tri['Site'] != null){
                $qb->andWhere('s.site_organisateur_Id = :siteId')
                    ->setParameter('siteId', $tri['Site']->getId());
            }

            if($tri['recherche'] != null){
                $qb->andWhere('s.nom LIKE :recherche')
                    ->setParameter('recherche', '%'.$tri['recherche'].'%');
            }

            if($tri['startDate'] != null){
                $qb->andWhere('s.date_debut >= :dateDebut')
                    ->setParameter('dateDebut', $tri['dateDebut']);
            }

            if($tri['endDate'] != null){
                $qb->andWhere('s.date_cloture <= :dateCloture')
                    ->setParameter('dateCloture', $tri['dateFin']);
            }

            if($tri['organisateur'] != 0){
                $qb->andWhere('s.organisateur_id = :organisateur')
                    ->setParameter('organisateur', $participant->getId());
            }

            if($tri['inscrit'] != 0){
                // TODO: dépatouiller ça
//                $participant->getSortiesOrganisees()->findFirst((int)$tri['id'])->setDateFin(new \DateTime($tri['dateFin']));
//                $qb->andWhere('s.inscrit = :inscrit')
//                    ->setParameter('inscrit', );
            }

            if($tri['non_inscrit'] != 0){
                // TODO: dépatouiller ça
//                $qb->andWhere('s.non_inscrit = :inscrit')
//                    ->setParameter('inscrit', );
            }

            if($tri['passees'] != 0){
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
