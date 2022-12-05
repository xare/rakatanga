<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221111093130 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE codespromo_reservation');
        $this->addSql('ALTER TABLE codespromo ADD reservations_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE codespromo ADD CONSTRAINT FK_2C2846FAD9A7F869 FOREIGN KEY (reservations_id) REFERENCES reservation (id)');
        $this->addSql('CREATE INDEX IDX_2C2846FAD9A7F869 ON codespromo (reservations_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE codespromo_reservation (codespromo_id INT UNSIGNED NOT NULL, reservation_id INT NOT NULL, INDEX IDX_B47D1CE14D847363 (codespromo_id), INDEX IDX_B47D1CE1B83297E7 (reservation_id), PRIMARY KEY(codespromo_id, reservation_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE codespromo_reservation ADD CONSTRAINT FK_B47D1CE14D847363 FOREIGN KEY (codespromo_id) REFERENCES codespromo (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE codespromo_reservation ADD CONSTRAINT FK_B47D1CE1B83297E7 FOREIGN KEY (reservation_id) REFERENCES reservation (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE codespromo DROP FOREIGN KEY FK_2C2846FAD9A7F869');
        $this->addSql('DROP INDEX IDX_2C2846FAD9A7F869 ON codespromo');
        $this->addSql('ALTER TABLE codespromo DROP reservations_id');
    }
}
