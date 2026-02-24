<?php

namespace App\DataFixtures;

use App\Entity\Lieu;
use App\Repository\VilleRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class LieuFixtures extends Fixture
{
    private VilleRepository $villeRepository;

    public function __construct(VilleRepository $villeRepository) {
        $this->villeRepository = $villeRepository;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        $villes = $this->villeRepository->findAll();

        $types = [
            'Bar',
            'Restaurant',
            'Musée',
            'Cinéma',
            'Parc',
            'Place',
            'Bowling',
            'Escape Game',
            'Théâtre',
            'Salle de concert'
        ];

        for ($i = 0; $i < 100; $i++) {
            $lieu = new Lieu();
            $ville = $faker->randomElement($villes);

            $type = $faker->randomElement($types);
            $nom = $type . ' ' . $faker->company();

            $lieu->setNom(substr($nom, 0, 30))
                ->setRue($faker->streetAddress())
                ->setLatitude($faker->latitude(41, 51))
                ->setLongitude($faker->longitude(-5, 9))
                ->setVille($ville);

            $manager->persist($lieu);
        }

        $manager->flush();
    }
}
