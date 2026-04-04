<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260404000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create cat_weight_entries table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE cat_weight_entries (
            id VARCHAR(36) NOT NULL,
            cat_id VARCHAR(36) NOT NULL,
            weight DOUBLE PRECISION NOT NULL,
            recorded_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX idx_cat_weight_entries_cat_id ON cat_weight_entries (cat_id)');
        $this->addSql("COMMENT ON COLUMN cat_weight_entries.recorded_at IS '(DC2Type:datetime_immutable)'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE cat_weight_entries');
    }
}
