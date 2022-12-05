<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221123074805 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE menu_menu_location (menu_id INT NOT NULL, menu_location_id INT NOT NULL, INDEX IDX_D40C99CBCCD7E912 (menu_id), INDEX IDX_D40C99CB6EB13FC2 (menu_location_id), PRIMARY KEY(menu_id, menu_location_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE menu_menu_location ADD CONSTRAINT FK_D40C99CBCCD7E912 FOREIGN KEY (menu_id) REFERENCES menu (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE menu_menu_location ADD CONSTRAINT FK_D40C99CB6EB13FC2 FOREIGN KEY (menu_location_id) REFERENCES menu_location (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE menu_location_menu');
        $this->addSql('ALTER TABLE menu CHANGE title title VARCHAR(255) NOT NULL, CHANGE position position INT DEFAULT NULL');
        $this->addSql('ALTER TABLE menu_location CHANGE name name VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE menu_translation DROP FOREIGN KEY FK_DC955B23B213FA4');
        $this->addSql('ALTER TABLE menu_translation DROP FOREIGN KEY FK_DC955B23CCD7E912');
        $this->addSql('DROP INDEX IDX_DC955B23B213FA4 ON menu_translation');
        $this->addSql('ALTER TABLE menu_translation DROP lang_id, DROP title, DROP slug, CHANGE menu_id menu_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE menu_translation ADD CONSTRAINT FK_DC955B23CCD7E912 FOREIGN KEY (menu_id) REFERENCES menu (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE menu_location_menu (menu_location_id INT NOT NULL, menu_id INT NOT NULL, INDEX IDX_5BC2DA456EB13FC2 (menu_location_id), INDEX IDX_5BC2DA45CCD7E912 (menu_id), PRIMARY KEY(menu_location_id, menu_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE menu_location_menu ADD CONSTRAINT FK_5BC2DA456EB13FC2 FOREIGN KEY (menu_location_id) REFERENCES menu_location (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE menu_location_menu ADD CONSTRAINT FK_5BC2DA45CCD7E912 FOREIGN KEY (menu_id) REFERENCES menu (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE menu_menu_location');
        $this->addSql('ALTER TABLE menu CHANGE title title VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE position position INT NOT NULL');
        $this->addSql('ALTER TABLE menu_location CHANGE name name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE menu_translation DROP FOREIGN KEY FK_DC955B23CCD7E912');
        $this->addSql('ALTER TABLE menu_translation ADD lang_id INT NOT NULL, ADD title VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, ADD slug VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE menu_id menu_id INT NOT NULL');
        $this->addSql('ALTER TABLE menu_translation ADD CONSTRAINT FK_DC955B23B213FA4 FOREIGN KEY (lang_id) REFERENCES lang (id)');
        $this->addSql('ALTER TABLE menu_translation ADD CONSTRAINT FK_DC955B23CCD7E912 FOREIGN KEY (menu_id) REFERENCES menu (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_DC955B23B213FA4 ON menu_translation (lang_id)');
    }
}
