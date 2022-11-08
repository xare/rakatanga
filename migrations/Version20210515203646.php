<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210515203646 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_dates (user_id INT NOT NULL, dates_id INT NOT NULL, INDEX IDX_49B63246A76ED395 (user_id), INDEX IDX_49B632463DA992C3 (dates_id), PRIMARY KEY(user_id, dates_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_dates ADD CONSTRAINT FK_49B63246A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_dates ADD CONSTRAINT FK_49B632463DA992C3 FOREIGN KEY (dates_id) REFERENCES dates (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE dates_backup');
        $this->addSql('DROP TABLE travel_backup');
        $this->addSql('ALTER TABLE continents CHANGE code code VARCHAR(2) NOT NULL');
        $this->addSql('CREATE INDEX voyage ON dates (voyage)');
        $this->addSql('ALTER TABLE onglets CHANGE url url VARCHAR(50) NOT NULL');
        $this->addSql('ALTER TABLE textes CHANGE id id VARCHAR(15) NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE dates_backup (id INT AUTO_INCREMENT NOT NULL, voyage INT UNSIGNED NOT NULL, debut DATE NOT NULL, fin DATE NOT NULL, prix_motard NUMERIC(10, 2) NOT NULL, prix_accomp NUMERIC(10, 2) NOT NULL, statut VARCHAR(15) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, travel_id INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE travel_backup (id INT AUTO_INCREMENT NOT NULL, departure_date DATETIME DEFAULT NULL, km INT DEFAULT NULL, arrival_date DATETIME DEFAULT NULL, price_driver NUMERIC(10, 2) NOT NULL, price_codriver NUMERIC(10, 2) NOT NULL, status VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, duration INT NOT NULL, level VARCHAR(3) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, date DATETIME NOT NULL, category_id INT DEFAULT NULL, main_title VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, INDEX IDX_2D0B6BCE12469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('DROP TABLE user_dates');
        $this->addSql('ALTER TABLE continents CHANGE code code VARCHAR(2) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('DROP INDEX voyage ON dates');
        $this->addSql('ALTER TABLE onglets CHANGE url url VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE textes CHANGE id id VARCHAR(15) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}
