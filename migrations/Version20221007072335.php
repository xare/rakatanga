<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221007072335 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mailings DROP FOREIGN KEY FK_97E36AC9B83297E7');
        $this->addSql('ALTER TABLE mailings ADD CONSTRAINT FK_97E36AC9B83297E7 FOREIGN KEY (reservation_id) REFERENCES reservation (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mailings DROP FOREIGN KEY FK_97E36AC9B83297E7');
        $this->addSql('ALTER TABLE mailings ADD CONSTRAINT FK_97E36AC9B83297E7 FOREIGN KEY (reservation_id) REFERENCES reservation (id)');
    }
}
