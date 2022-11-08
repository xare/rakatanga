<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220314223523 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE media ADD is_gallery TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE reservation_travellers DROP FOREIGN KEY FK_DE3C546031429A44');
        $this->addSql('DROP INDEX idx_1d5faaca31429a44 ON reservation_travellers');
        $this->addSql('CREATE INDEX IDX_DE3C546031429A44 ON reservation_travellers (travellers_id)');
        $this->addSql('ALTER TABLE reservation_travellers ADD CONSTRAINT FK_DE3C546031429A44 FOREIGN KEY (travellers_id) REFERENCES travellers (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE media DROP is_gallery');
        $this->addSql('ALTER TABLE reservation_travellers DROP FOREIGN KEY FK_DE3C546031429A44');
        $this->addSql('DROP INDEX idx_de3c546031429a44 ON reservation_travellers');
        $this->addSql('CREATE INDEX IDX_1D5FAACA31429A44 ON reservation_travellers (travellers_id)');
        $this->addSql('ALTER TABLE reservation_travellers ADD CONSTRAINT FK_DE3C546031429A44 FOREIGN KEY (travellers_id) REFERENCES travellers (id)');
    }
}
