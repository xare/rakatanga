<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220613064227 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE blog ADD lang_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE blog ADD CONSTRAINT FK_C0155143B213FA4 FOREIGN KEY (lang_id) REFERENCES lang (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C0155143B213FA4 ON blog (lang_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE blog DROP FOREIGN KEY FK_C0155143B213FA4');
        $this->addSql('DROP INDEX UNIQ_C0155143B213FA4 ON blog');
        $this->addSql('ALTER TABLE blog DROP lang_id');
    }
}
