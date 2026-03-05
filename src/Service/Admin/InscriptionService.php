<?php

namespace App\Service\Admin;

use App\Entity\Participant;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

readonly class InscriptionService
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $passwordHasher
    ){}

    public function inscrire(Participant $participant, string $motDePasse): void {
        $participant->setPassword($this->passwordHasher->hashPassword($participant, $motDePasse));

        $this->em->persist($participant);
        $this->em->flush();
    }
}
