<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260124115311 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add index for node_uuid column';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE INDEX idx_node_uuid ON sensor_data (node_uuid)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX idx_node_uuid ON sensor_data');
    }
}
