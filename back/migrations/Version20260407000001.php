<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260407000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add admin_logs table for admin error log dashboard';
    }

    public function up(Schema $schema): void
    {
        if ($this->connection->createSchemaManager()->tableExists('admin_logs')) {
            return;
        }

        $this->addSql('CREATE TABLE admin_logs (
            id VARCHAR(36) NOT NULL,
            status_code INTEGER NOT NULL,
            method VARCHAR(10) NOT NULL,
            path VARCHAR(500) NOT NULL,
            user_id VARCHAR(180) DEFAULT NULL,
            message TEXT NOT NULL,
            stack_trace TEXT DEFAULT NULL,
            log_level VARCHAR(20) NOT NULL,
            created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            PRIMARY KEY(id)
        )');

        $this->addSql('CREATE INDEX idx_admin_logs_status_code ON admin_logs (status_code)');
        $this->addSql('CREATE INDEX idx_admin_logs_log_level ON admin_logs (log_level)');
        $this->addSql('CREATE INDEX idx_admin_logs_user_id ON admin_logs (user_id)');
        $this->addSql('CREATE INDEX idx_admin_logs_created_at ON admin_logs (created_at)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS admin_logs');
    }
}
