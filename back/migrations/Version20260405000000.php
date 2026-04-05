<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add missing DC2Type column comment for pet_sitters.created_at.
 *
 * Version20260403000000 added DC2Type comments for all datetime_immutable
 * columns in the initial tables, but pet_sitters was created in
 * Version20260401000000 and its created_at column was never annotated.
 * Without this comment, DBAL 4 reports the schema as out of sync.
 */
final class Version20260405000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add missing DC2Type comment for pet_sitters.created_at';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("COMMENT ON COLUMN pet_sitters.created_at IS '(DC2Type:datetime_immutable)'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("COMMENT ON COLUMN pet_sitters.created_at IS NULL");
    }
}
