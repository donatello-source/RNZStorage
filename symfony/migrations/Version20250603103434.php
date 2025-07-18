<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250603103434 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE category (id INT GENERATED BY DEFAULT AS IDENTITY NOT NULL, nazwa VARCHAR(255) NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE company (id INT GENERATED BY DEFAULT AS IDENTITY NOT NULL, nazwa VARCHAR(100) NOT NULL, nip VARCHAR(15) NOT NULL, adres TEXT NOT NULL, telefon VARCHAR(20) DEFAULT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE equipment (id INT GENERATED BY DEFAULT AS IDENTITY NOT NULL, nazwa VARCHAR(100) NOT NULL, opis TEXT DEFAULT NULL, ilosc INT NOT NULL, cena NUMERIC(10, 2) NOT NULL, idkategoria INT DEFAULT NULL, informacje_wycena TEXT DEFAULT NULL, informacje_dodatkowe TEXT DEFAULT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE person (id INT GENERATED BY DEFAULT AS IDENTITY NOT NULL, imie VARCHAR(50) NOT NULL, nazwisko VARCHAR(50) NOT NULL, mail VARCHAR(100) NOT NULL, haslo VARCHAR(255) NOT NULL, stanowisko VARCHAR(20) NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_34DCD1765126AC48 ON person (mail)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE quote (id INT GENERATED BY DEFAULT AS IDENTITY NOT NULL, projekt VARCHAR(255) NOT NULL, lokalizacja VARCHAR(255) NOT NULL, global_discount NUMERIC(5, 2) NOT NULL, status VARCHAR(20) NOT NULL, data_wystawienia DATE NOT NULL, company_id INT NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_6B71CBF4979B1AD6 ON quote (company_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE quote_date (id INT GENERATED BY DEFAULT AS IDENTITY NOT NULL, type VARCHAR(20) NOT NULL, value VARCHAR(100) NOT NULL, comment VARCHAR(255) DEFAULT NULL, quote_id INT NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_387968F0DB805178 ON quote_date (quote_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE quote_table (id INT GENERATED BY DEFAULT AS IDENTITY NOT NULL, label VARCHAR(255) NOT NULL, discount NUMERIC(5, 2) NOT NULL, quote_id INT NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_FBD60227DB805178 ON quote_table (quote_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE quote_table_equipment (id INT GENERATED BY DEFAULT AS IDENTITY NOT NULL, count INT NOT NULL, days INT NOT NULL, discount NUMERIC(5, 2) NOT NULL, show_comment BOOLEAN NOT NULL, quote_table_id INT NOT NULL, equipment_id INT NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_BBD855096C580CD ON quote_table_equipment (quote_table_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_BBD85509517FE9FE ON quote_table_equipment (equipment_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE upload (id INT GENERATED BY DEFAULT AS IDENTITY NOT NULL, file_path VARCHAR(255) NOT NULL, original_name VARCHAR(255) NOT NULL, status VARCHAR(255) NOT NULL, error_message TEXT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, type VARCHAR(10) NOT NULL, parent_id INT DEFAULT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_17BDE61F727ACA70 ON upload (parent_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE quote ADD CONSTRAINT FK_6B71CBF4979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE quote_date ADD CONSTRAINT FK_387968F0DB805178 FOREIGN KEY (quote_id) REFERENCES quote (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE quote_table ADD CONSTRAINT FK_FBD60227DB805178 FOREIGN KEY (quote_id) REFERENCES quote (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE quote_table_equipment ADD CONSTRAINT FK_BBD855096C580CD FOREIGN KEY (quote_table_id) REFERENCES quote_table (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE quote_table_equipment ADD CONSTRAINT FK_BBD85509517FE9FE FOREIGN KEY (equipment_id) REFERENCES equipment (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE upload ADD CONSTRAINT FK_17BDE61F727ACA70 FOREIGN KEY (parent_id) REFERENCES upload (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE quote DROP CONSTRAINT FK_6B71CBF4979B1AD6
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE quote_date DROP CONSTRAINT FK_387968F0DB805178
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE quote_table DROP CONSTRAINT FK_FBD60227DB805178
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE quote_table_equipment DROP CONSTRAINT FK_BBD855096C580CD
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE quote_table_equipment DROP CONSTRAINT FK_BBD85509517FE9FE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE upload DROP CONSTRAINT FK_17BDE61F727ACA70
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE category
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE company
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE equipment
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE person
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE quote
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE quote_date
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE quote_table
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE quote_table_equipment
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE upload
        SQL);
    }
}
