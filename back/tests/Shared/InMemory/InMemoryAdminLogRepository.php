<?php

declare(strict_types=1);

namespace App\Tests\Shared\InMemory;

use App\Admin\Domain\Model\AdminLog;
use App\Admin\Domain\Repository\AdminLogRepository;

final class InMemoryAdminLogRepository implements AdminLogRepository
{
    /** @var array<string, AdminLog> */
    private array $store = [];

    public function save(AdminLog $log): void
    {
        $this->store[$log->id()->value()] = $log;
    }

    /** @return list<AdminLog> */
    public function findPaginated(
        int $page,
        int $limit,
        ?string $userId = null,
        ?string $logLevel = null,
        ?string $search = null,
    ): array {
        $filtered = array_filter($this->store, static function (AdminLog $log) use ($userId, $logLevel, $search) {
            if (null !== $userId && $log->userId() !== $userId) {
                return false;
            }
            if (null !== $logLevel && $log->logLevel() !== $logLevel) {
                return false;
            }
            if (null !== $search) {
                if (
                    !str_contains($log->message(), $search)
                    && !str_contains($log->path(), $search)
                ) {
                    return false;
                }
            }

            return true;
        });

        return array_values(array_slice($filtered, ($page - 1) * $limit, $limit));
    }

    public function countFiltered(
        ?string $userId = null,
        ?string $logLevel = null,
        ?string $search = null,
    ): int {
        return count($this->findPaginated(1, PHP_INT_MAX, $userId, $logLevel, $search));
    }
}
