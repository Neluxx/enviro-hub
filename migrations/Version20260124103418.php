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
        $table = $schema->createTable('nodes');

        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('uuid', 'string', ['length' => 36]);
        $table->addColumn('title', 'string', ['length' => 255]);
        $table->addColumn('home_id', 'integer', ['notnull' => false]);
        $table->addColumn('created_at', 'datetime');
        $table->addColumn('modified_at', 'datetime');

        $table->setPrimaryKey(['id']);
        $table->addIndex(['home_id'], 'idx_nodes_home_id');
        $table->addUniqueIndex(['uuid'], 'uniq_nodes_uuid');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('nodes');
    }
}
