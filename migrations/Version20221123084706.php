<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221123084706 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE menu_translation DROP FOREIGN KEY FK_DC955B23CCD7E912');
        $this->addSql('ALTER TABLE menu_translation ADD CONSTRAINT FK_DC955B23B213FA4 FOREIGN KEY (lang_id) REFERENCES lang (id)');
        $this->addSql('ALTER TABLE menu_translation ADD CONSTRAINT FK_DC955B23CCD7E912 FOREIGN KEY (menu_id) REFERENCES menu (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_DC955B23B213FA4 ON menu_translation (lang_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE menu_translation DROP FOREIGN KEY FK_DC955B23B213FA4');
        $this->addSql('ALTER TABLE menu_translation DROP FOREIGN KEY FK_DC955B23CCD7E912');
        $this->addSql('DROP INDEX IDX_DC955B23B213FA4 ON menu_translation');
        $this->addSql('ALTER TABLE menu_translation ADD CONSTRAINT FK_DC955B23CCD7E912 FOREIGN KEY (menu_id) REFERENCES menu (id)');
    }
}
