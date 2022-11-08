<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220729072552 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE oldreservations ADD travel_id INT DEFAULT NULL, ADD dates_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE oldreservations ADD CONSTRAINT FK_D29F4F03ECAB15B3 FOREIGN KEY (travel_id) REFERENCES travel (id)');
        $this->addSql('ALTER TABLE oldreservations ADD CONSTRAINT FK_D29F4F033DA992C3 FOREIGN KEY (dates_id) REFERENCES dates (id)');
        $this->addSql('CREATE INDEX IDX_D29F4F03ECAB15B3 ON oldreservations (travel_id)');
        $this->addSql('CREATE INDEX IDX_D29F4F033DA992C3 ON oldreservations (dates_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE oldreservations DROP FOREIGN KEY FK_D29F4F03ECAB15B3');
        $this->addSql('ALTER TABLE oldreservations DROP FOREIGN KEY FK_D29F4F033DA992C3');
        $this->addSql('DROP INDEX IDX_D29F4F03ECAB15B3 ON oldreservations');
        $this->addSql('DROP INDEX IDX_D29F4F033DA992C3 ON oldreservations');
        $this->addSql('ALTER TABLE oldreservations DROP travel_id, DROP dates_id');
    }
}
