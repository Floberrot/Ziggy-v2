<?php

declare(strict_types=1);

namespace App\Admin\Domain\Model;

final class ActivityLog
{
    public function __construct(
        private readonly ActivityLogId $id,
        private readonly string $method,
        private readonly string $path,
        private readonly int $statusCode,
        private readonly ?string $userId,
        private readonly ?string $ip,
        private readonly \DateTimeImmutable $createdAt,
    ) {
    }

    public static function record(
        ActivityLogId $id,
        string $method,
        string $path,
        int $statusCode,
        ?string $userId,
        ?string $ip,
    ): self {
        return new self(
            id: $id,
            method: $method,
            path: $path,
            statusCode: $statusCode,
            userId: $userId,
            ip: $ip,
            createdAt: new \DateTimeImmutable(),
        );
    }

    public static function reconstitute(
        ActivityLogId $id,
        string $method,
        string $path,
        int $statusCode,
        ?string $userId,
        ?string $ip,
        \DateTimeImmutable $createdAt,
    ): self {
        return new self(
            id: $id,
            method: $method,
            path: $path,
            statusCode: $statusCode,
            userId: $userId,
            ip: $ip,
            createdAt: $createdAt,
        );
    }

    public function id(): ActivityLogId
    {
        return $this->id;
    }

    public function method(): string
    {
        return $this->method;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function statusCode(): int
    {
        return $this->statusCode;
    }

    public function userId(): ?string
    {
        return $this->userId;
    }

    public function ip(): ?string
    {
        return $this->ip;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
