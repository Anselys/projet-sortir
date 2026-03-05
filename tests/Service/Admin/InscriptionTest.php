<?php

namespace App\Tests\Service\Admin;

use App\Entity\Participant;
use App\Service\Admin\InscriptionService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class InscriptionTest extends TestCase
{
    public function test_inscrire_participant_fonctionne(): void
    {
        $passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $em = $this->createMock(EntityManagerInterface::class);
        $service = new InscriptionService($em, $passwordHasher);

        $participant = new Participant();
        $password = "password";
        $hashedPassword = "hashed_password";

        $passwordHasher
            ->expects($this->once())
            ->method('hashPassword')
            ->with($participant, $password)
            ->willReturn($hashedPassword);

        $em->expects($this->once())
            ->method('persist')
            ->with($participant);

        $service->inscrire($participant, $password);
        $this->assertEquals($hashedPassword, $participant->getPassword());
    }
}
