<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260124102752 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create homes table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE homes (
              id INT AUTO_INCREMENT NOT NULL,
              title VARCHAR(255) NOT NULL,
              identifier VARCHAR(255) NOT NULL,
              created_at DATETIME NOT NULL,
              modified_at DATETIME NOT NULL,
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE homes');
    }
}
