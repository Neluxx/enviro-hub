<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251026154118 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Make "carbon_dioxide" nullable in "environmental_data" table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE environmental_data CHANGE carbon_dioxide carbon_dioxide DOUBLE PRECISION NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE environmental_data CHANGE carbon_dioxide carbon_dioxide DOUBLE PRECISION NOT NULL');
    }
}
