<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200306090033 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('CREATE TABLE exchange_rate (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, from_currency CHAR(3) NOT NULL, to_currency CHAR(3) NOT NULL, rate DOUBLE PRECISION NOT NULL, datetime DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , src VARCHAR(255) NOT NULL)');
    }

    public function down(Schema $schema) : void
    {
    }
}
