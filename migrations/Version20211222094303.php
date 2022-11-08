<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211222094303 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reservation_data ADD insurance_company VARCHAR(255) DEFAULT NULL, ADD insurance_contract_number VARCHAR(255) DEFAULT NULL, ADD abroad_phone_number VARCHAR(255) DEFAULT NULL, ADD contact_person_name VARCHAR(255) DEFAULT NULL, ADD contact_person_phone VARCHAR(255) DEFAULT NULL, ADD flight_number VARCHAR(255) DEFAULT NULL, ADD flight_arrival DATETIME DEFAULT NULL, ADD flight_arrival_airport VARCHAR(255) DEFAULT NULL, ADD arrival_hotel VARCHAR(255) DEFAULT NULL, ADD flight_departure_number VARCHAR(255) DEFAULT NULL, ADD flight_departure DATETIME DEFAULT NULL, ADD flight_departure_airport VARCHAR(255) DEFAULT NULL, ADD departure_hotel VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reservation_data DROP insurance_company, DROP insurance_contract_number, DROP abroad_phone_number, DROP contact_person_name, DROP contact_person_phone, DROP flight_number, DROP flight_arrival, DROP flight_arrival_airport, DROP arrival_hotel, DROP flight_departure_number, DROP flight_departure, DROP flight_departure_airport, DROP departure_hotel');
    }
}
