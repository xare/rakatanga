<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220607082602 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE codespromo DROP FOREIGN KEY FK_2C2846FAA76ED395');
        $this->addSql('ALTER TABLE codespromo ADD nombre_total INT DEFAULT NULL');
        $this->addSql('ALTER TABLE codespromo ADD CONSTRAINT FK_2C2846FAA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE invoices DROP FOREIGN KEY FK_6A2F2F95B83297E7');
        $this->addSql('ALTER TABLE invoices ADD CONSTRAINT FK_6A2F2F95B83297E7 FOREIGN KEY (reservation_id) REFERENCES reservation (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C84955A76ED395');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C84955A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE codespromo DROP FOREIGN KEY FK_2C2846FAA76ED395');
        $this->addSql('ALTER TABLE codespromo DROP nombre_total');
        $this->addSql('ALTER TABLE codespromo ADD CONSTRAINT FK_2C2846FAA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE invoices DROP FOREIGN KEY FK_6A2F2F95B83297E7');
        $this->addSql('ALTER TABLE invoices ADD CONSTRAINT FK_6A2F2F95B83297E7 FOREIGN KEY (reservation_id) REFERENCES reservation (id)');
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C84955A76ED395');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C84955A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }
}
