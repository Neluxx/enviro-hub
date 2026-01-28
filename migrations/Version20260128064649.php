<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260128064649 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add many-to-one relation between sensor data and node';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE sensor_data
            ADD node_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE sensor_data
            ADD CONSTRAINT fk_nodes_id FOREIGN KEY (node_id) REFERENCES nodes (id)
        SQL);
        $this->addSql('CREATE INDEX idx_node_id ON sensor_data (node_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE sensor_data DROP FOREIGN KEY fk_nodes_id');
        $this->addSql('DROP INDEX idx_node_id ON sensor_data');
        $this->addSql(<<<'SQL'
            ALTER TABLE sensor_data
            DROP node_id
        SQL);
    }
}
