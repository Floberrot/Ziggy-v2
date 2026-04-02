<?php

declare(strict_types=1);

namespace App\Identity\Infrastructure\Output\Persistence;

use App\Identity\Application\Port\OwnerStatsPort;
use Doctrine\DBAL\Connection;

final readonly class DoctrineOwnerStatsProvider implements OwnerStatsPort
{
    public function __construct(private Connection $connection)
    {
    }

    public function countCatsByOwnerId(string $ownerId): int
    {
        $result = $this->connection->fetchOne(
            'SELECT COUNT(id) FROM cats WHERE owner_id = ?',
            [$ownerId],
        );

        return is_scalar($result) ? (int) $result : 0;
    }

    public function countChipsByOwnerId(string $ownerId): int
    {
        $result = $this->connection->fetchOne(
            'SELECT COUNT(id) FROM chips WHERE author_id = ?',
            [$ownerId],
        );

        return is_scalar($result) ? (int) $result : 0;
    }
}
