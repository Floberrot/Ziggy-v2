<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260329130000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Make chips.author_id NOT NULL (run ziggy:calendar:backfill-chip-authors first)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE chips ALTER COLUMN author_id SET NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE chips ALTER COLUMN author_id DROP NOT NULL');
    }
}
