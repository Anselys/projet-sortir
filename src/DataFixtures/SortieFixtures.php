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
    private EtatRepository $etatRepository;
    private ParticipantRepository $participantRepository;
    private LieuRepository $lieuRepository;
    private SiteRepository $siteRepository;

    public function __construct(
        EtatRepository        $etatRepository,
        ParticipantRepository $participantRepository,
        LieuRepository        $lieuRepository,
        SiteRepository        $siteRepository
    )
    {
        $this->etatRepository = $etatRepository;
        $this->participantRepository = $participantRepository;
        $this->lieuRepository = $lieuRepository;
        $this->siteRepository = $siteRepository;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        $etats = $this->etatRepository->findAll();
        $participants = $this->participantRepository->findAll();
        $lieux = $this->lieuRepository->findAll();
        $sites = $this->siteRepository->findAll();

        for ($i = 0; $i < 100; $i++) {
            $sortie = new Sortie();

            // gestion des dates et durée
            $dateDebut = $faker->dateTimeBetween('now', '+3 months', 'Europe/Paris'); // min, now, max: now + 3 mois
            $duree = $faker->numberBetween(60, 1440); // min 1h, max 24h
            $dateFin = (clone $dateDebut)->modify("+$duree minutes"); // fin de la sortie = dateDebut + duree
            $minCloture = (clone $dateDebut)->modify('+1 hour'); // min cloture = now +1h
            $maxCloture = (clone $dateFin)->modify('-2 days'); // max cloture = dateFin - 2 jours

            //  si max < min, max +1h (ex: durée trop courte)
            if ($maxCloture <= $minCloture) {
                $maxCloture = (clone $minCloture)->modify('+1 hour');
            }

            $dateCloture = $faker->dateTimeBetween($minCloture, $maxCloture);

            $sortie
                ->setNom($faker->words(2, true))
                ->setDescription($faker->realText(300))
                ->setDuree($duree)
                ->setDateDebut($dateDebut)
                ->setDateCloture($dateCloture)
                ->setEtat($faker->randomElement($etats))
                ->setOrganisateur($faker->randomElement($participants))
                ->setNbInscriptionsMax($faker->numberBetween($min = 1, $max = 10))
                ->setUrlPhoto($faker->imageUrl($width = 640, $height = 480))
                ->setLieu($faker->randomElement($lieux))
                ->setSiteOrganisateur($faker->randomElement($sites))
                ->setEtat($faker->randomElement($etats))
                ->setIsArchivee(false);

            $manager->persist($sortie);
        }
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            ParticipantFixtures::class,
            LieuFixtures::class,
        ];
    }
}
