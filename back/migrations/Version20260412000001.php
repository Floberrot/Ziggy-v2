<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260412000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add scheduled_chip_type_ids JSON column to calendars table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            "ALTER TABLE calendars ADD COLUMN scheduled_chip_type_ids JSON NOT NULL DEFAULT '[]'"
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE calendars DROP COLUMN scheduled_chip_type_ids');
    }
}
