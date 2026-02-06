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
        $table = $schema->createTable('sensor_data');

        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('node_uuid', 'string', ['length' => 36, 'notnull' => false]);
        $table->addColumn('temperature', 'decimal', ['precision' => 5, 'scale' => 2]);
        $table->addColumn('humidity', 'decimal', ['precision' => 5, 'scale' => 2]);
        $table->addColumn('pressure', 'integer');
        $table->addColumn('carbon_dioxide', 'integer', ['notnull' => false]);
        $table->addColumn('measured_at', 'datetime');
        $table->addColumn('created_at', 'datetime');

        $table->setPrimaryKey(['id']);
        $table->addIndex(['node_uuid'], 'idx_sensor_data_node_uuid');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('sensor_data');
    }
}
