<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220814170754 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE oldreservations CHANGE date_paiement_1 date_paiement_1 DATETIME DEFAULT NULL, CHANGE date_paiement_2 date_paiement_2 DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE oldreservations CHANGE date_paiement_1 date_paiement_1 DATETIME NOT NULL, CHANGE date_paiement_2 date_paiement_2 DATETIME NOT NULL');
    }
}
