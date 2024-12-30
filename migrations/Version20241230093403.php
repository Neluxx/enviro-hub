<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241230093403 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create OpenWeather data table';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE open_weather_data (id INT AUTO_INCREMENT NOT NULL, longitude DOUBLE PRECISION DEFAULT NULL, latitude DOUBLE PRECISION DEFAULT NULL, weather_main VARCHAR(255) DEFAULT NULL, weather_description VARCHAR(255) DEFAULT NULL, weather_icon VARCHAR(50) DEFAULT NULL, temperature DOUBLE PRECISION DEFAULT NULL, feels_like DOUBLE PRECISION DEFAULT NULL, temp_min DOUBLE PRECISION DEFAULT NULL, temp_max DOUBLE PRECISION DEFAULT NULL, pressure INT DEFAULT NULL, humidity INT DEFAULT NULL, visibility INT DEFAULT NULL, wind_speed DOUBLE PRECISION DEFAULT NULL, wind_direction INT DEFAULT NULL, cloudiness INT DEFAULT NULL, timestamp DATETIME DEFAULT NULL, country VARCHAR(3) DEFAULT NULL, sunrise DATETIME DEFAULT NULL, sunset DATETIME DEFAULT NULL, city_name VARCHAR(255) DEFAULT NULL, timezone INT DEFAULT NULL, created_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE open_weather_data');
    }
}
