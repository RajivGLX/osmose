<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250909121256 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE administrator (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, service VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_58DF0651A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE availability (id INT AUTO_INCREMENT NOT NULL, center_id INT NOT NULL, slot_id INT NOT NULL, date DATETIME NOT NULL, available_place INT NOT NULL, reserved_place INT NOT NULL, INDEX IDX_3FB7A2BF5932F377 (center_id), INDEX IDX_3FB7A2BF59E5119C (slot_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE booking (id INT AUTO_INCREMENT NOT NULL, center_id INT NOT NULL, patient_id INT NOT NULL, availability_id INT NOT NULL, slot_id INT NOT NULL, date_reserve DATETIME NOT NULL, create_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', comment VARCHAR(255) DEFAULT NULL, reason VARCHAR(255) NOT NULL, INDEX IDX_E00CEDDE5932F377 (center_id), INDEX IDX_E00CEDDE6B899279 (patient_id), INDEX IDX_E00CEDDE61778466 (availability_id), INDEX IDX_E00CEDDE59E5119C (slot_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE center (id INT AUTO_INCREMENT NOT NULL, region_id INT NOT NULL, name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, phone VARCHAR(255) NOT NULL, url VARCHAR(255) DEFAULT NULL, band VARCHAR(255) DEFAULT NULL, latitude_longitude VARCHAR(255) DEFAULT NULL, slug VARCHAR(255) NOT NULL, address VARCHAR(500) NOT NULL, city VARCHAR(255) NOT NULL, zipcode DOUBLE PRECISION NOT NULL, place_available INT NOT NULL, information LONGTEXT DEFAULT NULL, different_facturation TINYINT(1) NOT NULL, address_facturation VARCHAR(255) DEFAULT NULL, city_facturation VARCHAR(255) DEFAULT NULL, zipcode_facturation DOUBLE PRECISION DEFAULT NULL, active TINYINT(1) NOT NULL, center_day JSON DEFAULT NULL, deleted TINYINT(1) NOT NULL, INDEX IDX_40F0EB2498260155 (region_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE center_administrator (center_id INT NOT NULL, administrator_id INT NOT NULL, INDEX IDX_F8D206AD5932F377 (center_id), INDEX IDX_F8D206AD4B09E92C (administrator_id), PRIMARY KEY(center_id, administrator_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE facture (id INT AUTO_INCREMENT NOT NULL, center_id INT NOT NULL, number VARCHAR(255) NOT NULL, mean_of_payment VARCHAR(255) NOT NULL, amount_excluding_taxes DOUBLE PRECISION NOT NULL, amount_all_charges DOUBLE PRECISION NOT NULL, type VARCHAR(255) NOT NULL, state TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_FE8664105932F377 (center_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE patient (id INT AUTO_INCREMENT NOT NULL, center_id INT DEFAULT NULL, user_id INT DEFAULT NULL, phone VARCHAR(255) DEFAULT NULL, medical_history VARCHAR(255) DEFAULT NULL, checked TINYINT(1) NOT NULL, type_dialysis VARCHAR(255) DEFAULT NULL, drug_allergies TINYINT(1) DEFAULT NULL, drug_allergie_precise VARCHAR(255) DEFAULT NULL, dialysis_start_date DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', vascular_access_type VARCHAR(255) DEFAULT NULL, INDEX IDX_1ADAD7EB5932F377 (center_id), UNIQUE INDEX UNIQ_1ADAD7EBA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE region (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_F62F1765E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reset_password (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, token VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_B9983CE5A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reset_password_request (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, selector VARCHAR(20) NOT NULL, hashed_token VARCHAR(100) NOT NULL, requested_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', expires_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_7CE748AA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE role (id INT AUTO_INCREMENT NOT NULL, role_name VARCHAR(100) NOT NULL, libelle VARCHAR(100) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE slots (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, first TINYINT(1) NOT NULL, second TINYINT(1) NOT NULL, third TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE status (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, color VARCHAR(255) DEFAULT NULL, bg_color VARCHAR(255) DEFAULT NULL, status_wait TINYINT(1) NOT NULL, status_confirm TINYINT(1) NOT NULL, status_denied TINYINT(1) NOT NULL, status_contact TINYINT(1) NOT NULL, status_canceled TINYINT(1) NOT NULL, status_finish TINYINT(1) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE status_booking (id INT AUTO_INCREMENT NOT NULL, status_id INT NOT NULL, booking_id INT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', status_active TINYINT(1) NOT NULL, INDEX IDX_E8DC9A5B6BF700BD (status_id), INDEX IDX_E8DC9A5B3301C60 (booking_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, roles JSON NOT NULL, email VARCHAR(100) NOT NULL, valid TINYINT(1) NOT NULL, deleted TINYINT(1) NOT NULL, `admin` TINYINT(1) NOT NULL, password VARCHAR(255) NOT NULL, firstname VARCHAR(255) NOT NULL, lastname VARCHAR(255) NOT NULL, create_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE administrator ADD CONSTRAINT FK_58DF0651A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE availability ADD CONSTRAINT FK_3FB7A2BF5932F377 FOREIGN KEY (center_id) REFERENCES center (id)');
        $this->addSql('ALTER TABLE availability ADD CONSTRAINT FK_3FB7A2BF59E5119C FOREIGN KEY (slot_id) REFERENCES slots (id)');
        $this->addSql('ALTER TABLE booking ADD CONSTRAINT FK_E00CEDDE5932F377 FOREIGN KEY (center_id) REFERENCES center (id)');
        $this->addSql('ALTER TABLE booking ADD CONSTRAINT FK_E00CEDDE6B899279 FOREIGN KEY (patient_id) REFERENCES patient (id)');
        $this->addSql('ALTER TABLE booking ADD CONSTRAINT FK_E00CEDDE61778466 FOREIGN KEY (availability_id) REFERENCES availability (id)');
        $this->addSql('ALTER TABLE booking ADD CONSTRAINT FK_E00CEDDE59E5119C FOREIGN KEY (slot_id) REFERENCES slots (id)');
        $this->addSql('ALTER TABLE center ADD CONSTRAINT FK_40F0EB2498260155 FOREIGN KEY (region_id) REFERENCES region (id)');
        $this->addSql('ALTER TABLE center_administrator ADD CONSTRAINT FK_F8D206AD5932F377 FOREIGN KEY (center_id) REFERENCES center (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE center_administrator ADD CONSTRAINT FK_F8D206AD4B09E92C FOREIGN KEY (administrator_id) REFERENCES administrator (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE facture ADD CONSTRAINT FK_FE8664105932F377 FOREIGN KEY (center_id) REFERENCES center (id)');
        $this->addSql('ALTER TABLE patient ADD CONSTRAINT FK_1ADAD7EB5932F377 FOREIGN KEY (center_id) REFERENCES center (id)');
        $this->addSql('ALTER TABLE patient ADD CONSTRAINT FK_1ADAD7EBA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE reset_password ADD CONSTRAINT FK_B9983CE5A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE reset_password_request ADD CONSTRAINT FK_7CE748AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE status_booking ADD CONSTRAINT FK_E8DC9A5B6BF700BD FOREIGN KEY (status_id) REFERENCES status (id)');
        $this->addSql('ALTER TABLE status_booking ADD CONSTRAINT FK_E8DC9A5B3301C60 FOREIGN KEY (booking_id) REFERENCES booking (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE administrator DROP FOREIGN KEY FK_58DF0651A76ED395');
        $this->addSql('ALTER TABLE availability DROP FOREIGN KEY FK_3FB7A2BF5932F377');
        $this->addSql('ALTER TABLE availability DROP FOREIGN KEY FK_3FB7A2BF59E5119C');
        $this->addSql('ALTER TABLE booking DROP FOREIGN KEY FK_E00CEDDE5932F377');
        $this->addSql('ALTER TABLE booking DROP FOREIGN KEY FK_E00CEDDE6B899279');
        $this->addSql('ALTER TABLE booking DROP FOREIGN KEY FK_E00CEDDE61778466');
        $this->addSql('ALTER TABLE booking DROP FOREIGN KEY FK_E00CEDDE59E5119C');
        $this->addSql('ALTER TABLE center DROP FOREIGN KEY FK_40F0EB2498260155');
        $this->addSql('ALTER TABLE center_administrator DROP FOREIGN KEY FK_F8D206AD5932F377');
        $this->addSql('ALTER TABLE center_administrator DROP FOREIGN KEY FK_F8D206AD4B09E92C');
        $this->addSql('ALTER TABLE facture DROP FOREIGN KEY FK_FE8664105932F377');
        $this->addSql('ALTER TABLE patient DROP FOREIGN KEY FK_1ADAD7EB5932F377');
        $this->addSql('ALTER TABLE patient DROP FOREIGN KEY FK_1ADAD7EBA76ED395');
        $this->addSql('ALTER TABLE reset_password DROP FOREIGN KEY FK_B9983CE5A76ED395');
        $this->addSql('ALTER TABLE reset_password_request DROP FOREIGN KEY FK_7CE748AA76ED395');
        $this->addSql('ALTER TABLE status_booking DROP FOREIGN KEY FK_E8DC9A5B6BF700BD');
        $this->addSql('ALTER TABLE status_booking DROP FOREIGN KEY FK_E8DC9A5B3301C60');
        $this->addSql('DROP TABLE administrator');
        $this->addSql('DROP TABLE availability');
        $this->addSql('DROP TABLE booking');
        $this->addSql('DROP TABLE center');
        $this->addSql('DROP TABLE center_administrator');
        $this->addSql('DROP TABLE facture');
        $this->addSql('DROP TABLE patient');
        $this->addSql('DROP TABLE region');
        $this->addSql('DROP TABLE reset_password');
        $this->addSql('DROP TABLE reset_password_request');
        $this->addSql('DROP TABLE role');
        $this->addSql('DROP TABLE slots');
        $this->addSql('DROP TABLE status');
        $this->addSql('DROP TABLE status_booking');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
