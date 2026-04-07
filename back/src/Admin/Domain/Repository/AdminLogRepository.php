<?php

declare(strict_types=1);

namespace App\Admin\Domain\Repository;

use App\Admin\Domain\Model\AdminLog;

interface AdminLogRepository
{
    public function save(AdminLog $log): void;

    /**
     * @return list<AdminLog>
     */
    public function findPaginated(
        int $page,
        int $limit,
        ?string $userId = null,
        ?string $logLevel = null,
        ?string $search = null,
    ): array;

    public function countFiltered(
        ?string $userId = null,
        ?string $logLevel = null,
        ?string $search = null,
    ): int;
}
