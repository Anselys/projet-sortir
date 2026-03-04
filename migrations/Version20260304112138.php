<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260304112138 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("INSERT INTO Ville(nom, cpo)
VALUES('Rezé', '44400'),
       ('Saint-Sébastien-sur-Loire', '44230'),
       ('Sainte-Luce-sur-Loire', '44980'),
       ('Saint-Herblain', '44800'),
       ('Orvault', '44700'),
       ('Bruz', '35170'),
       ('Melesse', '35520'),
       ('Noyal-sur-Vilaine', '35530'),
       ('Plomelin', '29700'),
       ('Pluguffan', '29700'),
       ('Gouesnach', '29950'),
       ('Aiffres', '79230'),
       ('Frontenay-Rohan-Rohan', '79270'),
       ('Coulon', '79510')");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
