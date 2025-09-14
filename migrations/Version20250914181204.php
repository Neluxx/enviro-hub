<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250914181204 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE open_weather_data DROP longitude, DROP latitude, DROP timestamp, DROP country, DROP sunrise, DROP sunset, DROP city_name, DROP timezone, CHANGE weather_main weather_main VARCHAR(255) NOT NULL, CHANGE weather_description weather_description VARCHAR(255) NOT NULL, CHANGE weather_icon weather_icon VARCHAR(50) NOT NULL, CHANGE temperature temperature DOUBLE PRECISION NOT NULL, CHANGE feels_like feels_like DOUBLE PRECISION NOT NULL, CHANGE temp_min temp_min DOUBLE PRECISION NOT NULL, CHANGE temp_max temp_max DOUBLE PRECISION NOT NULL, CHANGE pressure pressure INT NOT NULL, CHANGE humidity humidity INT NOT NULL, CHANGE visibility visibility INT NOT NULL, CHANGE wind_speed wind_speed DOUBLE PRECISION NOT NULL, CHANGE wind_direction wind_direction INT NOT NULL, CHANGE cloudiness cloudiness INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE open_weather_data ADD longitude DOUBLE PRECISION DEFAULT NULL, ADD latitude DOUBLE PRECISION DEFAULT NULL, ADD timestamp DATETIME DEFAULT NULL, ADD country VARCHAR(3) DEFAULT NULL, ADD sunrise DATETIME DEFAULT NULL, ADD sunset DATETIME DEFAULT NULL, ADD city_name VARCHAR(255) DEFAULT NULL, ADD timezone INT DEFAULT NULL, CHANGE weather_main weather_main VARCHAR(255) DEFAULT NULL, CHANGE weather_description weather_description VARCHAR(255) DEFAULT NULL, CHANGE weather_icon weather_icon VARCHAR(50) DEFAULT NULL, CHANGE temperature temperature DOUBLE PRECISION DEFAULT NULL, CHANGE feels_like feels_like DOUBLE PRECISION DEFAULT NULL, CHANGE temp_min temp_min DOUBLE PRECISION DEFAULT NULL, CHANGE temp_max temp_max DOUBLE PRECISION DEFAULT NULL, CHANGE pressure pressure INT DEFAULT NULL, CHANGE humidity humidity INT DEFAULT NULL, CHANGE visibility visibility INT DEFAULT NULL, CHANGE wind_speed wind_speed DOUBLE PRECISION DEFAULT NULL, CHANGE wind_direction wind_direction INT DEFAULT NULL, CHANGE cloudiness cloudiness INT DEFAULT NULL');
    }
}
