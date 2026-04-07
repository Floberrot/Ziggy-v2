<?php

declare(strict_types=1);

namespace App\Shared\Domain\Port;

interface HttpErrorLogger
{
    public function log(
        int $statusCode,
        string $method,
        string $path,
        ?string $userId,
        string $message,
        ?\Throwable $exception,
    ): void;
}
