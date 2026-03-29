<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Initial schema — creates all base tables.
 * All subsequent migrations apply ALTER statements on top of this baseline.
 */
final class Version20260327000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Initial schema: users, cats, chip_types, calendars, chips, invitations, password_reset_tokens';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE users (
            id VARCHAR(36) NOT NULL,
            email VARCHAR(180) NOT NULL,
            password VARCHAR(255) NOT NULL,
            role VARCHAR(50) NOT NULL,
            username VARCHAR(50) DEFAULT NULL,
            created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX uniq_users_email ON users (email)');

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

        $this->addSql('CREATE TABLE chip_types (
            id VARCHAR(36) NOT NULL,
            name VARCHAR(100) NOT NULL,
            color VARCHAR(7) NOT NULL,
            owner_id VARCHAR(36) NOT NULL,
            created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            PRIMARY KEY(id)
        )');

        $this->addSql('CREATE TABLE calendars (
            id VARCHAR(36) NOT NULL,
            cat_id VARCHAR(36) NOT NULL,
            created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX uniq_calendars_cat_id ON calendars (cat_id)');

        $this->addSql('CREATE TABLE chips (
            id VARCHAR(36) NOT NULL,
            chip_type_id VARCHAR(36) NOT NULL,
            date DATE NOT NULL,
            note TEXT DEFAULT NULL,
            calendar_id VARCHAR(36) NOT NULL,
            PRIMARY KEY(id)
        )');
        $this->addSql('ALTER TABLE chips ADD CONSTRAINT fk_chips_calendar FOREIGN KEY (calendar_id) REFERENCES calendars (id) NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('CREATE TABLE invitations (
            id VARCHAR(36) NOT NULL,
            owner_id VARCHAR(36) NOT NULL,
            invitee_email VARCHAR(180) NOT NULL,
            cat_id VARCHAR(36) NOT NULL,
            token VARCHAR(64) NOT NULL,
            expires_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            accepted BOOLEAN NOT NULL,
            PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX uniq_invitations_token ON invitations (token)');

        $this->addSql('CREATE TABLE password_reset_tokens (
            id VARCHAR(36) NOT NULL,
            email VARCHAR(180) NOT NULL,
            token VARCHAR(64) NOT NULL,
            expires_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            used BOOLEAN NOT NULL,
            PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX uniq_password_reset_tokens_token ON password_reset_tokens (token)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE chips DROP CONSTRAINT fk_chips_calendar');
        $this->addSql('DROP TABLE password_reset_tokens');
        $this->addSql('DROP TABLE invitations');
        $this->addSql('DROP TABLE chips');
        $this->addSql('DROP TABLE calendars');
        $this->addSql('DROP TABLE chip_types');
        $this->addSql('DROP TABLE cats');
        $this->addSql('DROP TABLE users');
    }
}
