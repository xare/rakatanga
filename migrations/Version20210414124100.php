<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210414124100 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE actualites (id INT UNSIGNED AUTO_INCREMENT NOT NULL, partenaire VARCHAR(255) NOT NULL, titre VARCHAR(255) NOT NULL COMMENT \'Titre\', url VARCHAR(255) NOT NULL, introduction TEXT NOT NULL, texte TEXT NOT NULL, categorie VARCHAR(15) NOT NULL, galerie VARCHAR(3) DEFAULT \'non\' NOT NULL, statut VARCHAR(3) NOT NULL, auteurid INT UNSIGNED NOT NULL, auteurnom VARCHAR(150) NOT NULL, datepublication DATETIME DEFAULT NULL, dateexpiration DATETIME DEFAULT NULL, date_ajout DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE carnet (id INT UNSIGNED AUTO_INCREMENT NOT NULL, titre_fr VARCHAR(255) NOT NULL, titre_es VARCHAR(255) NOT NULL, titre_en VARCHAR(255) NOT NULL, texte_fr TEXT NOT NULL, texte_es TEXT NOT NULL, texte_en TEXT NOT NULL, lien VARCHAR(255) NOT NULL, image VARCHAR(255) NOT NULL, date_publication DATE DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE categories (id INT UNSIGNED AUTO_INCREMENT NOT NULL, continent VARCHAR(10) NOT NULL, libelle_fr VARCHAR(255) NOT NULL, libelle_es VARCHAR(255) NOT NULL, libelle_en VARCHAR(255) NOT NULL, libelle_de VARCHAR(255) NOT NULL, libelle_it VARCHAR(255) NOT NULL, libelle_tr VARCHAR(255) NOT NULL, url_fr VARCHAR(255) NOT NULL, url_es VARCHAR(255) NOT NULL, url_en VARCHAR(255) NOT NULL, url_de VARCHAR(255) NOT NULL, url_it VARCHAR(255) NOT NULL, url_tr VARCHAR(255) NOT NULL, intro_fr TEXT NOT NULL, intro_es TEXT NOT NULL, intro_en TEXT NOT NULL, intro_de TEXT NOT NULL, intro_it TEXT NOT NULL, intro_tr TEXT NOT NULL, statut VARCHAR(3) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE clients (id INT UNSIGNED AUTO_INCREMENT NOT NULL, nom VARCHAR(100) NOT NULL, prenom VARCHAR(100) NOT NULL, adresse VARCHAR(255) NOT NULL, codepostal VARCHAR(25) NOT NULL, ville VARCHAR(100) NOT NULL, pays VARCHAR(2) NOT NULL, telephone VARCHAR(25) NOT NULL, email VARCHAR(255) NOT NULL, mdp VARCHAR(100) NOT NULL, nationalite VARCHAR(2) NOT NULL, sexe VARCHAR(1) NOT NULL, date_naissance DATE NOT NULL, profession VARCHAR(100) NOT NULL, numero_passeport VARCHAR(50) NOT NULL, date_passeport DATE NOT NULL, numero_visa VARCHAR(50) NOT NULL, date_visa DATE NOT NULL, inscription DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE codespromo (id INT UNSIGNED AUTO_INCREMENT NOT NULL, code VARCHAR(10) NOT NULL, libelle VARCHAR(100) NOT NULL, commentaire VARCHAR(255) NOT NULL, montant NUMERIC(10, 2) NOT NULL, pourcentage INT UNSIGNED NOT NULL, type VARCHAR(15) NOT NULL, nombre INT UNSIGNED NOT NULL, debut DATE DEFAULT NULL, fin DATE DEFAULT NULL, email VARCHAR(255) NOT NULL, statut VARCHAR(3) NOT NULL, date_ajout DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE connexions (id INT UNSIGNED AUTO_INCREMENT NOT NULL, site VARCHAR(15) NOT NULL, email VARCHAR(255) NOT NULL, mdp VARCHAR(50) NOT NULL, statut VARCHAR(10) NOT NULL, date DATETIME DEFAULT NULL, adresseip VARCHAR(15) NOT NULL, hostname VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE continents (code VARCHAR(2) NOT NULL, nom_fr VARCHAR(255) NOT NULL, nom_es VARCHAR(255) NOT NULL, PRIMARY KEY(code)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE dates (id INT AUTO_INCREMENT NOT NULL, voyage INT UNSIGNED NOT NULL, debut DATE NOT NULL, fin DATE NOT NULL, prix_motard NUMERIC(10, 2) NOT NULL, prix_accomp NUMERIC(10, 2) NOT NULL, statut VARCHAR(15) NOT NULL, INDEX voyage (voyage), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE equipe (id INT UNSIGNED AUTO_INCREMENT NOT NULL, categorie INT UNSIGNED NOT NULL, nom VARCHAR(100) NOT NULL, prenom VARCHAR(100) NOT NULL, fonction VARCHAR(50) NOT NULL, email VARCHAR(255) NOT NULL, telephone VARCHAR(25) NOT NULL, extranet VARCHAR(25) NOT NULL, acces VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE etapes (id INT UNSIGNED AUTO_INCREMENT NOT NULL, date DATE DEFAULT NULL, pays VARCHAR(50) NOT NULL, titre_fr VARCHAR(255) NOT NULL, titre_es VARCHAR(255) NOT NULL, titre_en VARCHAR(255) NOT NULL, commentaire_fr TEXT NOT NULL, commentaire_es TEXT NOT NULL, commentaire_en TEXT NOT NULL, km INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE faq_categories (id INT UNSIGNED AUTO_INCREMENT NOT NULL, ordre INT UNSIGNED NOT NULL, libelle_fr VARCHAR(100) NOT NULL, libelle_en VARCHAR(100) NOT NULL, libelle_es VARCHAR(100) NOT NULL, libelle_de VARCHAR(100) NOT NULL, libelle_it VARCHAR(100) NOT NULL, libelle_tr VARCHAR(100) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE faq_questions (id INT UNSIGNED AUTO_INCREMENT NOT NULL, categorie INT UNSIGNED NOT NULL, ordre INT UNSIGNED NOT NULL, question_fr VARCHAR(255) NOT NULL, question_en VARCHAR(255) NOT NULL, question_es VARCHAR(255) NOT NULL, question_de VARCHAR(255) NOT NULL, question_it VARCHAR(255) NOT NULL, question_tr VARCHAR(255) NOT NULL, reponse_fr TEXT NOT NULL, reponse_en TEXT NOT NULL, reponse_es TEXT NOT NULL, reponse_de TEXT NOT NULL, reponse_it TEXT NOT NULL, reponse_tr TEXT NOT NULL, date_modification DATETIME DEFAULT NULL, INDEX categorie (categorie), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE galeries (id INT UNSIGNED AUTO_INCREMENT NOT NULL, ordre INT UNSIGNED NOT NULL, titre_fr VARCHAR(255) NOT NULL, titre_es VARCHAR(255) NOT NULL, titre_en VARCHAR(255) NOT NULL, dossier VARCHAR(50) NOT NULL, description_fr TEXT NOT NULL, description_es TEXT NOT NULL, description_en TEXT NOT NULL, date DATE DEFAULT NULL, visible CHAR(3) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE inscriptions (id INT UNSIGNED AUTO_INCREMENT NOT NULL, date INT UNSIGNED NOT NULL, reservation INT UNSIGNED NOT NULL, langue VARCHAR(2) NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, telephone VARCHAR(25) NOT NULL, email VARCHAR(255) NOT NULL, position VARCHAR(15) NOT NULL, arrhes INT UNSIGNED NOT NULL, solde INT UNSIGNED NOT NULL, assurance VARCHAR(3) NOT NULL, vols VARCHAR(3) NOT NULL, statut VARCHAR(15) NOT NULL, remarque TEXT NOT NULL, date_ajout DATETIME DEFAULT NULL, INDEX client (reservation), INDEX date (date), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mailings (id INT UNSIGNED AUTO_INCREMENT NOT NULL, sujet VARCHAR(255) NOT NULL, intro TEXT NOT NULL, texte TEXT NOT NULL, auteur INT UNSIGNED NOT NULL, date DATETIME DEFAULT NULL, INDEX auteur (auteur), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE onglets (url VARCHAR(50) NOT NULL, libelle VARCHAR(50) NOT NULL, PRIMARY KEY(url)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE options (id INT UNSIGNED AUTO_INCREMENT NOT NULL, libelle_fr VARCHAR(255) NOT NULL, libelle_es VARCHAR(255) NOT NULL, libelle_en VARCHAR(255) NOT NULL, libelle_de VARCHAR(255) NOT NULL, libelle_it VARCHAR(255) NOT NULL, intro_fr TEXT NOT NULL, intro_es TEXT NOT NULL, intro_en TEXT NOT NULL, intro_de TEXT NOT NULL, intro_it TEXT NOT NULL, prix NUMERIC(10, 2) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE options2 (id INT UNSIGNED AUTO_INCREMENT NOT NULL, voyage INT UNSIGNED NOT NULL, libelle_fr VARCHAR(255) NOT NULL, libelle_es VARCHAR(255) NOT NULL, libelle_en VARCHAR(255) NOT NULL, libelle_de VARCHAR(255) NOT NULL, libelle_it VARCHAR(255) NOT NULL, intro_fr TEXT NOT NULL, intro_es TEXT NOT NULL, intro_en TEXT NOT NULL, intro_de TEXT NOT NULL, intro_it TEXT NOT NULL, prix NUMERIC(10, 2) NOT NULL, INDEX voyages (voyage), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE paiements (id INT UNSIGNED AUTO_INCREMENT NOT NULL, reservations INT UNSIGNED NOT NULL, montant NUMERIC(10, 2) NOT NULL, commentaire VARCHAR(255) NOT NULL, date DATETIME DEFAULT NULL, INDEX reservations (reservations), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reservations (id INT AUTO_INCREMENT NOT NULL, date INT UNSIGNED NOT NULL, langue VARCHAR(2) NOT NULL, nbpilotes INT UNSIGNED NOT NULL, nbpassagers INT UNSIGNED NOT NULL, commentaire TEXT NOT NULL, log TEXT NOT NULL, codepromo VARCHAR(10) NOT NULL, montant NUMERIC(10, 2) NOT NULL, reduction NUMERIC(10, 2) NOT NULL, totalttc NUMERIC(10, 2) NOT NULL, notes TEXT NOT NULL, statut VARCHAR(15) NOT NULL, origine_ajout VARCHAR(50) NOT NULL, date_ajout DATETIME DEFAULT NULL, date_paiement_1 DATETIME DEFAULT NULL, date_paiement_2 DATETIME DEFAULT NULL, INDEX date (date), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE textes (id VARCHAR(15) NOT NULL, section VARCHAR(100) NOT NULL, titre_fr VARCHAR(255) NOT NULL, texte_fr TEXT NOT NULL, texte_en TEXT NOT NULL, texte_es TEXT NOT NULL, texte_de TEXT NOT NULL, texte_it TEXT NOT NULL, texte_tr TEXT NOT NULL, date DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', password VARCHAR(255) NOT NULL, langue VARCHAR(2) NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, telephone VARCHAR(25) DEFAULT NULL, position VARCHAR(15) DEFAULT NULL, arrhes INT NOT NULL, solde INT DEFAULT NULL, assurance VARCHAR(3) DEFAULT NULL, vols VARCHAR(3) DEFAULT NULL, statut VARCHAR(15) DEFAULT NULL, remarque VARCHAR(255) DEFAULT NULL, date_ajout DATETIME NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_dates (user_id INT NOT NULL, dates_id INT NOT NULL, INDEX IDX_49B63246A76ED395 (user_id), INDEX IDX_49B632463DA992C3 (dates_id), PRIMARY KEY(user_id, dates_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_reservations (user_id INT NOT NULL, reservations_id INT NOT NULL, INDEX IDX_8081DE3BA76ED395 (user_id), INDEX IDX_8081DE3BD9A7F869 (reservations_id), PRIMARY KEY(user_id, reservations_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE voyages (id INT UNSIGNED AUTO_INCREMENT NOT NULL, categorie INT UNSIGNED NOT NULL, duree INT UNSIGNED NOT NULL, niveau VARCHAR(3) NOT NULL, titre_fr VARCHAR(255) NOT NULL, titre_es VARCHAR(255) NOT NULL, titre_en VARCHAR(255) NOT NULL, titre_de VARCHAR(255) NOT NULL, titre_it VARCHAR(255) NOT NULL, titre_tr VARCHAR(255) NOT NULL, url_fr VARCHAR(255) NOT NULL, url_es VARCHAR(255) NOT NULL, url_en VARCHAR(255) NOT NULL, url_de VARCHAR(255) NOT NULL, url_it VARCHAR(255) NOT NULL, url_tr VARCHAR(255) NOT NULL, resume_fr TEXT NOT NULL, resume_es TEXT NOT NULL, resume_en TEXT NOT NULL, resume_de TEXT NOT NULL, resume_it TEXT NOT NULL, resume_tr TEXT NOT NULL, intro_fr TEXT NOT NULL, intro_es TEXT NOT NULL, intro_en TEXT NOT NULL, intro_de TEXT NOT NULL, intro_it TEXT NOT NULL, intro_tr TEXT NOT NULL, description_fr TEXT NOT NULL, description_es TEXT NOT NULL, description_en TEXT NOT NULL, description_de TEXT NOT NULL, description_it TEXT NOT NULL, description_tr TEXT NOT NULL, itineraire_fr TEXT NOT NULL, itineraire_es TEXT NOT NULL, itineraire_en TEXT NOT NULL, itineraire_de TEXT NOT NULL, itineraire_it TEXT NOT NULL, itineraire_tr TEXT NOT NULL, pratique_fr TEXT NOT NULL, pratique_es TEXT NOT NULL, pratique_en TEXT NOT NULL, pratique_de TEXT NOT NULL, pratique_it TEXT NOT NULL, pratique_tr TEXT NOT NULL, options VARCHAR(255) NOT NULL, statut VARCHAR(3) NOT NULL, date DATETIME DEFAULT NULL, INDEX categorie (categorie), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_dates ADD CONSTRAINT FK_49B63246A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_dates ADD CONSTRAINT FK_49B632463DA992C3 FOREIGN KEY (dates_id) REFERENCES dates (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_reservations ADD CONSTRAINT FK_8081DE3BA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_reservations ADD CONSTRAINT FK_8081DE3BD9A7F869 FOREIGN KEY (reservations_id) REFERENCES reservations (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_dates DROP FOREIGN KEY FK_49B632463DA992C3');
        $this->addSql('ALTER TABLE user_reservations DROP FOREIGN KEY FK_8081DE3BD9A7F869');
        $this->addSql('ALTER TABLE user_dates DROP FOREIGN KEY FK_49B63246A76ED395');
        $this->addSql('ALTER TABLE user_reservations DROP FOREIGN KEY FK_8081DE3BA76ED395');
        $this->addSql('DROP TABLE actualites');
        $this->addSql('DROP TABLE carnet');
        $this->addSql('DROP TABLE categories');
        $this->addSql('DROP TABLE clients');
        $this->addSql('DROP TABLE codespromo');
        $this->addSql('DROP TABLE connexions');
        $this->addSql('DROP TABLE continents');
        $this->addSql('DROP TABLE dates');
        $this->addSql('DROP TABLE equipe');
        $this->addSql('DROP TABLE etapes');
        $this->addSql('DROP TABLE faq_categories');
        $this->addSql('DROP TABLE faq_questions');
        $this->addSql('DROP TABLE galeries');
        $this->addSql('DROP TABLE inscriptions');
        $this->addSql('DROP TABLE mailings');
        $this->addSql('DROP TABLE onglets');
        $this->addSql('DROP TABLE options');
        $this->addSql('DROP TABLE options2');
        $this->addSql('DROP TABLE paiements');
        $this->addSql('DROP TABLE reservations');
        $this->addSql('DROP TABLE textes');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE user_dates');
        $this->addSql('DROP TABLE user_reservations');
        $this->addSql('DROP TABLE voyages');
    }
}
