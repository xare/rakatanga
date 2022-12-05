<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221128062716 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE options_translations ADD infodocs_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE options_translations ADD CONSTRAINT FK_E963F50311C1AC1F FOREIGN KEY (infodocs_id) REFERENCES infodocs (id)');
        $this->addSql('CREATE INDEX IDX_E963F50311C1AC1F ON options_translations (infodocs_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE options_translations DROP FOREIGN KEY FK_E963F50311C1AC1F');
        $this->addSql('DROP INDEX IDX_E963F50311C1AC1F ON options_translations');
        $this->addSql('ALTER TABLE options_translations DROP infodocs_id');
    }
}
