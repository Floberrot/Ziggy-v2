<?php

declare(strict_types=1);

namespace App\Identity\Application\Port;

interface OwnerStatsPort
{
    public function countCatsByOwnerId(string $ownerId): int;

    public function countChipsByOwnerId(string $ownerId): int;
}
