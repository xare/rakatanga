<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210427091612 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE continents CHANGE code code VARCHAR(2) NOT NULL');
        $this->addSql('ALTER TABLE menu_translation DROP FOREIGN KEY FK_DC955B23CCD7E912');
        $this->addSql('ALTER TABLE menu_translation ADD CONSTRAINT FK_DC955B23CCD7E912 FOREIGN KEY (menu_id) REFERENCES menu (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE onglets CHANGE url url VARCHAR(50) NOT NULL');
        $this->addSql('ALTER TABLE page_translation ADD page_id INT NOT NULL');
        $this->addSql('ALTER TABLE page_translation ADD CONSTRAINT FK_A3D51B1DC4663E4 FOREIGN KEY (page_id) REFERENCES pages (id)');
        $this->addSql('CREATE INDEX IDX_A3D51B1DC4663E4 ON page_translation (page_id)');
        $this->addSql('ALTER TABLE textes CHANGE id id VARCHAR(15) NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE continents CHANGE code code VARCHAR(2) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE menu_translation DROP FOREIGN KEY FK_DC955B23CCD7E912');
        $this->addSql('ALTER TABLE menu_translation ADD CONSTRAINT FK_DC955B23CCD7E912 FOREIGN KEY (menu_id) REFERENCES menu (id)');
        $this->addSql('ALTER TABLE onglets CHANGE url url VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE page_translation DROP FOREIGN KEY FK_A3D51B1DC4663E4');
        $this->addSql('DROP INDEX IDX_A3D51B1DC4663E4 ON page_translation');
        $this->addSql('ALTER TABLE page_translation DROP page_id');
        $this->addSql('ALTER TABLE textes CHANGE id id VARCHAR(15) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}
