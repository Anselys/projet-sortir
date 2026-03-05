<?php

namespace App\Tests\Service\Admin;

use App\Entity\Participant;
use App\Service\Admin\UtilisateurService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class UtilisateurTest extends TestCase
{
    public function test_changer_statut_compte_utilisateur_cas_nominal(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $service = new UtilisateurService($em);
        $participant = new Participant();
        $participant->setIsAdmin(false);
        $participant->setIsActif(true);

        // La méthode flush doit se déclencher si l'utilisateur n'est pas admin
        $em->expects($this->once())->method('flush');

        $service->changerStatutCompteUtilisateur($participant);

        $this->assertFalse($participant->isActif());
    }

    public function test_changer_statut_compte_admin_impossible(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $service = new UtilisateurService($em);
        $participant = new Participant();
        $participant->setIsAdmin(true);
        $participant->setIsActif(true);

        // Le flush ne doit pas avoir lieu car la méthode retourne false
        $em->expects($this->never())->method('flush');

        // La méthode doit renvoyer false si isAdmin vaut true => on ne doit pas pouvoir rendre un compte admin inactif
        $result = $service->changerStatutCompteUtilisateur($participant);

        $this->assertFalse($result);
    }
}
