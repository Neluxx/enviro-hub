<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250118153504 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rename "co2" to "carbon_dioxide" in "environmental_data" table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE environmental_data CHANGE co2 carbon_dioxide DOUBLE PRECISION NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE environmental_data CHANGE carbon_dioxide co2 DOUBLE PRECISION NOT NULL');
    }
}
