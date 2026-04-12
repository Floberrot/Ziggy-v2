<?php

declare(strict_types=1);

namespace App\Admin\Application\Query\ListActivityLogs;

use App\Admin\Domain\Model\ActivityLog;
use App\Admin\Domain\Repository\ActivityLogRepository;
use App\Shared\Application\DTO\PaginatedResult;
use DateTimeInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'query.bus')]
final readonly class ListActivityLogsHandler
{
    public function __construct(private ActivityLogRepository $activityLogRepository)
    {
    }

    public function __invoke(ListActivityLogsQuery $query): PaginatedResult
    {
        $logs = $this->activityLogRepository->findPaginated(
            page: $query->page,
            limit: $query->limit,
            userId: $query->userId,
            method: $query->method,
            search: $query->search,
        );

        $total = $this->activityLogRepository->countFiltered(
            userId: $query->userId,
            method: $query->method,
            search: $query->search,
        );

        $items = array_map(
            static fn (ActivityLog $log) => (new ActivityLogView(
                id: $log->id()->value(),
                method: $log->method(),
                path: $log->path(),
                statusCode: $log->statusCode(),
                userId: $log->userId(),
                ip: $log->ip(),
                createdAt: $log->createdAt()->format(DateTimeInterface::ATOM),
            ))->toArray(),
            $logs,
        );

        return new PaginatedResult(
            items: $items,
            total: $total,
            page: $query->page,
            limit: $query->limit,
        );
    }
}
