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
        $table = $schema->createTable('homes');

        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('title', 'string', ['length' => 255]);
        $table->addColumn('identifier', 'string', ['length' => 255]);
        $table->addColumn('created_at', 'datetime');
        $table->addColumn('modified_at', 'datetime');

        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['identifier'], 'uniq_homes_identifier');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('homes');
    }
}
