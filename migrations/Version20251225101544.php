<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251225101544 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add node_uuid field to environmental_data table';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE environmental_data ADD node_uuid VARCHAR(36) NOT NULL AFTER id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE environmental_data DROP node_uuid');
    }
}
