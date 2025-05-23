<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250331065054 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE category (id INT GENERATED BY DEFAULT AS IDENTITY NOT NULL, nazwa VARCHAR(100) NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE company (id INT GENERATED BY DEFAULT AS IDENTITY NOT NULL, nazwa VARCHAR(100) NOT NULL, nip VARCHAR(15) NOT NULL, adres TEXT NOT NULL, telefon VARCHAR(20) DEFAULT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE equipment (id INT GENERATED BY DEFAULT AS IDENTITY NOT NULL, nazwa VARCHAR(100) NOT NULL, opis TEXT DEFAULT NULL, ilosc INT NOT NULL, cena NUMERIC(10, 2) NOT NULL, idkategoria INT DEFAULT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE person (id INT GENERATED BY DEFAULT AS IDENTITY NOT NULL, imie VARCHAR(50) NOT NULL, nazwisko VARCHAR(50) NOT NULL, mail VARCHAR(100) NOT NULL, haslo VARCHAR(255) NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_34DCD1765126AC48 ON person (mail)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE quote (id INT GENERATED BY DEFAULT AS IDENTITY NOT NULL, company INT NOT NULL, dodatkowe_informacje TEXT DEFAULT NULL, status VARCHAR(20) NOT NULL, data_wystawienia TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, data_poczatek DATE NOT NULL, data_koniec DATE NOT NULL, dane_kontaktowe TEXT NOT NULL, miejsce TEXT NOT NULL, rabat NUMERIC(5, 2) NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE quote_equipment (ilosc INT NOT NULL, rabat NUMERIC(5, 2) NOT NULL, idQuote INT NOT NULL, idEquipment INT NOT NULL, PRIMARY KEY(idQuote, idEquipment))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_54DA4CF97CB588E9 ON quote_equipment (idQuote)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_54DA4CF92FD2FED5 ON quote_equipment (idEquipment)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE quote_equipment ADD CONSTRAINT FK_54DA4CF97CB588E9 FOREIGN KEY (idQuote) REFERENCES quote (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE quote_equipment ADD CONSTRAINT FK_54DA4CF92FD2FED5 FOREIGN KEY (idEquipment) REFERENCES equipment (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE quote_equipment DROP CONSTRAINT FK_54DA4CF97CB588E9
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE quote_equipment DROP CONSTRAINT FK_54DA4CF92FD2FED5
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
            DROP TABLE quote_equipment
        SQL);
    }
}
