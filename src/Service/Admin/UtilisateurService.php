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
        if ($participant->isAdmin()) {
            return false;
        }

        $participant->setIsActif(!$participant->isActif());
        $this->em->flush();

        return true;
    }
}
