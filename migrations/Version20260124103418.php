<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260124103418 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create nodes table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE nodes (
              id INT AUTO_INCREMENT NOT NULL,
              uuid VARCHAR(36) NOT NULL,
              title VARCHAR(255) NOT NULL,
              home_id INT NOT NULL,
              created_at DATETIME NOT NULL,
              modified_at DATETIME NOT NULL,
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE nodes');
    }
}
