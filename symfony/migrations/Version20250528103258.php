<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250528103258 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE upload ADD type VARCHAR(10) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE upload ADD parent_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE upload ADD CONSTRAINT FK_17BDE61F727ACA70 FOREIGN KEY (parent_id) REFERENCES upload (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_17BDE61F727ACA70 ON upload (parent_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE upload DROP CONSTRAINT FK_17BDE61F727ACA70
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_17BDE61F727ACA70
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE upload DROP type
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE upload DROP parent_id
        SQL);
    }
}
