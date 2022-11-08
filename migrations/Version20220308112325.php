<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220308112325 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        /* $this->addSql('ALTER TABLE reservation_travellers DROP FOREIGN KEY FK_1D5FAACAB83297E7');
        $this->addSql('ALTER TABLE reservation_travellers DROP FOREIGN KEY FK_1D5FAACA31429A44');
        $this->addSql('ALTER TABLE reservation_travellers DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE reservation_travellers DROP FOREIGN KEY FK_1D5FAACAB83297E7');
        $this->addSql('ALTER TABLE reservation_travellers DROP FOREIGN KEY FK_1D5FAACA31429A44');
        $this->addSql('ALTER TABLE reservation_travellers ADD CONSTRAINT FK_DE3C5460B83297E7 FOREIGN KEY (reservation_id) REFERENCES reservation (id)');
        $this->addSql('ALTER TABLE reservation_travellers ADD CONSTRAINT FK_DE3C546031429A44 FOREIGN KEY (travellers_id) REFERENCES travellers (id)');
        $this->addSql('ALTER TABLE reservation_travellers ADD PRIMARY KEY (reservation_id, travellers_id)'); */
        /* $this->addSql('DROP INDEX idx_1d5faacab83297e7 ON reservation_travellers');
        $this->addSql('CREATE INDEX IDX_DE3C5460B83297E7 ON reservation_travellers (reservation_id)');
        $this->addSql('DROP INDEX idx_1d5faaca31429a44 ON reservation_travellers');
        $this->addSql('CREATE INDEX IDX_DE3C546031429A44 ON reservation_travellers (travellers_id)');
        $this->addSql('ALTER TABLE reservation_travellers ADD CONSTRAINT FK_1D5FAACAB83297E7 FOREIGN KEY (reservation_id) REFERENCES reservation (id) ON DELETE CASCADE'); */
        /* $this->addSql('ALTER TABLE reservation_travellers ADD CONSTRAINT FK_1D5FAACA31429A44 FOREIGN KEY (travellers_id) REFERENCES travellers (id) ON DELETE CASCADE'); */
        $this->addSql('ALTER TABLE user DROP arrhes, DROP solde, DROP assurance, DROP vols, DROP statut, DROP remarque, DROP charge_id, DROP idcard');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reservation_travellers DROP FOREIGN KEY FK_DE3C5460B83297E7');
        $this->addSql('ALTER TABLE reservation_travellers DROP FOREIGN KEY FK_DE3C546031429A44');
        $this->addSql('ALTER TABLE reservation_travellers DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE reservation_travellers DROP FOREIGN KEY FK_DE3C5460B83297E7');
        $this->addSql('ALTER TABLE reservation_travellers DROP FOREIGN KEY FK_DE3C546031429A44');
        $this->addSql('ALTER TABLE reservation_travellers ADD CONSTRAINT FK_1D5FAACAB83297E7 FOREIGN KEY (reservation_id) REFERENCES reservation (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reservation_travellers ADD CONSTRAINT FK_1D5FAACA31429A44 FOREIGN KEY (travellers_id) REFERENCES travellers (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reservation_travellers ADD PRIMARY KEY (travellers_id, reservation_id)');
        $this->addSql('DROP INDEX idx_de3c546031429a44 ON reservation_travellers');
        $this->addSql('CREATE INDEX IDX_1D5FAACA31429A44 ON reservation_travellers (travellers_id)');
        $this->addSql('DROP INDEX idx_de3c5460b83297e7 ON reservation_travellers');
        $this->addSql('CREATE INDEX IDX_1D5FAACAB83297E7 ON reservation_travellers (reservation_id)');
        $this->addSql('ALTER TABLE reservation_travellers ADD CONSTRAINT FK_DE3C5460B83297E7 FOREIGN KEY (reservation_id) REFERENCES reservation (id)');
        $this->addSql('ALTER TABLE reservation_travellers ADD CONSTRAINT FK_DE3C546031429A44 FOREIGN KEY (travellers_id) REFERENCES travellers (id)');
        $this->addSql('ALTER TABLE user ADD arrhes INT DEFAULT NULL, ADD solde INT DEFAULT NULL, ADD assurance VARCHAR(3) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, ADD vols VARCHAR(3) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, ADD statut VARCHAR(15) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, ADD remarque VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, ADD charge_id VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, ADD idcard VARCHAR(10) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}
