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
        // EM est mocké car on veut juste tester le comportement, on ne veut pas réellement se connecter à la base etc
        $em = $this->createMock(EntityManagerInterface::class);

        // On instancie le service injecté
        $service = new UtilisateurService($em);

        // On instancie un Participant avec
            // un rôle utilisateur (isAdmin à false)
            // et un compte actif (isActif à true)
        $utilisateur = new Participant();
        $utilisateur->setIsAdmin(false);
        $utilisateur->setIsActif(true);

        // La méthode flush doit se déclencher pour mettre à jour le statut du compte utilisateur
        $em->expects($this->once())->method('flush');

        // $result doit retourner true si le statut d'utilisateur a bien été modifié
        $result = $service->changerStatutCompteUtilisateur($utilisateur);

        // On teste que $result vaut bien true (et que donc, théoriquement, le statut a bien changé)
        $this->assertTrue($result);

        // On vérifie que le statut a effectivement changé
        $this->assertFalse($utilisateur->isActif());
    }

    public function test_desactiver_compte_admin_impossible(): void
    {
        // EM est mocké car on veut juste tester le comportement, on ne veut pas réellement se connecter à la base etc
        $em = $this->createMock(EntityManagerInterface::class);

        // On instancie le service injecté
        $service = new UtilisateurService($em);

        // On instancie un Participant avec
            // un rôle admin (isAdmin à true)
            // et un compte actif (isActif à true)
        $admin = new Participant();
        $admin->setIsAdmin(true);
        $admin->setIsActif(true);

        // Le flush ne doit pas avoir lieu car la méthode fait un early return si isAdmin et isActif valent true
        $em->expects($this->never())->method('flush');

        // La méthode doit renvoyer false si isAdmin ET isActif true => on ne doit pas pouvoir rendre un compte admin inactif
        $result = $service->changerStatutCompteUtilisateur($admin);

        // On vérifie que la méthode a bien renvoyé false
        $this->assertFalse($result);

        // On vérifie que le statut n'a pas changé et que le compte admin est toujours actif
        $this->assertTrue($admin->isActif());
    }

    public function test_reactiver_compte_admin_inactif(): void
    {
        // EM est mocké car on veut juste tester le comportement, on ne veut pas réellement se connecter à la base etc
        $em = $this->createMock(EntityManagerInterface::class);

        // On instancie le service injecté
        $service = new UtilisateurService($em);

        // On instancie un Participant avec
            // un rôle admin (isAdmin à true)
            // et un compte inactif (isActif à false)
        $admin = new Participant();
        $admin->setIsAdmin(true);
        $admin->setIsActif(false);

        // Le flush doit avoir lieu car le compte est inactif, et doit pouvoir être réactivé
        $em->expects($this->once())->method('flush');

        // La méthode doit renvoyer true si isAdmin ET isActif false => on doit pouvoir réactiver un compte admin inactif
        $result = $service->changerStatutCompteUtilisateur($admin);

        // On vérifie que la méthode a bien renvoyé true
        $this->assertTrue($result);

        // On vérifie que le statut a bien changé et que le compte admin est désormais actif
        $this->assertTrue($admin->isActif());
    }
}
