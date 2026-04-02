<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Align constraint and index names with Doctrine ORM's auto-generated naming convention.
 *
 * Doctrine generates CRC32-based names (e.g. UNIQ_1483A5E9E7927C74) while the initial
 * migrations used human-readable names. doctrine:schema:validate compares the exact names,
 * so this migration renames every mismatching constraint / index and adds the missing
 * index on chips.calendar_id that Doctrine expects for the ManyToOne side of the relation.
 */
final class Version20260402000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rename unique/FK constraints and add missing FK index to match Doctrine naming convention';
    }

    public function up(Schema $schema): void
    {
        // users.email unique index
        $this->addSql('ALTER INDEX uniq_users_email RENAME TO "UNIQ_1483A5E9E7927C74"');

        // calendars.cat_id unique index
        $this->addSql('ALTER INDEX uniq_calendars_cat_id RENAME TO "UNIQ_84DF820FE6ADA943"');

        // invitations.token unique index
        $this->addSql('ALTER INDEX uniq_invitations_token RENAME TO "UNIQ_232710AE5F37A13B"');

        // password_reset_tokens.token unique index
        $this->addSql('ALTER INDEX uniq_password_reset_tokens_token RENAME TO "UNIQ_3967A2165F37A13B"');

        // chips: rename FK constraint and add missing index on calendar_id
        $this->addSql('ALTER TABLE chips RENAME CONSTRAINT fk_chips_calendar TO "FK_47178CB3A40A2C8"');
        $this->addSql('CREATE INDEX "IDX_47178CB3A40A2C8" ON chips (calendar_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX "IDX_47178CB3A40A2C8"');
        $this->addSql('ALTER TABLE chips RENAME CONSTRAINT "FK_47178CB3A40A2C8" TO fk_chips_calendar');

        $this->addSql('ALTER INDEX "UNIQ_3967A2165F37A13B" RENAME TO uniq_password_reset_tokens_token');
        $this->addSql('ALTER INDEX "UNIQ_232710AE5F37A13B" RENAME TO uniq_invitations_token');
        $this->addSql('ALTER INDEX "UNIQ_84DF820FE6ADA943" RENAME TO uniq_calendars_cat_id');
        $this->addSql('ALTER INDEX "UNIQ_1483A5E9E7927C74" RENAME TO uniq_users_email');
    }
}
