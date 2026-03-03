<?php

namespace App\DataFixtures;

use App\Entity\Participant;
use App\Repository\SiteRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ParticipantFixtures extends Fixture
{
    private SiteRepository $siteRepository;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(SiteRepository $siteRepository, UserPasswordHasherInterface $passwordHasher)
    {
        $this->siteRepository = $siteRepository;
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        $sites = $this->siteRepository->findAll();

        $adminParticipant = new Participant();
        $userParticipant = new Participant();

        $password = $this->passwordHasher->hashPassword($adminParticipant, 'password');

        $adminParticipant
            ->setPseudo('admin')
            ->setPrenom('Admin')
            ->setNom('ADMIN')
            ->setEmail('admin@sortir.com')
            ->setPassword($password)
            ->setTelephone($faker->phoneNumber())
            ->setIsActif(true)
            ->setIsAdmin(true)
            ->setSite($faker->randomElement($sites))
            ->setRoles(['ROLE_ADMIN', 'ROLE_USER'])
            ->setUrlPhoto(null);

        $userParticipant
            ->setPseudo('user')
            ->setPrenom('User')
            ->setNom('USER')
            ->setEmail('user@sortir.com')
            ->setPassword($password)
            ->setTelephone($faker->phoneNumber())
            ->setIsActif(true)
            ->setIsAdmin(false)
            ->setSite($faker->randomElement($sites))
            ->setRoles(['ROLE_USER'])
            ->setUrlPhoto(null);

        $isActif = $faker->boolean(95);

        $manager->persist($userParticipant);
        $manager->persist($adminParticipant);

        for ($i = 0; $i < 20; $i++) {
            $site = $faker->randomElement($sites);
            $isAdmin = $faker->boolean();
            $roles = $isAdmin ? ['ROLE_ADMIN', 'ROLE_USER'] : ['ROLE_USER'];
            $firstName = $faker->firstName();
            $lastName = $faker->lastName();
            $pseudo = $firstName . $lastName;
            $email = $firstName . $lastName . '@gmail.com';
            $participant = new Participant();
            $password = $this->passwordHasher->hashPassword($participant, 'password');

            $participant
                ->setPseudo($pseudo)
                ->setPassword($password)
                ->setNom($lastName)
                ->setPrenom($firstName)
                ->setEmail($email)
                ->setTelephone($faker->phoneNumber())
                ->setIsActif($isActif)
                ->setIsAdmin($isAdmin)
                ->setSite($site)
                ->setRoles($roles)
                ->setUrlPhoto(null);

            $manager->persist($participant);
        }

        $manager->flush();
    }
}
