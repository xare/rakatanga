<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211220130950 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE ext_log_entries (id INT AUTO_INCREMENT NOT NULL, action VARCHAR(8) NOT NULL, logged_at DATETIME NOT NULL, object_id VARCHAR(64) DEFAULT NULL, object_class VARCHAR(191) NOT NULL, version INT NOT NULL, data LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', username VARCHAR(191) DEFAULT NULL, INDEX log_class_lookup_idx (object_class), INDEX log_date_lookup_idx (logged_at), INDEX log_user_lookup_idx (username), INDEX log_version_lookup_idx (object_id, object_class, version), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB ROW_FORMAT = DYNAMIC');
        $this->addSql('CREATE TABLE ext_translations (id INT AUTO_INCREMENT NOT NULL, locale VARCHAR(8) NOT NULL, object_class VARCHAR(191) NOT NULL, field VARCHAR(32) NOT NULL, foreign_key VARCHAR(64) NOT NULL, content LONGTEXT DEFAULT NULL, INDEX translations_lookup_idx (locale, object_class, foreign_key), UNIQUE INDEX lookup_unique_idx (locale, object_class, field, foreign_key), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB ROW_FORMAT = DYNAMIC');
        $this->addSql('CREATE TABLE reservation_data (id INT AUTO_INCREMENT NOT NULL, reservation_id INT NOT NULL, user_id INT NOT NULL, traveller_id INT DEFAULT NULL, passport_no VARCHAR(255) DEFAULT NULL, passport_issue_date DATE DEFAULT NULL, passport_expiration_date DATE DEFAULT NULL, visa_number VARCHAR(255) DEFAULT NULL, visa_issue_date DATE DEFAULT NULL, visa_expiration_date DATE DEFAULT NULL, drivers_number VARCHAR(255) DEFAULT NULL, drivers_issue_date DATE DEFAULT NULL, drivers_expiration_date DATE DEFAULT NULL, INDEX IDX_BBCA3E0BB83297E7 (reservation_id), INDEX IDX_BBCA3E0BA76ED395 (user_id), INDEX IDX_BBCA3E0BE58489A0 (traveller_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reservation_data_document (reservation_data_id INT NOT NULL, document_id INT NOT NULL, INDEX IDX_FCDB33F750601AB9 (reservation_data_id), INDEX IDX_FCDB33F7C33F7837 (document_id), PRIMARY KEY(reservation_data_id, document_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE reservation_data ADD CONSTRAINT FK_BBCA3E0BB83297E7 FOREIGN KEY (reservation_id) REFERENCES reservation (id)');
        $this->addSql('ALTER TABLE reservation_data ADD CONSTRAINT FK_BBCA3E0BA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE reservation_data ADD CONSTRAINT FK_BBCA3E0BE58489A0 FOREIGN KEY (traveller_id) REFERENCES travellers (id)');
        $this->addSql('ALTER TABLE reservation_data_document ADD CONSTRAINT FK_FCDB33F750601AB9 FOREIGN KEY (reservation_data_id) REFERENCES reservation_data (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reservation_data_document ADD CONSTRAINT FK_FCDB33F7C33F7837 FOREIGN KEY (document_id) REFERENCES document (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE reservations');
        $this->addSql('ALTER TABLE options DROP FOREIGN KEY FK_D035FA87ECAB15B3');
        $this->addSql('ALTER TABLE options ADD CONSTRAINT FK_D035FA87ECAB15B3 FOREIGN KEY (travel_id) REFERENCES travel (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE travel_translation CHANGE url url VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reservation_data_document DROP FOREIGN KEY FK_FCDB33F750601AB9');
        $this->addSql('CREATE TABLE reservations (id INT AUTO_INCREMENT NOT NULL, date INT UNSIGNED NOT NULL, langue VARCHAR(2) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, nbpilotes INT UNSIGNED NOT NULL, nbpassagers INT UNSIGNED NOT NULL, commentaire TEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, log TEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, codepromo VARCHAR(10) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, montant NUMERIC(10, 2) NOT NULL, reduction NUMERIC(10, 2) NOT NULL, totalttc NUMERIC(10, 2) NOT NULL, notes TEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, statut VARCHAR(15) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, origine_ajout VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, date_ajout DATETIME DEFAULT NULL, date_paiement_1 DATETIME DEFAULT NULL, date_paiement_2 DATETIME DEFAULT NULL, INDEX date (date), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('DROP TABLE ext_log_entries');
        $this->addSql('DROP TABLE ext_translations');
        $this->addSql('DROP TABLE reservation_data');
        $this->addSql('DROP TABLE reservation_data_document');
        $this->addSql('ALTER TABLE options DROP FOREIGN KEY FK_D035FA87ECAB15B3');
        $this->addSql('ALTER TABLE options ADD CONSTRAINT FK_D035FA87ECAB15B3 FOREIGN KEY (travel_id) REFERENCES travel (id)');
        $this->addSql('ALTER TABLE travel_translation CHANGE url url VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}
