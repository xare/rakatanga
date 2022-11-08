<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220620063322 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE menu_location_menu (menu_location_id INT NOT NULL, menu_id INT NOT NULL, INDEX IDX_5BC2DA456EB13FC2 (menu_location_id), INDEX IDX_5BC2DA45CCD7E912 (menu_id), PRIMARY KEY(menu_location_id, menu_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE menu_location_menu ADD CONSTRAINT FK_5BC2DA456EB13FC2 FOREIGN KEY (menu_location_id) REFERENCES menu_location (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE menu_location_menu ADD CONSTRAINT FK_5BC2DA45CCD7E912 FOREIGN KEY (menu_id) REFERENCES menu (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE menu_location DROP FOREIGN KEY FK_72CFCC5DCCD7E912');
        $this->addSql('DROP INDEX UNIQ_72CFCC5DCCD7E912 ON menu_location');
        $this->addSql('ALTER TABLE menu_location DROP menu_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE menu_location_menu');
        $this->addSql('ALTER TABLE menu_location ADD menu_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE menu_location ADD CONSTRAINT FK_72CFCC5DCCD7E912 FOREIGN KEY (menu_id) REFERENCES menu (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_72CFCC5DCCD7E912 ON menu_location (menu_id)');
    }
}
