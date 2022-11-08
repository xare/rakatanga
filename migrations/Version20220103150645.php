<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220103150645 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE codespromo (id INT UNSIGNED AUTO_INCREMENT NOT NULL, code VARCHAR(10) NOT NULL, libelle VARCHAR(100) NOT NULL, commentaire VARCHAR(255) NOT NULL, montant NUMERIC(10, 2) NOT NULL, pourcentage INT UNSIGNED NOT NULL, type VARCHAR(15) NOT NULL, nombre INT UNSIGNED NOT NULL, debut DATE DEFAULT NULL, fin DATE DEFAULT NULL, email VARCHAR(255) NOT NULL, statut VARCHAR(3) NOT NULL, date_ajout DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE codespromo');
    }
}
