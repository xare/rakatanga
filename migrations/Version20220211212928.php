<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220211212928 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reservation_travellers DROP FOREIGN KEY FK_DE3C546031429A44');
        $this->addSql('ALTER TABLE reservation_travellers DROP FOREIGN KEY FK_DE3C5460B83297E7');
        $this->addSql('ALTER TABLE reservation_travellers DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE reservation_travellers ADD CONSTRAINT FK_DE3C546031429A44 FOREIGN KEY (travellers_id) REFERENCES reservation (id)');
        $this->addSql('ALTER TABLE reservation_travellers ADD CONSTRAINT FK_DE3C5460B83297E7 FOREIGN KEY (reservation_id) REFERENCES travellers (id)');
        $this->addSql('ALTER TABLE reservation_travellers ADD PRIMARY KEY (travellers_id, reservation_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reservation_travellers DROP FOREIGN KEY FK_DE3C546031429A44');
        $this->addSql('ALTER TABLE reservation_travellers DROP FOREIGN KEY FK_DE3C5460B83297E7');
        $this->addSql('ALTER TABLE reservation_travellers DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE reservation_travellers ADD CONSTRAINT FK_DE3C546031429A44 FOREIGN KEY (travellers_id) REFERENCES travellers (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reservation_travellers ADD CONSTRAINT FK_DE3C5460B83297E7 FOREIGN KEY (reservation_id) REFERENCES reservation (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reservation_travellers ADD PRIMARY KEY (reservation_id, travellers_id)');
    }
}
