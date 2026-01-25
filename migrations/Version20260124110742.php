<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260124110742 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create sensor_data table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE sensor_data (
              id INT AUTO_INCREMENT NOT NULL,
              node_uuid VARCHAR(36) NOT NULL,
              temperature INT NOT NULL,
              humidity INT NOT NULL,
              pressure INT NOT NULL,
              carbon_dioxide INT DEFAULT NULL,
              measured_at DATETIME NOT NULL,
              created_at DATETIME NOT NULL,
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE sensor_data');
    }
}
