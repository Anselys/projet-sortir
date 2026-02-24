<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260223135045 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("INSERT INTO etat (libelle) VALUES ('CREEE'), ('OUVERTE'), ('EN_COURS'), ('CLOTUREE'), ('PASSEE'),('ANNULEE')");
        $this->addSql("INSERT INTO site (nom) VALUES ('NANTES'), ('RENNES'), ('QUIMPER'), ('NIORT')");
        $this->addSql("INSERT INTO ville (nom, cpo) VALUES ('NANTES', '44000' ), ('RENNES', '35000'), ('QUIMPER', '29000'), ('NIORT', '79000')");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
