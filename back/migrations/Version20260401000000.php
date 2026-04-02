<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Creates pet_sitters and owner_profiles tables; adds declined column to invitations.
 */
final class Version20260401000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add pet_sitters, owner_profiles tables and declined column to invitations';
    }

    public function up(Schema $schema): void
    {
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
        $this->addSql('CREATE INDEX idx_pet_sitters_owner_id ON pet_sitters (owner_id)');
        $this->addSql('CREATE INDEX idx_pet_sitters_owner_email ON pet_sitters (owner_id, invitee_email)');

        $this->addSql('CREATE TABLE owner_profiles (
            user_id VARCHAR(36) NOT NULL,
            age INTEGER DEFAULT NULL,
            phone_number VARCHAR(30) DEFAULT NULL,
            PRIMARY KEY(user_id)
        )');

        $this->addSql('ALTER TABLE invitations ADD COLUMN declined BOOLEAN NOT NULL DEFAULT FALSE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE invitations DROP COLUMN declined');
        $this->addSql('DROP TABLE owner_profiles');
        $this->addSql('DROP TABLE pet_sitters');
    }
}
