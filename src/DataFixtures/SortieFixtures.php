<?php

namespace App\DataFixtures;

use App\Entity\Sortie;
use App\Repository\EtatRepository;
use App\Repository\LieuRepository;
use App\Repository\ParticipantRepository;
use App\Repository\SiteRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class SortieFixtures extends Fixture implements DependentFixtureInterface
{
    private const ETAT_CREEE = 'CREEE';
    private const ETAT_OUVERTE = 'OUVERTE';
    private const ETAT_CLOTUREE = 'CLOTUREE';
    private const ETAT_PASSEE = 'PASSEE';
    private const ETAT_ANNULEE = 'ANNULEE';

    public function __construct(
        private readonly EtatRepository $etatRepository,
        private readonly ParticipantRepository $participantRepository,
        private readonly LieuRepository $lieuRepository,
        private readonly SiteRepository $siteRepository
    ) {}

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        $participants = $this->participantRepository->findAll();
        $lieux = $this->lieuRepository->findAll();
        $sites = $this->siteRepository->findAll();
        $sortieNames = [
            'Randonnée en forêt',
            'Balade au bord de la mer',
            'Soirée jeux de société',
            'Apéro entre amis',
            'Sortie cinéma',
            'Bowling night',
            'Escape game',
            'Pique-nique au parc',
            'Course à pied matinale',
            'Tournoi de pétanque',
            'Sortie vélo',
            'Randonnée en montagne',
            'Soirée karaoké',
            'Dégustation de vins',
            'Brunch du dimanche',
            'Match de foot entre amis',
            'Sortie musée',
            'Atelier cuisine',
            'Découverte d’un restaurant',
            'Sortie plage',
            'Barbecue entre amis',
            'Marche nordique',
            'Visite du centre-ville',
            'Balade photo',
            'Session yoga au parc',
            'Sortie patinoire',
            'Laser game',
            'Soirée billard',
            'Randonnée nocturne',
            'Observation des étoiles',
            'Sortie paddle',
            'Kayak sur la rivière',
            'Visite d’un marché local',
            'Festival de musique',
            'Concert live',
            'Soirée pizza',
            'Tournoi de jeux vidéo',
            'Balade en bord de lac',
            'Sortie accrobranche',
            'Initiation escalade',
            'Atelier peinture',
            'Sortie shopping',
            'Course d’orientation',
            'Balade à cheval',
            'Visite d’un château',
            'Découverte d’un vignoble',
            'Sortie roller',
            'Soirée quiz',
            'Sortie théâtre',
            'Balade urbaine',
        ];

        $now = new \DateTime();

        for ($i = 0; $i < 100; $i++) {

            $sortie = new Sortie();

            // Date début : passé ou futur
            $dateDebut = $faker->dateTimeBetween('-2 months', '+3 months');

            $duree = $faker->numberBetween(60, 300);

            // Date de clôture TOUJOURS avant dateDebut
            $dateCloture = $faker->dateTimeBetween(
                (clone $dateDebut)->modify('-1 month'),
                (clone $dateDebut)->modify('-1 day')
            );

            // nb max d'inscriptions autorisé
            $nbMax = $faker->numberBetween(3, 12);
            // nb d'inscrits
            $nbInscrits = $faker->numberBetween(0, $nbMax);

            $isAnnulee = $faker->boolean(5); // 5% annulées

            $etatCode = $this->determineEtat(
                $now,
                $dateDebut,
                $dateCloture,
                $nbInscrits,
                $nbMax,
                $isAnnulee
            );

            $etat = $this->etatRepository->findOneBy(['libelle' => $etatCode]);

            $sortie
                ->setNom($faker->randomElement($sortieNames))
                ->setDescription($faker->realText(200))
                ->setDuree($duree)
                ->setDateDebut($dateDebut)
                ->setDateCloture($dateCloture)
                ->setEtat($etat)
                ->setOrganisateur($faker->randomElement($participants))
                ->setNbInscriptionsMax($nbMax)
                ->setUrlPhoto(null)
                ->setLieu($faker->randomElement($lieux))
                ->setSiteOrganisateur($faker->randomElement($sites))
                ->setIsArchivee(false);

            $manager->persist($sortie);
        }

        $manager->flush();
    }

    private function determineEtat(
        \DateTime $now,
        \DateTime $dateDebut,
        \DateTime $dateCloture,
        int $nbInscrits,
        int $nbMax,
        bool $isAnnulee
    ): string {

        // Annulée
        if ($isAnnulee) {
            return self::ETAT_ANNULEE;
        }

        // Passée
        if ($dateDebut < $now) {
            return self::ETAT_PASSEE;
        }

        // Clôturée
        if ($nbInscrits >= $nbMax || $now >= $dateCloture) {
            return self::ETAT_CLOTUREE;
        }

        // Sinon => forcément future et encore ouverte
        return rand(0, 1)
            ? self::ETAT_OUVERTE
            : self::ETAT_CREEE;
    }

    public function getDependencies(): array
    {
        return [
            ParticipantFixtures::class,
            LieuFixtures::class,
        ];
    }
}
