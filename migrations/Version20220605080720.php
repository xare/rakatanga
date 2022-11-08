<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220605080720 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE oldreservations (id INT AUTO_INCREMENT NOT NULL, langue VARCHAR(2) NOT NULL, nbpilotes INT NOT NULL, nbpassagers INT NOT NULL, commentaire LONGTEXT NOT NULL, log LONGTEXT NOT NULL, codepromo VARCHAR(10) NOT NULL, montant NUMERIC(10, 2) NOT NULL, reduction NUMERIC(10, 2) NOT NULL, totalttc NUMERIC(10, 2) NOT NULL, notes LONGTEXT NOT NULL, statut VARCHAR(15) NOT NULL, origine_ajout VARCHAR(50) NOT NULL, date_ajout DATETIME NOT NULL, date_paiement_1 DATETIME NOT NULL, date_paiement_2 DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE oldreservations');
    }
}
