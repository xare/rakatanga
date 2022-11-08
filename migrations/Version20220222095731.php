<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220222095731 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE codespromo ADD user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE codespromo ADD CONSTRAINT FK_2C2846FAA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_2C2846FAA76ED395 ON codespromo (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE codespromo DROP FOREIGN KEY FK_2C2846FAA76ED395');
        $this->addSql('DROP INDEX IDX_2C2846FAA76ED395 ON codespromo');
        $this->addSql('ALTER TABLE codespromo DROP user_id');
    }
}
