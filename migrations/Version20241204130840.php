<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241204130840 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create environmental data table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE environmental_data (id INT AUTO_INCREMENT NOT NULL, temperature DOUBLE PRECISION NOT NULL, humidity DOUBLE PRECISION NOT NULL, pressure DOUBLE PRECISION NOT NULL, co2 DOUBLE PRECISION NOT NULL, measured_at DATETIME NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE environmental_data');
    }
}
