<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add DC2Type column comments required by Doctrine DBAL 4.x.
 *
 * DBAL 4 uses COMMENT ON COLUMN to distinguish custom PHP types
 * (datetime_immutable, json) from native PostgreSQL types. Without these
 * comments doctrine:schema:validate reports the schema as out of sync
 * even though all tables and columns are structurally correct.
 */
final class Version20260403000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add DC2Type column comments for datetime_immutable and json columns';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("COMMENT ON COLUMN users.created_at IS '(DC2Type:datetime_immutable)'");
        $this->addSql("COMMENT ON COLUMN cats.created_at IS '(DC2Type:datetime_immutable)'");
        $this->addSql("COMMENT ON COLUMN cats.colors IS '(DC2Type:json)'");
        $this->addSql("COMMENT ON COLUMN chip_types.created_at IS '(DC2Type:datetime_immutable)'");
        $this->addSql("COMMENT ON COLUMN calendars.created_at IS '(DC2Type:datetime_immutable)'");
        $this->addSql("COMMENT ON COLUMN chips.date IS '(DC2Type:datetime_immutable)'");
        $this->addSql("COMMENT ON COLUMN invitations.expires_at IS '(DC2Type:datetime_immutable)'");
        $this->addSql("COMMENT ON COLUMN password_reset_tokens.expires_at IS '(DC2Type:datetime_immutable)'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("COMMENT ON COLUMN users.created_at IS NULL");
        $this->addSql("COMMENT ON COLUMN cats.created_at IS NULL");
        $this->addSql("COMMENT ON COLUMN cats.colors IS NULL");
        $this->addSql("COMMENT ON COLUMN chip_types.created_at IS NULL");
        $this->addSql("COMMENT ON COLUMN calendars.created_at IS NULL");
        $this->addSql("COMMENT ON COLUMN chips.date IS NULL");
        $this->addSql("COMMENT ON COLUMN invitations.expires_at IS NULL");
        $this->addSql("COMMENT ON COLUMN password_reset_tokens.expires_at IS NULL");
    }
}
