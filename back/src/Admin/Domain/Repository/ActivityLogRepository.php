<?php

declare(strict_types=1);

namespace App\Admin\Domain\Repository;

use App\Admin\Domain\Model\ActivityLog;

interface ActivityLogRepository
{
    public function save(ActivityLog $log): void;

    /**
     * @return list<ActivityLog>
     */
    public function findPaginated(
        int $page,
        int $limit,
        ?string $userId = null,
        ?string $method = null,
        ?string $search = null,
    ): array;

    public function countFiltered(
        ?string $userId = null,
        ?string $method = null,
        ?string $search = null,
    ): int;
}
