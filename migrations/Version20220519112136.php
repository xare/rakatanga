<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220519112136 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE popups_media (popups_id INT NOT NULL, media_id INT NOT NULL, INDEX IDX_F0C89172B1BEDAAA (popups_id), INDEX IDX_F0C89172EA9FDD75 (media_id), PRIMARY KEY(popups_id, media_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE popups_media ADD CONSTRAINT FK_F0C89172B1BEDAAA FOREIGN KEY (popups_id) REFERENCES popups (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE popups_media ADD CONSTRAINT FK_F0C89172EA9FDD75 FOREIGN KEY (media_id) REFERENCES media (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE popups_translation DROP FOREIGN KEY FK_8DF97A117CA3AC35');
        $this->addSql('ALTER TABLE popups_translation ADD CONSTRAINT FK_8DF97A117CA3AC35 FOREIGN KEY (popup_id) REFERENCES popups (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE popups_media');
        $this->addSql('ALTER TABLE popups_translation DROP FOREIGN KEY FK_8DF97A117CA3AC35');
        $this->addSql('ALTER TABLE popups_translation ADD CONSTRAINT FK_8DF97A117CA3AC35 FOREIGN KEY (popup_id) REFERENCES popups (id)');
    }
}
