<?php

declare(strict_types=1);

namespace App\Admin\Application\Query\ListLogs;

use App\Admin\Domain\Repository\AdminLogRepository;
use App\Shared\Application\DTO\PaginatedResult;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'query.bus')]
final readonly class ListLogsHandler
{
    public function __construct(private AdminLogRepository $adminLogRepository)
    {
    }

    public function __invoke(ListLogsQuery $query): PaginatedResult
    {
        $logs = $this->adminLogRepository->findPaginated(
            page: $query->page,
            limit: $query->limit,
            userId: $query->userId,
            logLevel: $query->logLevel,
            search: $query->search,
        );

        $total = $this->adminLogRepository->countFiltered(
            userId: $query->userId,
            logLevel: $query->logLevel,
            search: $query->search,
        );

        $items = array_map(
            static fn ($log) => (new AdminLogView(
                id: $log->id()->value(),
                statusCode: $log->statusCode(),
                method: $log->method(),
                path: $log->path(),
                userId: $log->userId(),
                message: $log->message(),
                stackTrace: $log->stackTrace(),
                logLevel: $log->logLevel(),
                createdAt: $log->createdAt()->format(\DateTimeInterface::ATOM),
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
