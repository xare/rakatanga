<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220620091554 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE menu_location_menu DROP FOREIGN KEY FK_5BC2DA456EB13FC2');
        $this->addSql('ALTER TABLE menu_location_menu DROP FOREIGN KEY FK_5BC2DA45CCD7E912');
        $this->addSql('ALTER TABLE menu_location_menu ADD CONSTRAINT FK_5BC2DA456EB13FC2 FOREIGN KEY (menu_location_id) REFERENCES menu_location (id)');
        $this->addSql('ALTER TABLE menu_location_menu ADD CONSTRAINT FK_5BC2DA45CCD7E912 FOREIGN KEY (menu_id) REFERENCES menu (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE menu_location_menu DROP FOREIGN KEY FK_5BC2DA456EB13FC2');
        $this->addSql('ALTER TABLE menu_location_menu DROP FOREIGN KEY FK_5BC2DA45CCD7E912');
        $this->addSql('ALTER TABLE menu_location_menu ADD CONSTRAINT FK_5BC2DA456EB13FC2 FOREIGN KEY (menu_location_id) REFERENCES menu_location (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE menu_location_menu ADD CONSTRAINT FK_5BC2DA45CCD7E912 FOREIGN KEY (menu_id) REFERENCES menu (id) ON DELETE CASCADE');
    }
}
