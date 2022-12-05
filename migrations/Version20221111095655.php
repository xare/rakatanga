<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221111095655 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE codespromo DROP FOREIGN KEY FK_2C2846FAB83297E7');
        $this->addSql('DROP INDEX IDX_2C2846FAB83297E7 ON codespromo');
        $this->addSql('ALTER TABLE codespromo DROP reservation_id');
        $this->addSql('ALTER TABLE reservation ADD codespromo_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C849554D847363 FOREIGN KEY (codespromo_id) REFERENCES codespromo (id)');
        $this->addSql('CREATE INDEX IDX_42C849554D847363 ON reservation (codespromo_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE codespromo ADD reservation_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE codespromo ADD CONSTRAINT FK_2C2846FAB83297E7 FOREIGN KEY (reservation_id) REFERENCES reservation (id)');
        $this->addSql('CREATE INDEX IDX_2C2846FAB83297E7 ON codespromo (reservation_id)');
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C849554D847363');
        $this->addSql('DROP INDEX IDX_42C849554D847363 ON reservation');
        $this->addSql('ALTER TABLE reservation DROP codespromo_id');
    }
}
