<?php

declare(strict_types=1);

namespace App\Admin\Infrastructure\Http;

use App\Admin\Domain\Model\AdminLog;
use App\Admin\Domain\Model\AdminLogId;
use App\Admin\Domain\Repository\AdminLogRepository;
use App\Shared\Domain\Port\HttpErrorLogger;

final readonly class DoctrineHttpErrorLogger implements HttpErrorLogger
{
    public function __construct(private AdminLogRepository $adminLogRepository)
    {
    }

    public function log(
        int $statusCode,
        string $method,
        string $path,
        ?string $userId,
        string $message,
        ?\Throwable $exception,
    ): void {
        $stackTrace = null;
        if ($statusCode >= 500 && null !== $exception) {
            $stackTrace = $exception->getTraceAsString();
        }

        $this->adminLogRepository->save(AdminLog::record(
            id: AdminLogId::generate(),
            statusCode: $statusCode,
            method: $method,
            path: $path,
            userId: $userId,
            message: $message,
            stackTrace: $stackTrace,
        ));
    }
}
