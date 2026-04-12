<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260412000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add activity_logs table for tracking all API requests';
    }

    public function up(Schema $schema): void
    {
        if ($this->connection->createSchemaManager()->tableExists('activity_logs')) {
            return;
        }

        $this->addSql('CREATE TABLE activity_logs (
            id VARCHAR(36) NOT NULL,
            method VARCHAR(10) NOT NULL,
            path VARCHAR(500) NOT NULL,
            status_code INTEGER NOT NULL,
            user_id VARCHAR(180) DEFAULT NULL,
            ip VARCHAR(45) DEFAULT NULL,
            created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            PRIMARY KEY(id)
        )');

        $this->addSql('CREATE INDEX idx_activity_logs_user_id ON activity_logs (user_id)');
        $this->addSql('CREATE INDEX idx_activity_logs_method ON activity_logs (method)');
        $this->addSql('CREATE INDEX idx_activity_logs_status_code ON activity_logs (status_code)');
        $this->addSql('CREATE INDEX idx_activity_logs_created_at ON activity_logs (created_at)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS activity_logs');
    }
}
