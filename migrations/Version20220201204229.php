<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220201204229 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pages ADD main_photo_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE pages ADD CONSTRAINT FK_2074E575A7BC5DF9 FOREIGN KEY (main_photo_id) REFERENCES media (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_2074E575A7BC5DF9 ON pages (main_photo_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pages DROP FOREIGN KEY FK_2074E575A7BC5DF9');
        $this->addSql('DROP INDEX UNIQ_2074E575A7BC5DF9 ON pages');
        $this->addSql('ALTER TABLE pages DROP main_photo_id');
    }
}
