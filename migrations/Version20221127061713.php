<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221127061713 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE invoices DROP INDEX IDX_6A2F2F95B83297E7, ADD UNIQUE INDEX UNIQ_6A2F2F95B83297E7 (reservation_id)');
        $this->addSql('ALTER TABLE invoices DROP FOREIGN KEY FK_6A2F2F95B83297E7');
        $this->addSql('ALTER TABLE invoices ADD CONSTRAINT FK_6A2F2F95B83297E7 FOREIGN KEY (reservation_id) REFERENCES reservation (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE invoices DROP INDEX UNIQ_6A2F2F95B83297E7, ADD INDEX IDX_6A2F2F95B83297E7 (reservation_id)');
        $this->addSql('ALTER TABLE invoices DROP FOREIGN KEY FK_6A2F2F95B83297E7');
        $this->addSql('ALTER TABLE invoices ADD CONSTRAINT FK_6A2F2F95B83297E7 FOREIGN KEY (reservation_id) REFERENCES reservation (id) ON DELETE SET NULL');
    }
}
