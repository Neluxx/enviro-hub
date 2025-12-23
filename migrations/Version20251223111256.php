<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251223111256 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Deletes the open_weather_data table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DROP TABLE open_weather_data');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TABLE open_weather_data (
            id INT AUTO_INCREMENT NOT NULL,
            weather_main VARCHAR(255) NOT NULL,
            weather_description VARCHAR(255) NOT NULL,
            weather_icon VARCHAR(50) NOT NULL,
            temperature DOUBLE PRECISION NOT NULL,
            feels_like DOUBLE PRECISION NOT NULL,
            temp_min DOUBLE PRECISION NOT NULL,
            temp_max DOUBLE PRECISION NOT NULL,
            pressure INT NOT NULL,
            humidity INT NOT NULL,
            visibility INT NOT NULL,
            wind_speed DOUBLE PRECISION NOT NULL,
            wind_direction INT NOT NULL,
            cloudiness INT NOT NULL,
            timestamp DATETIME NOT NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }
}
