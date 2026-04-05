<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Squashed migration — full schema as of 2026-04-05.
 *
 * Replaces all previous migrations (Version20260327000000 through Version20260405000000).
 *
 * Production safety: if the `users` table already exists the up() method exits
 * immediately, so running `doctrine:migrations:migrate` on an existing database
 * is safe — the migration is simply recorded as executed without touching the schema.
 */
final class Version20260405000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Squashed migration: full schema (users, cats, chip_types, calendars, chips, invitations, password_reset_tokens, pet_sitters, owner_profiles, messenger_messages, cat_weight_entries)';
    }

    public function up(Schema $schema): void
    {
        // Guard: existing installations already have the schema in place.
        // The migration framework records this version as executed after up() returns.
        if ($this->connection->createSchemaManager()->tableExists('users')) {
            return;
        }

        // ------------------------------------------------------------------ users
        $this->addSql('CREATE TABLE users (
            id VARCHAR(36) NOT NULL,
            email VARCHAR(180) NOT NULL,
            password VARCHAR(255) NOT NULL,
            role VARCHAR(50) NOT NULL,
            username VARCHAR(50) DEFAULT NULL,
            created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX "UNIQ_1483A5E9E7927C74" ON users (email)');

        // ------------------------------------------------------------------- cats
        $this->addSql('CREATE TABLE cats (
            id VARCHAR(36) NOT NULL,
            name VARCHAR(100) NOT NULL,
            weight DOUBLE PRECISION DEFAULT NULL,
            breed VARCHAR(100) DEFAULT NULL,
            colors JSON NOT NULL,
            owner_id VARCHAR(36) NOT NULL,
            created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            PRIMARY KEY(id)
        )');

        // --------------------------------------------------------------- chip_types
        $this->addSql('CREATE TABLE chip_types (
            id VARCHAR(36) NOT NULL,
            name VARCHAR(100) NOT NULL,
            color VARCHAR(7) NOT NULL,
            owner_id VARCHAR(36) NOT NULL,
            created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            PRIMARY KEY(id)
        )');

        // ------------------------------------------------------------- calendars
        $this->addSql('CREATE TABLE calendars (
            id VARCHAR(36) NOT NULL,
            cat_id VARCHAR(36) NOT NULL,
            created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX "UNIQ_84DF820FE6ADA943" ON calendars (cat_id)');

        // ------------------------------------------------------------------ chips
        $this->addSql('CREATE TABLE chips (
            id VARCHAR(36) NOT NULL,
            chip_type_id VARCHAR(36) NOT NULL,
            date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            note TEXT DEFAULT NULL,
            calendar_id VARCHAR(36) NOT NULL,
            author_id VARCHAR(36) NOT NULL,
            PRIMARY KEY(id)
        )');
        $this->addSql('ALTER TABLE chips ADD CONSTRAINT "FK_47178CB3A40A2C8" FOREIGN KEY (calendar_id) REFERENCES calendars (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX "IDX_47178CB3A40A2C8" ON chips (calendar_id)');

        // ------------------------------------------------------------- invitations
        $this->addSql('CREATE TABLE invitations (
            id VARCHAR(36) NOT NULL,
            owner_id VARCHAR(36) NOT NULL,
            invitee_email VARCHAR(180) NOT NULL,
            cat_id VARCHAR(36) NOT NULL,
            token VARCHAR(64) NOT NULL,
            expires_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            accepted BOOLEAN NOT NULL,
            declined BOOLEAN NOT NULL,
            PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX "UNIQ_232710AE5F37A13B" ON invitations (token)');

        // ------------------------------------------------- password_reset_tokens
        $this->addSql('CREATE TABLE password_reset_tokens (
            id VARCHAR(36) NOT NULL,
            email VARCHAR(180) NOT NULL,
            token VARCHAR(64) NOT NULL,
            expires_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            used BOOLEAN NOT NULL,
            PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX "UNIQ_3967A2165F37A13B" ON password_reset_tokens (token)');

        // ------------------------------------------------------------- pet_sitters
        $this->addSql('CREATE TABLE pet_sitters (
            id VARCHAR(36) NOT NULL,
            owner_id VARCHAR(36) NOT NULL,
            invitee_email VARCHAR(180) NOT NULL,
            user_id VARCHAR(36) DEFAULT NULL,
            type VARCHAR(50) NOT NULL,
            age INTEGER DEFAULT NULL,
            phone_number VARCHAR(30) DEFAULT NULL,
            created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            PRIMARY KEY(id)
        )');

        // ----------------------------------------------------------- owner_profiles
        $this->addSql('CREATE TABLE owner_profiles (
            user_id VARCHAR(36) NOT NULL,
            age INTEGER DEFAULT NULL,
            phone_number VARCHAR(30) DEFAULT NULL,
            PRIMARY KEY(user_id)
        )');

        // -------------------------------------------------------- messenger_messages
        $this->addSql('CREATE TABLE messenger_messages (
            id BIGINT GENERATED BY DEFAULT AS IDENTITY NOT NULL,
            body TEXT NOT NULL,
            headers TEXT NOT NULL,
            queue_name VARCHAR(190) NOT NULL,
            created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            available_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            delivered_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
            PRIMARY KEY(id)
        )');
        $this->addSql(
            'CREATE INDEX "IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750"'
            . ' ON messenger_messages (queue_name, available_at, delivered_at, id)'
        );

        // ------------------------------------------------------- cat_weight_entries
        $this->addSql('CREATE TABLE cat_weight_entries (
            id VARCHAR(36) NOT NULL,
            cat_id VARCHAR(36) NOT NULL,
            weight DOUBLE PRECISION NOT NULL,
            recorded_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            PRIMARY KEY(id)
        )');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE chips DROP CONSTRAINT "FK_47178CB3A40A2C8"');
        $this->addSql('DROP TABLE cat_weight_entries');
        $this->addSql('DROP TABLE messenger_messages');
        $this->addSql('DROP TABLE owner_profiles');
        $this->addSql('DROP TABLE pet_sitters');
        $this->addSql('DROP TABLE password_reset_tokens');
        $this->addSql('DROP TABLE invitations');
        $this->addSql('DROP TABLE chips');
        $this->addSql('DROP TABLE calendars');
        $this->addSql('DROP TABLE chip_types');
        $this->addSql('DROP TABLE cats');
        $this->addSql('DROP TABLE users');
    }
}
