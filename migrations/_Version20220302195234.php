<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220302195234 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        /* $this->addSql('CREATE TABLE travellers_reservation (travellers_id INT NOT NULL, reservation_id INT NOT NULL, INDEX IDX_1D5FAACA31429A44 (travellers_id), INDEX IDX_1D5FAACAB83297E7 (reservation_id), PRIMARY KEY(travellers_id, reservation_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE travellers_reservation ADD CONSTRAINT FK_1D5FAACA31429A44 FOREIGN KEY (travellers_id) REFERENCES travellers (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE travellers_reservation ADD CONSTRAINT FK_1D5FAACAB83297E7 FOREIGN KEY (reservation_id) REFERENCES reservation (id) ON DELETE CASCADE'); */
        $this->addSql('ALTER TABLE reservation_travellers DROP FOREIGN KEY FK_DE3C546031429A44');
        $this->addSql('ALTER TABLE reservation_travellers DROP FOREIGN KEY FK_DE3C5460B83297E7');
        $this->addSql('DROP INDEX IDX_DE3C546031429A44 ON reservation_travellers');
        $this->addSql('ALTER TABLE reservation_travellers DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE reservation_travellers CHANGE travellers_id traveller_id INT NOT NULL');
        $this->addSql('ALTER TABLE reservation_travellers ADD CONSTRAINT FK_DE3C5460E58489A0 FOREIGN KEY (traveller_id) REFERENCES travellers (id)');
        $this->addSql('ALTER TABLE reservation_travellers ADD CONSTRAINT FK_DE3C5460B83297E7 FOREIGN KEY (reservation_id) REFERENCES reservation (id)');
        $this->addSql('CREATE INDEX IDX_DE3C5460E58489A0 ON reservation_travellers (traveller_id)');
        $this->addSql('ALTER TABLE reservation_travellers ADD PRIMARY KEY (reservation_id, traveller_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE travellers_reservation');
        $this->addSql('ALTER TABLE reservation_travellers DROP FOREIGN KEY FK_DE3C5460E58489A0');
        $this->addSql('ALTER TABLE reservation_travellers DROP FOREIGN KEY FK_DE3C5460B83297E7');
        $this->addSql('DROP INDEX IDX_DE3C5460E58489A0 ON reservation_travellers');
        $this->addSql('ALTER TABLE reservation_travellers DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE reservation_travellers CHANGE traveller_id travellers_id INT NOT NULL');
        $this->addSql('ALTER TABLE reservation_travellers ADD CONSTRAINT FK_DE3C546031429A44 FOREIGN KEY (travellers_id) REFERENCES reservation (id)');
        $this->addSql('ALTER TABLE reservation_travellers ADD CONSTRAINT FK_DE3C5460B83297E7 FOREIGN KEY (reservation_id) REFERENCES travellers (id)');
        $this->addSql('CREATE INDEX IDX_DE3C546031429A44 ON reservation_travellers (travellers_id)');
        $this->addSql('ALTER TABLE reservation_travellers ADD PRIMARY KEY (travellers_id, reservation_id)');
    }
}
