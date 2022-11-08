<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220727190610 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE oldreservations ADD inscriptions_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE oldreservations ADD CONSTRAINT FK_D29F4F038E2AD382 FOREIGN KEY (inscriptions_id) REFERENCES inscriptions (id)');
        $this->addSql('CREATE INDEX IDX_D29F4F038E2AD382 ON oldreservations (inscriptions_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE oldreservations DROP FOREIGN KEY FK_D29F4F038E2AD382');
        $this->addSql('DROP INDEX IDX_D29F4F038E2AD382 ON oldreservations');
        $this->addSql('ALTER TABLE oldreservations DROP inscriptions_id');
    }
}
