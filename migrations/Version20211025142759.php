<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211025142759 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE texts (id INT AUTO_INCREMENT NOT NULL, lang_id INT NOT NULL, section VARCHAR(100) NOT NULL, acronym VARCHAR(15) NOT NULL, date DATETIME DEFAULT NULL, INDEX IDX_1E3513BFB213FA4 (lang_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE texts ADD CONSTRAINT FK_1E3513BFB213FA4 FOREIGN KEY (lang_id) REFERENCES lang (id)');
        $this->addSql('ALTER TABLE continents CHANGE code code VARCHAR(2) NOT NULL');
        $this->addSql('ALTER TABLE onglets CHANGE url url VARCHAR(50) NOT NULL');
        $this->addSql('ALTER TABLE textes CHANGE id id VARCHAR(15) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE texts');
        $this->addSql('ALTER TABLE continents CHANGE code code VARCHAR(2) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE onglets CHANGE url url VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE textes CHANGE id id VARCHAR(15) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}
