<?php

namespace App\Service\Admin;

use App\Entity\Participant;
use Doctrine\ORM\EntityManagerInterface;

readonly class UtilisateurService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ){}

    public function changerStatutCompteUtilisateur(Participant $participant): bool {

        // Interdire de désactiver un compte admin
        if ($participant->isAdmin() && $participant->isActif()) {
            return false;
        }

        $participant->setIsActif(!$participant->isActif());
        $this->em->flush();

        return true;
    }
}
