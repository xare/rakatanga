<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220803082909 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mailings ADD reservation_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE mailings ADD CONSTRAINT FK_97E36AC9B83297E7 FOREIGN KEY (reservation_id) REFERENCES reservation (id)');
        $this->addSql('CREATE INDEX IDX_97E36AC9B83297E7 ON mailings (reservation_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mailings DROP FOREIGN KEY FK_97E36AC9B83297E7');
        $this->addSql('DROP INDEX IDX_97E36AC9B83297E7 ON mailings');
        $this->addSql('ALTER TABLE mailings DROP reservation_id');
    }
}
