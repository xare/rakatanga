<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220518101417 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE hotels DROP FOREIGN KEY FK_E402F625ECAB15B3');
        $this->addSql('DROP INDEX IDX_E402F625ECAB15B3 ON hotels');
        $this->addSql('ALTER TABLE hotels DROP travel_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE hotels ADD travel_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE hotels ADD CONSTRAINT FK_E402F625ECAB15B3 FOREIGN KEY (travel_id) REFERENCES travel (id)');
        $this->addSql('CREATE INDEX IDX_E402F625ECAB15B3 ON hotels (travel_id)');
    }
}
