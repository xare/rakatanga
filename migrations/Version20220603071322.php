<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220603071322 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE mailings (id INT AUTO_INCREMENT NOT NULL, subject VARCHAR(255) DEFAULT NULL, content LONGTEXT DEFAULT NULL, to_addresses LONGTEXT NOT NULL, attachment LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE popups ADD main_photo_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE popups ADD CONSTRAINT FK_7EB41FA9A7BC5DF9 FOREIGN KEY (main_photo_id) REFERENCES media (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7EB41FA9A7BC5DF9 ON popups (main_photo_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE mailings');
        $this->addSql('ALTER TABLE popups DROP FOREIGN KEY FK_7EB41FA9A7BC5DF9');
        $this->addSql('DROP INDEX UNIQ_7EB41FA9A7BC5DF9 ON popups');
        $this->addSql('ALTER TABLE popups DROP main_photo_id');
    }
}
