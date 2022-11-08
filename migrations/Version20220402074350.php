<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220402074350 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE articles ADD main_photo_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE articles ADD CONSTRAINT FK_BFDD3168A7BC5DF9 FOREIGN KEY (main_photo_id) REFERENCES media (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_BFDD3168A7BC5DF9 ON articles (main_photo_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE articles DROP FOREIGN KEY FK_BFDD3168A7BC5DF9');
        $this->addSql('DROP INDEX UNIQ_BFDD3168A7BC5DF9 ON articles');
        $this->addSql('ALTER TABLE articles DROP main_photo_id');
    }
}
