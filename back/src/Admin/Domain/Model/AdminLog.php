<?php

declare(strict_types=1);

namespace App\Admin\Domain\Model;

final class AdminLog
{
    public function __construct(
        private readonly AdminLogId $id,
        private readonly int $statusCode,
        private readonly string $method,
        private readonly string $path,
        private readonly ?string $userId,
        private readonly string $message,
        private readonly ?string $stackTrace,
        private readonly string $logLevel,
        private readonly \DateTimeImmutable $createdAt,
    ) {
    }

    public static function record(
        AdminLogId $id,
        int $statusCode,
        string $method,
        string $path,
        ?string $userId,
        string $message,
        ?string $stackTrace,
    ): self {
        $logLevel = match (true) {
            $statusCode >= 500 => 'error',
            $statusCode >= 400 => 'warning',
            default => 'info',
        };

        return new self(
            id: $id,
            statusCode: $statusCode,
            method: $method,
            path: $path,
            userId: $userId,
            message: $message,
            stackTrace: $stackTrace,
            logLevel: $logLevel,
            createdAt: new \DateTimeImmutable(),
        );
    }

    public static function reconstitute(
        AdminLogId $id,
        int $statusCode,
        string $method,
        string $path,
        ?string $userId,
        string $message,
        ?string $stackTrace,
        string $logLevel,
        \DateTimeImmutable $createdAt,
    ): self {
        return new self(
            id: $id,
            statusCode: $statusCode,
            method: $method,
            path: $path,
            userId: $userId,
            message: $message,
            stackTrace: $stackTrace,
            logLevel: $logLevel,
            createdAt: $createdAt,
        );
    }

    public function id(): AdminLogId
    {
        return $this->id;
    }

    public function statusCode(): int
    {
        return $this->statusCode;
    }

    public function method(): string
    {
        return $this->method;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function userId(): ?string
    {
        return $this->userId;
    }

    public function message(): string
    {
        return $this->message;
    }

    public function stackTrace(): ?string
    {
        return $this->stackTrace;
    }

    public function logLevel(): string
    {
        return $this->logLevel;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
