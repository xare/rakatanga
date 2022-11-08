<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220113112833 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reservation_data DROP FOREIGN KEY FK_BBCA3E0BE58489A0');
        $this->addSql('DROP INDEX IDX_BBCA3E0BE58489A0 ON reservation_data');
        $this->addSql('ALTER TABLE reservation_data CHANGE traveller_id travellers_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE reservation_data ADD CONSTRAINT FK_BBCA3E0B31429A44 FOREIGN KEY (travellers_id) REFERENCES travellers (id)');
        $this->addSql('CREATE INDEX IDX_BBCA3E0B31429A44 ON reservation_data (travellers_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reservation_data DROP FOREIGN KEY FK_BBCA3E0B31429A44');
        $this->addSql('DROP INDEX IDX_BBCA3E0B31429A44 ON reservation_data');
        $this->addSql('ALTER TABLE reservation_data CHANGE travellers_id traveller_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE reservation_data ADD CONSTRAINT FK_BBCA3E0BE58489A0 FOREIGN KEY (traveller_id) REFERENCES travellers (id)');
        $this->addSql('CREATE INDEX IDX_BBCA3E0BE58489A0 ON reservation_data (traveller_id)');
    }
}
